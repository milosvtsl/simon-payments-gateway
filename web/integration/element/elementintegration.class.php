<?php
/**
 * Created by PhpStorm.
 * Merchant: ari
 * Date: 9/2/2016
 * Time: 11:13 AM
 */
namespace Integration\Element;

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

class ElementIntegration extends AbstractIntegration
{
    const _CLASS = __CLASS__;

    const POST_URL_TRANSACTION = "/express.asmx"; // https://certtransaction.elementexpress.com/express.asmx

    /**
     * @param MerchantRow $Merchant
     * @param IntegrationRow $integrationRow
     * @return AbstractMerchantIdentity
     */
    public function getMerchantIdentity(MerchantRow $Merchant, IntegrationRow $integrationRow) {
        return new ElementMerchantIdentity($Merchant, $integrationRow);
    }

    /**
     * Execute a prepared request
     * @param AbstractMerchantIdentity $MerchantIdentity
     * @param IntegrationRequestRow $Request
     * @throws IntegrationException
     */
    function execute(AbstractMerchantIdentity $MerchantIdentity, IntegrationRequestRow $Request) {
        if(!$Request->getRequest())
            throw new IntegrationException("Request content is empty");
        if($Request->getResponse())
            throw new IntegrationException("This request instance already has a response");

        /** @var IntegrationRow $APIData */
        $APIData = IntegrationRow::fetchByID($Request->getIntegrationID());
        $url = $Request->getRequestURL();
        $userpass = $APIData->getAPIUsername() . ':' . $APIData->getAPIPassword();
        $headers = array(
            "Content-Type: application/soap+xml; charset=utf-8",
            "Content-Length: ". strlen($Request->getRequest())
        );

        // Init curl
        $ch = curl_init();

        // Disable SSL verification
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        // Set CURL options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_USERPWD, $userpass);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $Request->getRequest());

//        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);

        $duration = -microtime(true);
        $response = curl_exec($ch);

        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

        if(!$response) {
            $response = curl_error($ch);
            if($response)
                trigger_error($response);
            $Request->setResult(IntegrationRequestRow::ENUM_RESULT_ERROR);
        } else {

            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $header = substr($response, 0, $header_size);
            if($httpcode !== 200) {
                throw new IntegrationException("Invalid Response: " . $header);
            }
            $body = substr($response, $header_size);
            $response = $body;
        }


        curl_close($ch);

        // Set duration
        $duration += microtime(true);
        $Request->setDuration($duration);

        // Save the response
        $Request->setResponse($response);

//        try {
//             Try parsing the response
//            $Request->parseResponseData();
//            $Request->setResult(IntegrationRequestRow::ENUM_RESULT_FAIL);
//            if($Request->isRequestSuccessful($reason, $code)) {
//                $Request->setResult(IntegrationRequestRow::ENUM_RESULT_SUCCESS);
//            }
//            $Request->setResponseMessage($reason);
//            $Request->setResponseCode($code);
//        } catch (\Exception $ex) {
//            $Request->setResult(IntegrationRequestRow::ENUM_RESULT_ERROR);
//        }

    }


    /**
     * Print an HTML form containing the request fields
     * @param IntegrationRequestRow $Request
     * @return void
     * @throws IntegrationException if the form failed to print
     */
    function printFormHTML(IntegrationRequestRow $Request) {
        switch($Request->getIntegrationType()) {
            case IntegrationRequestRow::ENUM_TYPE_MERCHANT_IDENTITY:
                break;
            case IntegrationRequestRow::ENUM_TYPE_MERCHANT_PROVISION:
                break;
            case IntegrationRequestRow::ENUM_TYPE_MERCHANT_PAYMENT:
                break;
            case IntegrationRequestRow::ENUM_TYPE_TRANSACTION:
                break;
        }
    }


    /**
     * Return the API Request URL for this request
     * @param AbstractMerchantIdentity $MerchantIdentity
     * @param IntegrationRequestRow $Request
     * @return string
     * @throws IntegrationException
     */
    function getRequestURL(AbstractMerchantIdentity $MerchantIdentity, IntegrationRequestRow $Request) {
        $APIData = $MerchantIdentity->getIntegrationRow();
        $url = $APIData->getAPIURLBase() . self::POST_URL_TRANSACTION;
        switch($Request->getIntegrationType()) {
            case IntegrationRequestRow::ENUM_TYPE_TRANSACTION_SEARCH:
                $url = str_replace('transaction.', 'reporting.', $url);
                break;
        }
        return $url;
//            case IntegrationRequestRow::ENUM_TYPE_TRANSACTION_RETURN:
//            case IntegrationRequestRow::ENUM_TYPE_TRANSACTION_VOID:
//            case IntegrationRequestRow::ENUM_TYPE_TRANSACTION:
//                return $APIData->getAPIURLBase() . self::POST_URL_TRANSACTION;
//        }
//        throw new IntegrationException("No API url for this request type");
    }

    /**
     * Parse the response data and return a data object
     * @param IntegrationRequestRow $Request
     * @return mixed
     * @throws IntegrationException if response failed to parse
     */
    function parseResponseData(IntegrationRequestRow $Request) {
        $response = $Request->getResponse();
        if(!$response)
            throw new IntegrationException("Empty Request response");

        $response = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $response);
        $xml = new \SimpleXMLElement($response);
        $body = $xml->xpath('//soapBody')[0] ?: $xml->xpath('//SBody')[0];
        $data = json_decode(json_encode((array)$body), TRUE);
        if(!$data)
            throw new IntegrationException("Response failed to parse SOAP Response");
        if(isset($data['soapFault']))
            throw new IntegrationException($data['soapFault']['soapReason']['soapText']);

        switch($Request->getIntegrationType()) {
            case IntegrationRequestRow::ENUM_TYPE_TRANSACTION:
                $response =
                    @$data['CreditCardSaleResponse']
                    ?: @$data['DebitCardSaleResponse']
                    ?: @$data['CheckSaleResponse'];
                break;

            case IntegrationRequestRow::ENUM_TYPE_TRANSACTION_REVERSAL:
                $response =
                    @$data['CreditCardReversalResponse']
                        ?: @$data['CheckReversalResponse'];
                break;

            case IntegrationRequestRow::ENUM_TYPE_TRANSACTION_RETURN:
                $response =
                    @$data['CreditCardReturnResponse']
                    ?: @$data['CheckReturnResponse'];
                break;

            case IntegrationRequestRow::ENUM_TYPE_TRANSACTION_VOID:
                $response =
                    @$data['CreditCardVoidResponse']
                    ?: @$data['CheckVoidResponse'];
                break;

            case IntegrationRequestRow::ENUM_TYPE_HEALTH_CHECK:
                $response = $data['HealthCheckResponse'];
                break;

            case IntegrationRequestRow::ENUM_TYPE_TRANSACTION_SEARCH:
                $response = $data['TransactionQueryResponse'];
                break;

            default:
                throw new IntegrationException("Invalid integration type: " . $Request->getIntegrationType());
        }
        $response = $response['response'];
        if(!$response)
            throw new IntegrationException("Invalid response key");
        return $response;
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

        /** @var ElementMerchantIdentity $MerchantIdentity */

        $Subscription = null;
        if(!empty($post['recur_count']) && $post['recur_count'] > 0) {
            $Subscription = SubscriptionRow::createSubscriptionFromPost($MerchantIdentity, $Order, $post);
        }

        $Request = IntegrationRequestRow::prepareNew(
            $MerchantIdentity,
            IntegrationRequestRow::ENUM_TYPE_TRANSACTION
        );

        $APIData = IntegrationRow::fetchByID($Request->getIntegrationID());

        $url = $this->getRequestURL($MerchantIdentity, $Request);
//        $url = str_replace(':IDENTITY_ID', $MerchantIdentity->getRemoteID(), $url);
        $Request->setRequestURL($url);

        $APIUtil = new ElementAPIUtil();
        if($Order->getEntryMode() == OrderRow::ENUM_ENTRY_MODE_CHECK)
            $request = $APIUtil->prepareCheckSaleRequest($MerchantIdentity, $Transaction, $Order, $post);
        else
            $request = $APIUtil->prepareCreditCardSaleRequest($MerchantIdentity, $Transaction, $Order, $post);
        $Request->setRequest($request);

        $this->execute($MerchantIdentity, $Request);
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
        /** @var ElementMerchantIdentity $MerchantIdentity */

        $Request = IntegrationRequestRow::prepareNew(
            $MerchantIdentity,
            IntegrationRequestRow::ENUM_TYPE_TRANSACTION_REVERSAL
        );
        $APIData = IntegrationRow::fetchByID($Request->getIntegrationID());
        $url = $this->getRequestURL($APIData, $Request);
        $Request->setRequestURL($url);

        $APIUtil = new ElementAPIUtil();
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
     * @param ElementMerchantIdentity|AbstractMerchantIdentity $MerchantIdentity
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

        $APIUtil = new ElementAPIUtil();
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
     * @param ElementMerchantIdentity|AbstractMerchantIdentity $MerchantIdentity
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

        $APIUtil = new ElementAPIUtil();
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
     * @param ElementMerchantIdentity|AbstractMerchantIdentity $MerchantIdentity
     * @param UserRow $SessionUser
     * @param array $post
     * @return IntegrationRequestRow
     * @throws IntegrationException
     */
    function performHealthCheck(AbstractMerchantIdentity $MerchantIdentity, UserRow $SessionUser, Array $post) {
        $Request = IntegrationRequestRow::prepareNew(
            $MerchantIdentity,
            IntegrationRequestRow::ENUM_TYPE_HEALTH_CHECK
        );
//        $APIData = IntegrationRow::fetchByID($Request->getIntegrationID());
        $url = $this->getRequestURL($MerchantIdentity, $Request);
        $Request->setRequestURL($url);

        $APIUtil = new ElementAPIUtil();
        $request = $APIUtil->prepareHealthCheckRequest($MerchantIdentity, $post);
        $Request->setRequest($request);

        $this->execute($MerchantIdentity, $Request);
        $response = $this->parseResponseData($Request);
        $code = $response['ExpressResponseCode'];
        $message = $response['ExpressResponseMessage'];
        if(!$response) //  || !$code || !$message)
            throw new IntegrationException("Invalid response data");

        if($code === '101')
            throw new IntegrationException($message);

        $Request->setResult(IntegrationRequestRow::ENUM_RESULT_SUCCESS);

        return $Request;
    }


    /**
     * Perform transaction query on remote api
     * @param ElementMerchantIdentity|AbstractMerchantIdentity $MerchantIdentity
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
//        $APIData = IntegrationRow::fetchByID($Request->getIntegrationID());
        $url = $this->getRequestURL($MerchantIdentity, $Request);
        $Request->setRequestURL($url);

        $APIUtil = new ElementAPIUtil();
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
        $xml = new \SimpleXMLElement($data);
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
        // TODO: Implement renderChargeFormHTMLHeadLinks() method.
    }

    /**
     * Render Charge Form Hidden Fields
     * @param AbstractMerchantIdentity $MerchantIdentity
     */
    function renderChargeFormHiddenFields(AbstractMerchantIdentity $MerchantIdentity) {
        // TODO: Implement renderChargeFormHiddenFields() method.
    }
}

