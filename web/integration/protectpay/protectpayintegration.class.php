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
//    const POST_URL_MERCHANT_IDENTITY = "/ProtectPay/Payers/";
    const POST_URL_MERCHANT_IDENTITY = "/ProtectPay/MerchantProfiles/";
    const POST_URL_TRANSACTION_CREATE = "/ProtectPay/Payers/{PayerID}/PaymentMethods/";
    const POST_URL_TRANSACTION_AUTHORIZE = "/ProtectPay/Payers/{PayerID}/PaymentMethods/AuthorizedTransactions/";
    const POST_URL_TRANSACTION_AUTHORIZE_AND_CAPTURE = "/ProtectPay/Payers/{PayerID}/PaymentMethods/AuthorizedAndCapturedTransactions/";
    const POST_URL_TRANSACTION_REFUND = "/ProtectPay/Payers/{PayerID}/PaymentMethods/RefundTransactions/";
    const POST_URL_TRANSACTION_CAPTURE = "/ProtectPay/Payers/{PayerID}/PaymentMethods/CapturedTransactions/";
    const POST_URL_TRANSACTION_VOID = "/ProtectPay/Payers/{PayerID}/PaymentMethods/VoidedTransactions/";
    const POST_URL_TRANSACTION_SPLITPAY = "/ProtectPay/Payers/{PayerID}/PaymentMethods/ProcessedSplitPayTransactions/";

    const POST_URL_TRANSACTION_TEMP_TOKEN = "/ProtectPay/TempTokens/?payerName={payerName}&durationSeconds={durationSeconds}";


    /**
     * @param MerchantRow $Merchant
     * @param IntegrationRow $integrationRow
     * @return AbstractMerchantIdentity
     */
    public function getMerchantIdentity(MerchantRow $Merchant, IntegrationRow $integrationRow) {
        return new ProtectPayMerchantIdentity($Merchant, $integrationRow);
    }

    /**
     * Execute a prepared request
     * @param IntegrationRequestRow $Request
     * @return void
     * @throws IntegrationException if the request execution failed
     */
    function execute(IntegrationRequestRow $Request) {
        if(!$Request->getRequest())
            throw new IntegrationException("Request content is empty");
        if($Request->getResponse())
            throw new IntegrationException("This request instance already has a response");

        $APIUtil = new ProtectPayAPIUtil();

        $duration = -microtime(true);
        $response = $APIUtil->executeAPIRequest($Request);

        // Set duration
        $duration += microtime(true);
        $Request->setDuration($duration);

        // Save the response
        $Request->setResponse($response);

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
     * @param array $post
     * @return Array
     * @throws IntegrationException
     *
     * Encryption Process
     * Encrypt the Key-Value Pair using the following method:
     * 1. UTF-8 encode the TempToken string and generate an MD5 hash of it.
     * 2. UTF-8 encode the Key-Value Pair string and encrypt using AES-128 encryption using Cipher Block Chaining (CBC) mode.
     *      a. Set both the key and initialization vector (IV) equal to result from step 1.
     * 3. Base64 encode the result of 2
     */
    public function requestTempToken(ProtectPayMerchantIdentity $MerchantIdentity, Array $post) {
        $Request = IntegrationRequestRow::prepareNew(
            $MerchantIdentity,
            IntegrationRequestRow::ENUM_TYPE_TRANSACTION_TEMP_TOKEN
        );

        $Name = $post['payee_full_name'];

        $APIData = $MerchantIdentity->getIntegrationRow();
        $url = $APIData->getAPIURLBase() . self::POST_URL_TRANSACTION_TEMP_TOKEN;
        $url = str_replace('{payerName}', $Name, $url);
        $url = str_replace('{durationSeconds}', 600, $url);
        $Request->setRequestURL($url);

//        $APIUtil = new ProtectPayAPIUtil();
        $request = null; // $APIUtil->prepareTempTokenRequest($MerchantIdentity, $PayerID, $Name);
        $Request->setRequest($request);

        $this->execute($Request);

        // Try parsing the response
        $data = json_decode($Request->getResponse(), true);
        $Request->setResponseMessage($data['RequestResult']['ResultValue']);
        $Request->setResponseCode($data['RequestResult']['ResultCode']);

        if($Request->getResponseCode() !== '00')
            throw new IntegrationException($Request->getResponseCode() . ' : ' . $Request->getResponseMessage());

        $Request->setResult(IntegrationRequestRow::ENUM_RESULT_SUCCESS);

        $TempToken = $data['TempToken'];
        $TempTokenMD5 = md5($TempToken);
//        $data['TempTokenMD5'] = $TempTokenMD5;

        $PayerId = $data['PayerId'];
        $CID = $data['CredentialId'];
        $data['CID'] = $CID;

        $KeyValuePairs = array(
            'AuthToken'=> $MerchantIdentity->getAuthenticationToken(), // '1f25d31c-e8fe-4d68-be73-f7b439bfa0a329e90de6-4e93-4374-8633-22cef77467f5',
            'PayerID' => $PayerId, // '2833955147881261',
            'Amount' => @$post['amount'],
            'CurrencyCode' => 'USD',
            'ProcessMethod' => 'Capture',
            'PaymentMethodStorageOption' => 'None',
            'InvoiceNumber' => @$post['invoice_number'],
            'Comment1' => @$post['notes'],
            'Comment2' => '',
//            'echo' => 'echotest',
            'ReturnURL' => 'https://access.simonpayments.com/integration/protectpay/return.php',
            'ProfileId' => '3351',
            'PaymentProcessType' => 'CreditCard',
            'StandardEntryClassCode' => 'WEB',
            'DisplayMessage' => 'True',
            'Protected' => 'False',
        );

        $KeyValuePairString = http_build_query($KeyValuePairs);
        $iv = $TempTokenMD5; // '12345678';
        $passphrase = $TempTokenMD5; // '8chrsLng';

        $enc = mcrypt_encrypt(MCRYPT_BLOWFISH, $passphrase, $KeyValuePairString, MCRYPT_MODE_CBC, $iv);
        $SettingsCipher = base64_encode($enc);

        $data['SettingsCipher'] = $SettingsCipher;
        $data['merchant_uid'] = $MerchantIdentity->getMerchantRow()->getUID();
        $data['integration_uid'] = $MerchantIdentity->getIntegrationRow()->getUID();

        // New PayerId created. Store in session until transaction completes
        if(empty($_SESSION[__FILE__]))
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
     * The ‘ResponseCipher’ is encrypted using the same process and TempToken used to encrypt the ‘SettingsCipher’.
     * 1. Base64 decode the response cipher.
     * 2. UTF-8 encode the same TempToken used to encrypt and generate an MD5 hash of it.
     * 3. Decrypt the result of step 1 using AES-128 decryption using Cipher Block Chaining (CBC) mode.
     * a. Set both the Key and Initialization Vector (IV) equal to result from step 2.
     * ? The decrypted response will be in the form of Key-Value Pairs and contain the response of the requested transaction.
     */
    static function processResponseCipher($CID, $ResponseCipher) {
        $data = self::getSessionTempToken($CID);

        $merchant_uid = $data['merchant_uid'];
        $MerchantRow = MerchantRow::fetchByUID($merchant_uid);

        $integration_uid = $data['integration_uid'];
        $IntegrationRow = IntegrationRow::fetchByUID($integration_uid);

        /** @var ProtectPayIntegration $Integration */
        $Integration = $IntegrationRow->getIntegration();
        if(! $Integration instanceof ProtectPayIntegration)
            throw new \Exception("Not a protectpay integration: " . $integration_uid);

        /** @var ProtectPayMerchantIdentity $MerchantIdentity */
        $MerchantIdentity = $Integration->getMerchantIdentity($MerchantRow, $IntegrationRow);

        $TempToken = $data['TempToken'];
        $TempTokenMD5 = md5($TempToken);
        $passphrase = $TempTokenMD5;
        $iv = $TempTokenMD5;
        $SettingsCipher = $data['SettingsCipher'];
        $SettingsCipher = base64_decode($SettingsCipher);

        $KeyValuePairString = mcrypt_encrypt(MCRYPT_BLOWFISH, $passphrase, $SettingsCipher, MCRYPT_MODE_CBC, $iv);
        $res = array();
        parse_str($KeyValuePairString, $res);


        $post = array(
            'amount' => $res['Action'],
        );

        //    Action=Complete
        //    Echo=echotest
        //    PayerID=6192936083671743
        //    ObfuscatedAccountNumber=474747******4747
        //    ExpireDate=1215
        //    CardholderName=John Q Test
        //    Address1=123 A St.,
        //    Address2=
        //    Address3=
        //    City=Orem
        //    State=UT
        //    PostalCode=84058
        //    Country=USA
        //    PaymentMethodId=bb466e8d-0cdb-44db-8ef4-939207c204b3
        //    ProcessResult=Success
        //    ProcessResultAuthorizationCode=A11111
        //    ProcessResultAVSCode=T
        //    ProcessResultResultCode=00
        //    ProcessResultResultMessage=
        //    ProcessResultTransactionHistoryID=7909962
        //    ProcessResultTransactionId=524
        //    Amount=10.00
        //    GrossAmt=10.00
        //    NetAmt=9.32
        //    PerTransFee=0.35
        //    Rate=3.25
        //    GrossAmtLessNetAmt=0.68
        //    Example of a decrypted response for successful tokenization, but declined card transaction:
        //    Action=Complete
        //    &Echo=echotest
        //    &PayerID=2833955147881261
        //    &ObfuscatedAccountNumber=474747******4747
        //    &ExpireDate=1212
        //    &CardholderName=John Q Test
        //    &Address1=123 A St.,
        //    &Address2=
        //    &Address3=
        //    &City=Orem
        //    &State=UT
        //    &PostalCode=84058
        //    &Country=USA
        //    &PaymentMethodId=58bff1ed-e8a7-44e2-bce3-71a389e86eec
        //    &ProcErrCode=51
        //    &ProcErrMsg=Insufficient funds/over credit limit
        //    Example of a decrypted response of a storage error:
        //    Action=Complete
        //    &Echo=echotest
        //    &PayerID=7588958043622683
        //    &ExpireDate=1212
        //    &CardholderName=John Q Test
        //    &Address1=123 A St.,
        //    &Address2=
        //    &Address3=
        //    &City=Orem
        //    &State=UT
        //    &PostalCode=84058
        //    &Country=USA
        //    &StoreErrCode=308
        //    &StoreErrMsg=CreditCard number is invalid for specified type
        //                                          Example of a decrypted response with an error with the SPI request:
        //    ErrCode=301
        //    &ErrMsg=Invalid Settings Cipher
        //    Example of a decrypted response with multiple SPI request error codes:
        //    Action=Err
        //    &ErrCode0=301
        //    &ErrMsg0=Invalid Bank AccountNumber
        //    &ErrCode1=301
        //    &ErrMsg1=Invalid RoutingNumber
        //    &ErrCode2=301
        //    &ErrMsg2=Invalid BankAccountType
        //    &ErrCode3





        // Insert custom order fields

        foreach($OrderForm->getAllCustomFields(false) as $customField) {
            if(!empty($post[$customField])) {
                $Order->insertCustomField($customField, $post[$customField]);
            }
        }



    }

    /**
     * @param $CID
     * @param bool $remove
     * @return Array
     * @throws IntegrationException
     */
    public static function getSessionTempToken($CID, $remove=true) {
        if(empty($_SESSION[__FILE__]))
            throw new IntegrationException("No temp tokens were created for this session");

        if(empty($_SESSION[__FILE__][$CID]))
            throw new IntegrationException("Temp Token was not found: " . $CID);

        $data = $_SESSION[__FILE__][$CID];
        if($remove)
            unset($_SESSION[__FILE__][$CID]);
        return $data;
    }

    /**
     * Submit a new transaction
     * @param AbstractMerchantIdentity $MerchantIdentity
     * @param OrderRow $Order
     * @param UserRow $SessionUser
     * @param array $post
     * @return TransactionRow
     * @throws IntegrationException
     * @throws \Exception
     * @throws \phpmailerException
     */
    function submitNewTransaction(AbstractMerchantIdentity $MerchantIdentity, OrderRow $Order, UserRow $SessionUser, Array $post) {

        // Perform Fraud Scrubbing
        $Order->performFraudScrubbing($MerchantIdentity, $SessionUser, $post);

        OrderRow::insertOrUpdate($Order);
        if(!$Order->getID())
            throw new \InvalidArgumentException("Order must exist in the database");

        // Create Transaction
        $Transaction = TransactionRow::createTransactionFromPost($MerchantIdentity, $Order, $post);
        $service_fee = $MerchantIdentity->calculateServiceFee($Order, 'Authorized');
        $Transaction->setServiceFee($service_fee);

        /** @var ProtectPayMerchantIdentity $MerchantIdentity */

        $Subscription = null;
        if(!empty($post['recur_count']) && $post['recur_count'] > 0) {
            $Subscription = SubscriptionRow::createSubscriptionFromPost($MerchantIdentity, $Order, $post);
        }

        $Request = IntegrationRequestRow::prepareNew(
            $MerchantIdentity,
            IntegrationRequestRow::ENUM_TYPE_TRANSACTION
        );

        $url = $this->getRequestURL($MerchantIdentity, $Request);

//        $url = str_replace(':IDENTITY_ID', $MerchantIdentity->getRemoteID(), $url);
        $Request->setRequestURL($url);

        $APIUtil = new ProtectPayAPIUtil();
        if($Order->getEntryMode() == OrderRow::ENUM_ENTRY_MODE_CHECK)
            $request = $APIUtil->prepareCheckSaleRequest($MerchantIdentity, $Transaction, $Order, $post);
        else
            $request = $APIUtil->prepareSaleRequest($MerchantIdentity, $Transaction, $Order, $post);
        $Request->setRequest($request);

        $this->execute($Request);
        IntegrationRequestRow::insert($Request);

        $response = $this->parseResponseData($Request);
        if(!$response) //  || !$code || !$message)
            throw new IntegrationException("Invalid response data");

        $code = $response['ExpressResponseCode'];
        $message = $response['ExpressResponseMessage'];
        if($code !== "0")
            throw new IntegrationException($message);

        $Transaction->setAction("Authorized");
        $Order->setStatus("Authorized");
//                throw new IntegrationException($message);

        $date = $response['ExpressTransactionDate'] . ' ' . $response['ExpressTransactionTime'];
        $transactionID = $response['Transaction']['TransactionID'];

        $Transaction->setAuthCodeOrBatchID($code);
        $Transaction->setTransactionID($transactionID);
        $Transaction->setStatus($code, $message);
        // Store Transaction Result
        $Transaction->setTransactionDate($date);


        // Insert Transaction
        TransactionRow::insert($Transaction);

        // Insert Subscription
        if($Subscription) {
            SubscriptionRow::insert($Subscription);
            $Order->setSubscriptionID($Subscription->getID());
        }

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

        return $Transaction;
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

        $this->execute($Request);

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

        $ReverseTransaction->setAuthCodeOrBatchID($code);
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

        $this->execute($Request);
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
        $VoidTransaction->setAuthCodeOrBatchID($code);
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

        $this->execute($Request);
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
        $ReturnTransaction->setAuthCodeOrBatchID($code);
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

        $this->execute($Request);
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
//        echo <<<HEAD
//        <input type='hidden' name='CID' value='$CID' />
//        <input type='hidden' name='SettingsCipher' value='$SettingsCipher' />
//HEAD;
    }

}

