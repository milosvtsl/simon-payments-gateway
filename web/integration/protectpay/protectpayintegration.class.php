<?php
/**
 * Created by PhpStorm.
 * Merchant: ari
 * Date: 9/2/2016
 * Time: 11:13 AM
 */
namespace Integration\ProtectPay;

use Dompdf\Exception;
use Integration\Model;
use Integration\Model\AbstractIntegration;
use Integration\Model\AbstractMerchantIdentity;
use Integration\Model\Ex\IntegrationException;
use Integration\Model\IntegrationRow;
use Integration\Request\Model\IntegrationRequestRow;
use Merchant\Model\MerchantFormRow;
use Merchant\Model\MerchantIntegrationRow;
use Merchant\Model\MerchantRow;
use Order\Mail\ReceiptEmail;
use Order\Model\OrderRow;
use Order\Model\TransactionRow;
use Payment\Model\PaymentRow;
use Subscription\Mail\CancelEmail;
use Subscription\Model\SubscriptionRow;
use User\Model\UserRow;

class ProtectPayIntegration extends AbstractIntegration
{
    const _CLASS = __CLASS__;

    const REST_DOMAIN_PROPAY    = 'https://xml.propay.com/API/PropayAPI.aspx';
    const REST_DOMAIN_PROPAY_TEST    = 'https://xmltest.propay.com/API/PropayAPI.aspx';

    const REST_DOMAIN_PROTECTPAY = "https://xmltestapi.propay.com";
    const REST_DOMAIN_PROTECTPAY_TEST = "https://xmltestapi.propay.com";

//    const POST_URL_MERCHANT_IDENTITY = "/ProtectPay/Payers/";
    const POST_URL_MERCHANT_IDENTITY = "/ProtectPay/MerchantProfiles/";
    const POST_URL_TRANSACTION_PAYER = "/ProtectPay/Payers/";
    const POST_URL_TRANSACTION_CREATE = "/ProtectPay/Payers/{PayerID}/PaymentMethods/";
    const POST_URL_TRANSACTION_AUTHORIZE = "/ProtectPay/Payers/{PayerID}/PaymentMethods/AuthorizedTransactions/";
    const POST_URL_TRANSACTION_AUTHORIZE_AND_CAPTURE = "/ProtectPay/Payers/{PayerID}/PaymentMethods/AuthorizedAndCapturedTransactions/";
    const POST_URL_TRANSACTION_REFUND = "/ProtectPay/Payers/{PayerID}/PaymentMethods/RefundTransactions/";
    const POST_URL_TRANSACTION_CAPTURE = "/ProtectPay/Payers/{PayerID}/PaymentMethods/CapturedTransactions/";
    const POST_URL_TRANSACTION_VOID = "/ProtectPay/Payers/{PayerID}/PaymentMethods/VoidedTransactions/";
    const POST_URL_TRANSACTION_SPLITPAY = "/ProtectPay/Payers/{PayerID}/PaymentMethods/ProcessedSplitPayTransactions/";

    const POST_URL_TRANSACTION_TEMP_TOKEN = "/ProtectPay/TempTokens/?payerName={payerName}&durationSeconds={durationSeconds}";


    /**
     * @param MerchantRow $MerchantRow
     * @param IntegrationRow $IntegrationRow
     * @return AbstractMerchantIdentity
     */
    public function getMerchantIdentity(MerchantRow $MerchantRow, IntegrationRow $IntegrationRow) {
        $MerchantIdentity = MerchantIntegrationRow::fetch($MerchantRow->getID(), $IntegrationRow->getID());
        return new ProtectPayMerchantIdentity($MerchantRow, $IntegrationRow, $MerchantIdentity);
    }

    /**
     * Execute a prepared request
     * @param AbstractMerchantIdentity $MerchantIdentity
     * @param IntegrationRequestRow $Request
     * @throws IntegrationException
     */
    function execute(AbstractMerchantIdentity $MerchantIdentity, IntegrationRequestRow $Request) {
        // Propay has empty requests
//        if(!$Request->getRequest())
//            throw new IntegrationException("Request content is empty");
        if($Request->getResponse())
            throw new IntegrationException("This request instance already has a response");

        $APIUtil = new ProtectPayAPIUtil();

        $duration = -microtime(true);
        $response = $APIUtil->executeAPIRequest($MerchantIdentity, $Request);

        // Set duration
        $duration += microtime(true);
        $Request->setDuration($duration);

        // Save the response
        $Request->setResponse($response);

    }

    /**
     * Submit a new transaction
     * @param AbstractMerchantIdentity $MerchantIdentity
     * @param OrderRow $Order
     * @param UserRow $SessionUser
     * @param array $post
     * @return TransactionRow
     * @throws IntegrationException
     */
    function submitNewTransaction(AbstractMerchantIdentity $MerchantIdentity, OrderRow $Order, UserRow $SessionUser, Array $post) {

        OrderRow::insertOrUpdate($Order);
        if(!$Order->getID())
            throw new \InvalidArgumentException("Order must exist in the database");

        // Create Transaction
        $Transaction = TransactionRow::createTransactionFromPost($MerchantIdentity, $Order, $post);
        $service_fee = $MerchantIdentity->calculateServiceFee($Order, 'Authorized');
        $Transaction->setServiceFee($service_fee);



        $APIUtil = new ProtectPayAPIUtil();
        $IntegrationRow = $MerchantIdentity->getIntegrationRow();
        $Integration = $IntegrationRow->getIntegration();
        /** @var ProtectPayMerchantIdentity $MerchantIdentity **/

        // Create Payer Account ID
        $Request = $APIUtil->preparePayerAccountIdRequest($MerchantIdentity, $Order, $post);

        // Execute
        $Integration->execute($MerchantIdentity, $Request);

        // Try parsing the response
        $data = json_decode($Request->getResponse(), true);
        $Request->setResponseCode($data['RequestResult']['ResultCode'] ?: "-1");
        $Request->setResponseMessage(@$data['RequestResult']['ResultMessage'] ?: @$data['RequestResult']['ResultValue'] ?: 'No Error Message');
        if ($Request->getResponseCode() !== '00') {
            $Request->setResult(IntegrationRequestRow::ENUM_RESULT_FAIL);
            IntegrationRequestRow::insert($Request);
            throw new IntegrationException($Request->getResponseCode() . ' : ' . $Request->getResponseMessage());
        }

        $PayerAccountId = $data['ExternalAccountID'];
        if(!$PayerAccountId)
            throw new IntegrationException("Failed to create PayerAccountId");

        $remoteID = array();
        $remoteID['PayerAccountId'] = $PayerAccountId;
        $Order->setIntegrationRemoteID(json_encode($remoteID));


        // Process Transaction
        $Request = $APIUtil->prepareSaleRequest($MerchantIdentity, $Transaction, $Order, $post);

        // Execute
        $Integration->execute($MerchantIdentity, $Request);

        // Try parsing the response
        $data = json_decode($Request->getResponse(), true);
        $transaction = $data['AuthorizeResult']['Transaction'];
        $result = @$data['AuthorizeResult']['RequestResult'] ?: $data['Result'];
        $code = $result['ResultCode'];
        $message = @$result['ResultMessage'] ?: @$transaction['TransactionResult'] ?: @$transaction['ResultCode']['ResultMessage'] ?: @$transaction['ResultCode']['ResultValue'];
        $Request->setResponseCode($code);
        $Request->setResponseMessage($message);
        if ($Request->getResponseCode() !== '00') {
            $Request->setResult(IntegrationRequestRow::ENUM_RESULT_FAIL);
            IntegrationRequestRow::insert($Request);
            throw new IntegrationException($Request->getResponseCode() . ' : ' . $Request->getResponseMessage());
        }
        $Request->setResult(IntegrationRequestRow::ENUM_RESULT_SUCCESS);
        IntegrationRequestRow::insert($Request);

        $transactionID = $transaction['TransactionResult'];
        if(!$transactionID)
            throw new IntegrationException("Success response did not return a transaction ID");
        $Transaction->setTransactionID($transactionID);

        $Transaction->setAction("Authorized");
        $Order->setStatus("Authorized");

        $Transaction->setStatus($code, $message);
        // Store Transaction Result
        TransactionRow::insert($Transaction);

        // Update Order
        OrderRow::update($Order);

        // Insert Request
        $Request->setType('transaction');
        $Request->setTypeID($Transaction->getID());
        $Request->setOrderItemID($Order->getID());
        $Request->setTransactionID($Transaction->getID());
        if($SessionUser)
            $Request->setUserID($SessionUser->getID());
        IntegrationRequestRow::update($Request);

        if($Order->getPayeeEmail()) {
            $EmailReceipt = new ReceiptEmail($Order, $MerchantIdentity->getMerchantRow());
            if(!$EmailReceipt->send())
                error_log($EmailReceipt->ErrorInfo);
        }


    }


    /**
     * Create a new order, optionally set up a new payment entry with the remote integration
     * @param AbstractMerchantIdentity $MerchantIdentity
     * @param PaymentRow $PaymentInfo
     * @param MerchantFormRow $OrderForm
     * @param array $post Order Information
     * @return OrderRow
     */
    function createNewOrder(AbstractMerchantIdentity $MerchantIdentity, PaymentRow $PaymentInfo, MerchantFormRow $OrderForm, Array $post) {
        $Order = OrderRow::createNewOrder($MerchantIdentity, $PaymentInfo, $OrderForm, $post);
        return $Order;
    }

    /**
     * @param ProtectPayMerchantIdentity $MerchantIdentity
     * @param MerchantFormRow $OrderForm
     * @param array $post
     * @return Array
     * @throws IntegrationException
     */
    public function requestTempToken(ProtectPayMerchantIdentity $MerchantIdentity, MerchantFormRow $OrderForm, Array $post) {
        $Request = IntegrationRequestRow::prepareNew(
            $MerchantIdentity,
            IntegrationRequestRow::ENUM_TYPE_TRANSACTION_TEMP_TOKEN
        );

        $Name = $post['payee_full_name'];

        $APIData = $MerchantIdentity->getIntegrationRow();
        $url = $APIData->getAPIURLBase() . self::POST_URL_TRANSACTION_TEMP_TOKEN;
        $url = str_replace('{payerName}', urlencode($Name), $url);
        $url = str_replace('{durationSeconds}', 600, $url);
        $Request->setRequestURL($url);

//        $APIUtil = new ProtectPayAPIUtil();
        $request = null; // $APIUtil->prepareTempTokenRequest($MerchantIdentity, $PayerID, $Name);
        $Request->setRequest($request);

        $this->execute($MerchantIdentity, $Request);

        // Try parsing the response
        $data = json_decode($Request->getResponse(), true);
        $Request->setResponseMessage($data['RequestResult']['ResultMessage'] ?: "No Message");
        $Request->setResponseCode($data['RequestResult']['ResultCode'] ?: '-1');

        if($Request->getResponseCode() !== '00')
            throw new IntegrationException($Request->getResponseCode() . ' : ' . $Request->getResponseMessage());

        $Request->setResult(IntegrationRequestRow::ENUM_RESULT_SUCCESS);

        $TempToken = $data['TempToken'];
//        $data['TempTokenMD5'] = $TempTokenMD5;

        $PayerId = $data['PayerId'];
        $CID = $data['CredentialId'];
        $data['CID'] = $CID;
        unset($data['CredentialId']);

        $KeyValuePairs = array(
            'AuthToken'=> $MerchantIdentity->getAuthenticationToken(), // '1f25d31c-e8fe-4d68-be73-f7b439bfa0a329e90de6-4e93-4374-8633-22cef77467f5',
            'PayerID' => $PayerId, // '2833955147881261',
            'PaymentProcessType' => 'CreditCard',
            'ProcessMethod' => 'Capture',
            'PaymentMethodStorageOption' => 'None',
            'CurrencyCode' => 'USD',
            'Amount' => @$post['amount'],
            'StandardEntryClassCode' => 'WEB',
            'InvoiceNumber' => @$post['invoice_number'],
            'ReturnURL' => '/integration/protectpay/response.php',
            'ProfileId' => $MerchantIdentity->getMerchantProfileId(), // '3351',
            'DisplayMessage' => 'True',
            'Comment1' => @$post['notes'],
            'Comment2' => '',
            'Echo' => 'echotest',
            'Protected' => 'False',
        );

        $KeyValuePairString = http_build_query($KeyValuePairs);
        $padding = 16 - (strlen($KeyValuePairString) % 16);
        $KeyValuePairString .= str_repeat(chr($padding), $padding);

        $key = hash('MD5', utf8_encode($TempToken), true);
        $iv = $key;
        $SettingsCipher = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $KeyValuePairString, MCRYPT_MODE_CBC, $iv);
        $SettingsCipher = base64_encode($SettingsCipher);

        if(!$SettingsCipher)
            throw new IntegrationException("Failed to create SettingsCipher");

        $data['SettingsCipher'] = $SettingsCipher;
        $data['merchant_uid'] = $MerchantIdentity->getMerchantRow()->getUID();
        $data['integration_uid'] = $MerchantIdentity->getIntegrationRow()->getUID();
        $data['form_uid'] = $OrderForm->getUID();

        // New PayerId created. Store in session until transaction completes
        if(empty($_SESSION[__FILE__]) || sizeof($_SESSION[__FILE__]) > 10)
            $_SESSION[__FILE__] = array();
        $_SESSION[__FILE__][$CID] = $data;

        return $data;
    }

    /**
     * Process the remote order and return the result
     * @param $CID
     * @param $ResponseCipher
     * @return OrderRow
     * @throws IntegrationException
     * @throws \Exception
     *
     * Decryption Process
     * The �ResponseCipher� is encrypted using the same process and TempToken used to encrypt the �SettingsCipher�.
     * 1. Base64 decode the response cipher.
     * 2. UTF-8 encode the same TempToken used to encrypt and generate an MD5 hash of it.
     * 3. Decrypt the result of step 1 using AES-128 decryption using Cipher Block Chaining (CBC) mode.
     * a. Set both the Key and Initialization Vector (IV) equal to result from step 2.
     * ? The decrypted response will be in the form of Key-Value Pairs and contain the response of the requested transaction.
     */
    static function processResponseCipher($CID, $ResponseCipher) {
        if(empty($_SESSION[__FILE__]))
            throw new IntegrationException("No temp tokens were created for this session");

        if(empty($_SESSION[__FILE__][$CID]))
            throw new IntegrationException("Temp Token was not found: " . $CID);

        $data = $_SESSION[__FILE__][$CID];


        // TODO: store integration request

        $merchant_uid = $data['merchant_uid'];
        $MerchantRow = MerchantRow::fetchByUID($merchant_uid);

        $integration_uid = $data['integration_uid'];
        $IntegrationRow = IntegrationRow::fetchByUID($integration_uid);

        $OrderForm = MerchantFormRow::fetchByUID($data['form_uid']);

        /** @var ProtectPayIntegration $Integration */
        $Integration = $IntegrationRow->getIntegration();
        if(! $Integration instanceof ProtectPayIntegration)
            throw new \Exception("Not a protectpay integration: " . $integration_uid);

        /** @var ProtectPayMerchantIdentity $MerchantIdentity */
        $MerchantIdentity = $Integration->getMerchantIdentity($MerchantRow, $IntegrationRow);

        $TempToken = $data['TempToken'];
        $SettingsCipher = $data['SettingsCipher'];


        $key = hash('MD5', utf8_encode($TempToken), true);
        $iv = $key;
        $ResponseKeyValuePairString = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, base64_decode($ResponseCipher), MCRYPT_MODE_CBC, $iv);

        $SettingsCipher = base64_decode($SettingsCipher);
        $SettingsKeyValuePairString = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $SettingsCipher, MCRYPT_MODE_CBC, $iv);
        $sres = array();
        parse_str($SettingsKeyValuePairString, $sres);




        $padding = ord($ResponseKeyValuePairString[strlen($ResponseKeyValuePairString) - 1]);
        $ResponseKeyValuePairString = substr($ResponseKeyValuePairString, 0, -$padding);
        // Action=Complete&Echo=&PayerID=6105194103232727&ExpireDate=0319&CardholderName=Ari Asulin&Address1=611 W 6th Ave, sdf, sdf, sdf, sdf, sdf, sdf&Address2=sdf&Address3=&City=Mesa&State=AZ&PostalCode=85210&Country=USA
        $res = array();
        parse_str($ResponseKeyValuePairString, $res);

        $message = @$res['StoreMsg'] ?: @$res['ProcErrMsg'] ?: @$res['ErrMsg'] ?: @$res['ProcessResultResultMessage'] ?: @$res['ProcessResult'];
        $code = @$res['StoreCode'] ?: @$res['ProcErrCode'] ?: @$res['ErrCode'] ?: @$res['ProcessResultResultCode'];

        if($code !== '00')
            throw new IntegrationException($code . ':' . $message);

        $transactionID = @$res['ProcessResultTransactionId'];
//        $Action = @$res['Action'];

        $post = $_POST + array(
            'amount' => $res['Amount'],
            'payee_full_name' => @$res['CardholderName'],
            'payee_phone_number' => @$res[''],
            'payee_reciept_email' => @$res[''],
            'payee_address' => @$res['Address1'],
            'payee_address2' => @$res['Address2'],
            'payee_zipcode' => @$res['PostalCode'],
            'payee_city' => @$res['City'],
            'payee_state' => @$res['State'],

            'card_number' => @$res['ObfuscatedAccountNumber'],
            'card_type' => @$res[''],
            'card_exp_month' => @$res['ExpireDate'] ? substr(@$res['ExpireDate'], 0, 2) : '',
            'card_exp_year' => @$res['ExpireDate'] ? substr(@$res['ExpireDate'], -2) : '',

            'check_account_name' => @$res[''],
            'check_account_bank_name' => @$res[''],
            'check_account_number' => @$res[''],
            'check_account_type' => @$res[''],
            'check_routing_number' => @$res[''],
            'check_type' => @$res[''],
        );

        $PaymentInfo = PaymentRow::createPaymentFromPost($post);
        $OrderRow = OrderRow::createNewOrder($MerchantIdentity, $PaymentInfo, $OrderForm, $post);
        $OrderRow->setIntegrationRemoteID($ResponseKeyValuePairString);
        $OrderRow->setStatus("Authorized");
        OrderRow::insertOrUpdate($OrderRow);

        // Insert custom order fields

        foreach($OrderForm->getAllCustomFields(false) as $customField) {
            if(!empty($post[$customField])) {
                $OrderRow->insertCustomField($customField, $post[$customField]);
            }
        }

        // Create Transaction
        $Transaction = TransactionRow::createTransactionFromPost($MerchantIdentity, $OrderRow, $post);
        $service_fee = $MerchantIdentity->calculateServiceFee($OrderRow, 'Authorized');
        $Transaction->setServiceFee($service_fee);
        $Transaction->setAction("Authorized");
        $Transaction->setTransactionID($transactionID);
        $Transaction->setStatus($code, $message);
        // Store Transaction Result
        TransactionRow::insert($Transaction);

        // Update Order
        OrderRow::update($OrderRow);

        if($OrderRow->getPayeeEmail()) {
            $EmailReceipt = new ReceiptEmail($OrderRow, $MerchantIdentity->getMerchantRow());
            if(!$EmailReceipt->send())
                error_log($EmailReceipt->ErrorInfo);
        }

        // Clear session data
        unset($_SESSION[__FILE__]);

        return $OrderRow;
    }



    /**
     * Reverse an existing Transaction
     * @param AbstractMerchantIdentity $MerchantIdentity
     * @param OrderRow $Order
     * @param UserRow $SessionUser
     * @param array $post
     * @return mixed
     * @throws IntegrationException
     * @throws \Exception
     * @throws \phpmailerException
     */
    function reverseTransaction(AbstractMerchantIdentity $MerchantIdentity, OrderRow $Order, UserRow $SessionUser, Array $post) {
        if(!$Order->getID())
            throw new \InvalidArgumentException("Order must exist in the database");

        if(empty($post['amount']))
            $post['amount'] = $Order->getAmount();

        // Create Transaction
        $ReverseTransaction = TransactionRow::createTransactionFromPost($MerchantIdentity, $Order, $post);
        /** @var ProtectPayMerchantIdentity $MerchantIdentity */

        $Request = IntegrationRequestRow::prepareNew(
            $MerchantIdentity,
            IntegrationRequestRow::ENUM_TYPE_TRANSACTION_REVERSAL
        );

        $url = $this->getRequestURL($MerchantIdentity, $Request);

        $Request->setRequestURL($url);

        $APIUtil = new ProtectPayAPIUtil();
        $request = $APIUtil->prepareCreditCardReversalRequest($MerchantIdentity, $ReverseTransaction, $Order, $post);
        $Request->setRequest($request);

        $this->execute($MerchantIdentity, $Request);

        $response = $this->parseResponseData($Request);
        $code = $response['ExpressResponseCode'];
        $message = $response['ExpressResponseMessage'];
        if(!$response) //  || !$code || !$message)
            throw new IntegrationException("Invalid response data");

        if($code === '101')
            throw new IntegrationException($message);

        if($code !== "0")
            $ReverseTransaction->setAction($message);
        else
            $ReverseTransaction->setAction("Reversal");
//                throw new IntegrationException($message);

        $date = $response['ExpressTransactionDate'] . ' ' . $response['ExpressTransactionTime'];
        $transactionID = $response['Transaction']['TransactionID'];

        $ReverseTransaction->setTransactionID($transactionID);
        $ReverseTransaction->setStatus($code, $message);
        // Store Transaction Result
        $ReverseTransaction->setTransactionDate($date);

        $Order->setStatus("Reversal");
        OrderRow::update($Order);
        TransactionRow::insert($ReverseTransaction);

        // Insert Request
        $Request->setType('transaction');
        $Request->setTypeID($ReverseTransaction->getID());
        $Request->setOrderItemID($Order->getID());
        $Request->setTransactionID($ReverseTransaction->getID());
        if($SessionUser)
            $Request->setUserID($SessionUser->getID());
        IntegrationRequestRow::insert($Request);

        if($Order->getPayeeEmail()) {
            $EmailReceipt = new ReceiptEmail($Order, $MerchantIdentity->getMerchantRow());
            $EmailReceipt->send();
        }

        return $ReverseTransaction;
    }

    /**
     * Void an existing Transaction
     * @param ProtectPayMerchantIdentity|AbstractMerchantIdentity $MerchantIdentity
     * @param OrderRow $Order
     * @param UserRow $SessionUser
     * @param array $post
     * @return mixed
     * @throws IntegrationException
     * @throws \Exception
     * @throws \phpmailerException
     */
    function voidTransaction(AbstractMerchantIdentity $MerchantIdentity, OrderRow $Order, UserRow $SessionUser, Array $post) {
        if(!$Order->getID())
            throw new \InvalidArgumentException("Order must exist in the database");

        $AuthorizedTransaction = $Order->fetchAuthorizedTransaction();
        if(!$AuthorizedTransaction)
            throw new \InvalidArgumentException("Authorized Transaction Not Found for order: " . $Order->getID());

        $Request = IntegrationRequestRow::prepareNew(
            $MerchantIdentity,
            IntegrationRequestRow::ENUM_TYPE_TRANSACTION_VOID
        );

        $url = $this->getRequestURL($MerchantIdentity, $Request);

//        $url = str_replace(':IDENTITY_ID', $MerchantIdentity->getRemoteID(), $url);
        $Request->setRequestURL($url);

        $APIUtil = new ProtectPayAPIUtil();
        if($Order->getEntryMode() == OrderRow::ENUM_ENTRY_MODE_CHECK)
            $request = $APIUtil->prepareCheckVoidRequest($MerchantIdentity, $Order, $AuthorizedTransaction, $post);
        else
            $request = $APIUtil->prepareCreditCardVoidRequest($MerchantIdentity, $Order, $AuthorizedTransaction, $post);
        $Request->setRequest($request);

        $this->execute($MerchantIdentity, $Request);
        $response = $this->parseResponseData($Request);
        $code = $response['ExpressResponseCode'];
        $message = $response['ExpressResponseMessage'];
        if(!$response) //  || !$code || !$message)
            throw new IntegrationException("Invalid response data");

        if($code === '101')
            throw new IntegrationException($message);

        $VoidTransaction = $AuthorizedTransaction->createVoidTransaction();

        $action = "Voided";
        if($code !== "0")
            $action = "Error";


        $date = $response['ExpressTransactionDate'] . ' ' . $response['ExpressTransactionTime'];
        $transactionID = $response['Transaction']['TransactionID'];

        // Store Transaction Result
        $VoidTransaction->setAction($action);
        $VoidTransaction->setStatus($code, $message);
        $VoidTransaction->setTransactionID($transactionID);
        $VoidTransaction->setTransactionDate($date);

        TransactionRow::insert($VoidTransaction);

        $Order->setStatus("Voided");
        OrderRow::update($Order);

        // Insert Request
        $Request->setType('transaction');
        $Request->setTypeID($VoidTransaction->getID());
        $Request->setOrderItemID($Order->getID());
        $Request->setTransactionID($VoidTransaction->getID());
        if($SessionUser)
            $Request->setUserID($SessionUser->getID());
        IntegrationRequestRow::insert($Request);

        if($Order->getPayeeEmail()) {
            $EmailReceipt = new ReceiptEmail($Order, $MerchantIdentity->getMerchantRow());
            $EmailReceipt->send();
        }

        return $VoidTransaction;
    }

    /**
     * Return an existing Transaction
     * @param ProtectPayMerchantIdentity|AbstractMerchantIdentity $MerchantIdentity
     * @param OrderRow $Order
     * @param UserRow $SessionUser
     * @param array $post
     * @return mixed
     * @throws IntegrationException
     * @throws \Exception
     * @throws \phpmailerException
     */
    function returnTransaction(AbstractMerchantIdentity $MerchantIdentity, OrderRow $Order, UserRow $SessionUser, Array $post) {
        if(!$Order->getID())
            throw new \InvalidArgumentException("Order must exist in the database");

        $AuthorizedTransaction = $Order->fetchAuthorizedTransaction();
        if(!$AuthorizedTransaction)
            throw new \InvalidArgumentException("Authorized Transaction Not Found for order: " . $Order->getID());

        $ReturnTransaction = $AuthorizedTransaction->createReturnTransaction();

        $Request = IntegrationRequestRow::prepareNew(
            $MerchantIdentity,
            IntegrationRequestRow::ENUM_TYPE_TRANSACTION_RETURN
        );

        $url = $this->getRequestURL($MerchantIdentity, $Request);

        $Request->setRequestURL($url);

        $APIUtil = new ProtectPayAPIUtil();
        if($Order->getEntryMode() == OrderRow::ENUM_ENTRY_MODE_CHECK)
            $request = $APIUtil->prepareCheckReturnRequest($MerchantIdentity, $Order, $AuthorizedTransaction, $ReturnTransaction, $post);
        else
            $request = $APIUtil->prepareCreditCardReturnRequest($MerchantIdentity, $Order, $AuthorizedTransaction, $ReturnTransaction, $post);
        $Request->setRequest($request);

        $this->execute($MerchantIdentity, $Request);
        $response = $this->parseResponseData($Request);
        $code = $response['ExpressResponseCode'];
        $message = $response['ExpressResponseMessage'];
        if(!$response) //  || !$code || !$message)
            throw new IntegrationException("Invalid response data");

        if($code === '101')
            throw new IntegrationException($message);


        $action = "Return";
        if($code !== "0")
            $action = "Error";

        $date = $response['ExpressTransactionDate'] . ' ' . $response['ExpressTransactionTime'];
        $transactionID = $response['Transaction']['TransactionID'];

        // Store Transaction Result
        $ReturnTransaction->setAction($action);
        $ReturnTransaction->setStatus($code, $message);
        $ReturnTransaction->setTransactionID($transactionID);
        $ReturnTransaction->setTransactionDate($date);

        TransactionRow::insert($ReturnTransaction);

        $Order->setStatus("Return");
        OrderRow::update($Order);

        // Insert Request
        $Request->setType('transaction');
        $Request->setTypeID($ReturnTransaction->getID());
        $Request->setOrderItemID($Order->getID());
        $Request->setTransactionID($ReturnTransaction->getID());
        if($SessionUser)
            $Request->setUserID($SessionUser->getID());
        IntegrationRequestRow::insert($Request);

        if($Order->getPayeeEmail()) {
            $EmailReceipt = new ReceiptEmail($Order, $MerchantIdentity->getMerchantRow());
            $EmailReceipt->send();
        }

        return $ReturnTransaction;
    }


    /**
     * Perform health check on remote api
     * @param ProtectPayMerchantIdentity|AbstractMerchantIdentity $MerchantIdentity
     * @param UserRow $SessionUser
     * @param array $post
     * @return IntegrationRequestRow
     * @throws IntegrationException
     */
    function performHealthCheck(AbstractMerchantIdentity $MerchantIdentity, UserRow $SessionUser, Array $post) {
        throw new IntegrationException("ProPay does not provide a health check API call");
    }

    /**
     * Perform transaction query on remote api
     * @param ProtectPayMerchantIdentity|AbstractMerchantIdentity $MerchantIdentity
     * @param array $post
     * @param UserRow $SessionUser
     * @param Callable $callback
     * @return mixed
     * @throws IntegrationException
     */
    function performTransactionQuery(AbstractMerchantIdentity $MerchantIdentity, UserRow $SessionUser, Array $post, $callback) {
        $Request = IntegrationRequestRow::
        prepareNew(
            $MerchantIdentity,
            IntegrationRequestRow::ENUM_TYPE_TRANSACTION_SEARCH
        );

        $url = $this->getRequestURL($MerchantIdentity, $Request);

        $Request->setRequestURL($url);

        $APIUtil = new ProtectPayAPIUtil();
        $request = $APIUtil->prepareTransactionQueryRequest($MerchantIdentity, $post);
        $Request->setRequest($request);

        $this->execute($MerchantIdentity, $Request);
        $response = $this->parseResponseData($Request);
        $code = $response['ExpressResponseCode'];
        $message = $response['ExpressResponseMessage'];
        if(!$response) //  || !$code || !$message)
            throw new IntegrationException("Invalid response data");

        if($code === '101')
            throw new IntegrationException($message);

        if(empty($response['ReportingData']))
            throw new IntegrationException("Invalid ReportingData");

        $stats = array();
        $stats['total'] = 0;
        $stats['found'] = 0;
        $stats['not_found'] = 0;
        $stats['updated'] = 0;

        if($code === '90')
            return $stats;

        $data = $response['ReportingData'];
        $data = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $data);
        $xml = new \SimpleXMLPropay($data);
        $data = json_decode(json_encode((array)$xml), TRUE);
        $data = $data['Item'];
        if(is_array($data) && key($data) !== 0)
            $data = array($data);

        $stats['total'] = count($data);

        foreach ($data as $i => $item) {
            if(!is_array($item))
                continue;
            try {
                $TransactionRow = TransactionRow::fetchByTransactionID($item['TransactionID']);
                $OrderRow = OrderRow::fetchByID($TransactionRow->getOrderID());
                $ret = $callback($OrderRow, $TransactionRow, $item);
                if ($ret === true)
                    $stats['updated'] += $this->updateTransactionStatus($OrderRow, $TransactionRow, $item) ? 1 : 0;

                $stats['found']++;
                if ($ret === false)
                    break;
            } catch (\InvalidArgumentException $ex) {
                if(strpos($ex->getMessage(), 'not found') === false)
                    throw $ex;
                // Ignore transactions from other gateways
                $stats['not_found']++;
            }
        }
        return $stats;
    }


    protected function updateTransactionStatus(
        OrderRow $OrderRow,
        TransactionRow $TransactionRow,
        Array $Item) {
        $date = date('Y-m-d G:i:s', strtotime($Item['TimeStamp']));
        $ref = $Item['ReferenceNumber'];
        $ticket = $Item['TicketNumber'];

        $updated = false;
        switch($Item['TransactionType']) {
            case 'CreditCardSale':
                switch($Item['TransactionStatus']) {
                    case 'Settled':
                        if($OrderRow->getStatus() !== 'Settled') {
                            $SettledTransaction = $TransactionRow->createSettledTransaction();

                            // Store Transaction Result
                            $SettledTransaction->setAction("Settled");
                            $SettledTransaction->setTransactionDate($date);

                            TransactionRow::insert($SettledTransaction);

                            $OrderRow->setStatus("Settled");

                            $batch_id = $OrderRow->calculateCurrentBatchID();
                            $OrderRow->setBatchID($batch_id);

                            OrderRow::update($OrderRow);
                            $updated = true;
                        }
                        break;
                }
                break;
        }
        return $updated;
    }

    /**
     * Cancel an active subscription
     * @param AbstractMerchantIdentity $MerchantIdentity
     * @param SubscriptionRow $Subscription
     * @param UserRow $SessionUser
     * @param $message
     * @throws \Exception
     * @throws \phpmailerException
     */
    function cancelSubscription(AbstractMerchantIdentity $MerchantIdentity, SubscriptionRow $Subscription, UserRow $SessionUser, $message) {
        $Subscription->cancel($message);

        $Order = OrderRow::fetchByID($Subscription->getOrderID());
        if($Order->getPayeeEmail()) {
            $CancelReceipt = new CancelEmail($Order, $MerchantIdentity->getMerchantRow());
            if(!$CancelReceipt->send())
                error_log($CancelReceipt->ErrorInfo);
        }
    }

    /**
     * Render Charge Form Integration Headers
     * @param AbstractMerchantIdentity $MerchantIdentity
     */
    function renderChargeFormHTMLHeadLinks(AbstractMerchantIdentity $MerchantIdentity) {
        echo <<<HEAD
        <script src="integration/protectpay/view/assets/charge-form-integration.js"></script>
HEAD;
    }

    /**
     * Render Charge Form Hidden Fields
     * @param AbstractMerchantIdentity $MerchantIdentity
     */
    function renderChargeFormHiddenFields(AbstractMerchantIdentity $MerchantIdentity) {

//        $CID = '';
//        $SettingsCipher = '';
//
        echo <<<HEAD
        <input type='hidden' name='CID' value='' />
        <input type='hidden' name='SettingsCipher' value='' />
HEAD;
    }

}

