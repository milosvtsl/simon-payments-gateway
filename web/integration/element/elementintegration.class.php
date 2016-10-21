<?php
/**
 * Created by PhpStorm.
 * Merchant: ari
 * Date: 9/2/2016
 * Time: 11:13 AM
 */
namespace Integration\Element;

use Integration\Model;
use Integration\Model\AbstractIntegration;
use Integration\Model\IntegrationRow;
use Integration\Model\Ex\IntegrationException;
use Integration\Request\Model\IntegrationRequestRow;
use Merchant\Model\MerchantRow;
use Integration\Model\AbstractMerchantIdentity;
use Order\Model\OrderRow;
use Transaction\Mail\ReceiptEmail;
use Transaction\Model\TransactionRow;

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
     * @param IntegrationRequestRow $Request
     * @return void
     * @throws IntegrationException if the request execution failed
     */
    function execute(IntegrationRequestRow $Request) {
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

        if(!$response = curl_exec($ch)) {
            $response = curl_error($ch);
            trigger_error($response);
            $Request->setResult(IntegrationRequestRow::ENUM_RESULT_ERROR);
        }
        curl_close($ch);

        // Save the response
        $Request->setResponse($response);

        try {
            // Try parsing the response
            $Request->parseResponseData();
            $Request->setResult(IntegrationRequestRow::ENUM_RESULT_FAIL);
            if($Request->isRequestSuccessful())
                $Request->setResult(IntegrationRequestRow::ENUM_RESULT_SUCCESS);

        } catch (IntegrationException $ex) {
            $Request->setResult(IntegrationRequestRow::ENUM_RESULT_ERROR);
        }

        // Insert Request
        IntegrationRequestRow::insert($Request);

    }

    /**
     * Was this request successful?
     * @param IntegrationRequestRow $Request
     * @param null $reason
     * @return bool
     * @throws IntegrationException
     */
    function isRequestSuccessful(IntegrationRequestRow $Request, &$reason=null) {
        $data = $Request->parseResponseData();
        switch($Request->getIntegrationType()) {
            case IntegrationRequestRow::ENUM_TYPE_TRANSACTION:
                $response = @$data['CreditCardSaleResponse'] ?: @$data['DebitCardSaleResponse'];
                break;

            case IntegrationRequestRow::ENUM_TYPE_TRANSACTION_RETURN:
                $response = $data['CreditCardReturnResponse'];
                break;

            case IntegrationRequestRow::ENUM_TYPE_TRANSACTION_VOID:
                $response = $data['CreditCardVoidResponse'];
                break;

            case IntegrationRequestRow::ENUM_TYPE_HEALTH_CHECK:
                $response = $data['HealthCheckResponse'];
                break;

            case IntegrationRequestRow::ENUM_TYPE_TRANSACTION_SEARCH:
                $response = $data['TransactionQueryResponse'];
                break;

            default:
                return false;
        }
        $response = $response['response'];
        $code = $response['ExpressResponseCode'];
        $reason = $response['ExpressResponseMessage'];

        return $code === '0';
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
     * @param IntegrationRequestRow $Request
     * @return string
     * @throws IntegrationException
     */
    function getRequestURL(IntegrationRequestRow $Request) {
        $APIData = IntegrationRow::fetchByID($Request->getIntegrationID());
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
        return $data;
    }

    /**
     * Create or resume an order item
     * @param AbstractMerchantIdentity $MerchantIdentity
     * @param array $post
     * @return OrderRow
     */
    function createOrResumeOrder(AbstractMerchantIdentity $MerchantIdentity, Array $post) {
        $Order = OrderRow::createOrderFromPost($MerchantIdentity, $post);
        OrderRow::update($Order);
        return $Order;
    }

    /**
     * Submit a new transaction
     * @param AbstractMerchantIdentity $MerchantIdentity
     * @param OrderRow $Order
     * @param array $post
     * @return TransactionRow
     * @throws IntegrationException
     */
    function submitNewTransaction(AbstractMerchantIdentity $MerchantIdentity, OrderRow $Order, Array $post) {
        if(!$Order->getID())
            throw new \InvalidArgumentException("Order must exist in the database");

        // Create Transaction
        $Transaction = TransactionRow::createTransactionFromPost($MerchantIdentity, $Order, $post);
        /** @var ElementMerchantIdentity $MerchantIdentity */


        $Request = IntegrationRequestRow::prepareNew(
            __CLASS__,
            $MerchantIdentity->getIntegrationRow()->getID(),
            IntegrationRequestRow::ENUM_TYPE_TRANSACTION,
            $MerchantIdentity->getMerchantRow()->getID()
        );
        $url = $this->getRequestURL($Request);
//        $url = str_replace(':IDENTITY_ID', $MerchantIdentity->getRemoteID(), $url);
        $Request->setRequestURL($url);

        $APIUtil = new ElementAPIUtil();
        $request = $APIUtil->prepareCreditCardSaleRequest($MerchantIdentity, $Transaction, $Order, $post);
        $Request->setRequest($request);

        $this->execute($Request);
        $data = $this->parseResponseData($Request);
        if(empty($data['CreditCardSaleResponse']) && empty($data['DebitCardSaleResponse']))
            throw new IntegrationException("Invalid response array");

        $response = @$data['CreditCardSaleResponse'] ?: @$data['DebitCardSaleResponse'];
        $response = $response['response'];
        $code = $response['ExpressResponseCode'];
        $message = $response['ExpressResponseMessage'];
        if(!$response) //  || !$code || !$message)
            throw new IntegrationException("Invalid response data");

        if($code !== "0")
            throw new IntegrationException($message);

        $Transaction->setAction("Authorized");
//                throw new IntegrationException($message);

        $date = $response['ExpressTransactionDate'] . ' ' . $response['ExpressTransactionTime'];
        $transactionID = $response['Transaction']['TransactionID'];

        $Transaction->setAuthCodeOrBatchID($code);
        $Transaction->setTransactionID($transactionID);
        $Transaction->setStatus($code, $message);
        // Store Transaction Result
        $Transaction->setTransactionDate($date);

        $Order->setStatus("Authorized");
        OrderRow::update($Order);
        TransactionRow::insert($Transaction);

        if($Order->getPayeeEmail()) {
            $EmailReceipt = new ReceiptEmail($Order, $MerchantIdentity->getMerchantRow());
            $EmailReceipt->send();
        }

        return $Transaction;
    }


    /**
     * Reverse an existing Transaction
     * @param AbstractMerchantIdentity $MerchantIdentity
     * @param OrderRow $Order
     * @param array $post
     * @return mixed
     * @throws IntegrationException
     */
    function reverseTransaction(AbstractMerchantIdentity $MerchantIdentity, OrderRow $Order, Array $post) {
        if(!$Order->getID())
            throw new \InvalidArgumentException("Order must exist in the database");

        if(empty($post['amount']))
            $post['amount'] = $Order->getAmount();

        // Create Transaction
        $ReverseTransaction = TransactionRow::createTransactionFromPost($MerchantIdentity, $Order, $post);
        /** @var ElementMerchantIdentity $MerchantIdentity */

        $Request = IntegrationRequestRow::prepareNew(
            __CLASS__,
            $MerchantIdentity->getIntegrationRow()->getID(),
            IntegrationRequestRow::ENUM_TYPE_TRANSACTION_REVERSAL,
            $MerchantIdentity->getMerchantRow()->getID()
        );
        $url = $this->getRequestURL($Request);
        $Request->setRequestURL($url);

        $APIUtil = new ElementAPIUtil();
        $request = $APIUtil->prepareCreditCardReversalRequest($MerchantIdentity, $ReverseTransaction, $Order, $post);
        $Request->setRequest($request);

        $this->execute($Request);
        $data = $this->parseResponseData($Request);
        if(empty($data['CreditCardReversalResponse']))
            throw new IntegrationException("Invalid CreditCardReversalResponse");

        $response = $data['CreditCardReversalResponse'];
        $response = $response['response'];
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
     * @param array $post
     * @return mixed
     * @throws IntegrationException
     */
    function voidTransaction(AbstractMerchantIdentity $MerchantIdentity, OrderRow $Order, Array $post) {
        if(!$Order->getID())
            throw new \InvalidArgumentException("Order must exist in the database");

        $AuthorizedTransaction = $Order->getAuthorizedTransaction();

        $Request = IntegrationRequestRow::prepareNew(
            __CLASS__,
            $MerchantIdentity->getIntegrationRow()->getID(),
            IntegrationRequestRow::ENUM_TYPE_TRANSACTION_VOID,
            $MerchantIdentity->getMerchantRow()->getID()
        );
        $url = $this->getRequestURL($Request);
//        $url = str_replace(':IDENTITY_ID', $MerchantIdentity->getRemoteID(), $url);
        $Request->setRequestURL($url);

        $APIUtil = new ElementAPIUtil();
        $request = $APIUtil->voidCreditCardRequest($MerchantIdentity, $Order, $AuthorizedTransaction, $post);
        $Request->setRequest($request);

        $this->execute($Request);
        $data = $this->parseResponseData($Request);

        if(empty($data['CreditCardVoidResponse']))
            throw new IntegrationException("Invalid CreditCardVoidResponse");

        $response = $data['CreditCardVoidResponse'];
        $response = $response['response'];
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
     * @param array $post
     * @return mixed
     * @throws IntegrationException
     */
    function returnTransaction(AbstractMerchantIdentity $MerchantIdentity, OrderRow $Order, Array $post) {
        if(!$Order->getID())
            throw new \InvalidArgumentException("Order must exist in the database");

        $AuthorizedTransaction = $Order->getAuthorizedTransaction();

        $Request = IntegrationRequestRow::prepareNew(
            __CLASS__,
            $MerchantIdentity->getIntegrationRow()->getID(),
            IntegrationRequestRow::ENUM_TYPE_TRANSACTION_RETURN,
            $MerchantIdentity->getMerchantRow()->getID()
        );
        $url = $this->getRequestURL($Request);
        $Request->setRequestURL($url);

        $APIUtil = new ElementAPIUtil();
        $request = $APIUtil->returnCreditCardRequest($MerchantIdentity, $Order, $AuthorizedTransaction, $post);
        $Request->setRequest($request);

        $this->execute($Request);
        $data = $this->parseResponseData($Request);

        if(empty($data['CreditCardReturnResponse']))
            throw new IntegrationException("Invalid CreditCardReturnResponse");

        $response = $data['CreditCardReturnResponse'];
        $response = $response['response'];
        $code = $response['ExpressResponseCode'];
        $message = $response['ExpressResponseMessage'];
        if(!$response) //  || !$code || !$message)
            throw new IntegrationException("Invalid response data");

        if($code === '101')
            throw new IntegrationException($message);

        $ReturnTransaction = $AuthorizedTransaction->createReturnTransaction();

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

        if($Order->getPayeeEmail()) {
            $EmailReceipt = new ReceiptEmail($Order, $MerchantIdentity->getMerchantRow());
            $EmailReceipt->send();
        }

        return $ReturnTransaction;
    }


    /**
     * Perform health check on remote api
     * @param ElementMerchantIdentity|AbstractMerchantIdentity $MerchantIdentity
     * @param array $post
     * @return IntegrationRequestRow
     * @throws IntegrationException
     */
    function performHealthCheck(AbstractMerchantIdentity $MerchantIdentity, Array $post) {
        $Request = IntegrationRequestRow::prepareNew(
            __CLASS__,
            $MerchantIdentity->getIntegrationRow()->getID(),
            IntegrationRequestRow::ENUM_TYPE_HEALTH_CHECK,
            $MerchantIdentity->getMerchantRow()->getID()
        );
        $url = $this->getRequestURL($Request);
        $Request->setRequestURL($url);

        $APIUtil = new ElementAPIUtil();
        $request = $APIUtil->prepareHealthCheckRequest($MerchantIdentity, $post);
        $Request->setRequest($request);

        $this->execute($Request);
        $data = $this->parseResponseData($Request);

        if(empty($data['HealthCheckResponse']))
            throw new IntegrationException("Invalid HealthCheckResponse");

        $response = $data['HealthCheckResponse'];
        $response = $response['response'];
        $code = $response['ExpressResponseCode'];
        $message = $response['ExpressResponseMessage'];
        if(!$response) //  || !$code || !$message)
            throw new IntegrationException("Invalid response data");

        if($code === '101')
            throw new IntegrationException($message);

        return $Request;
    }

    /**
     * Perform transaction query on remote api
     * @param ElementMerchantIdentity|AbstractMerchantIdentity $MerchantIdentity
     * @param array $post
     * @param Callable $callback
     * @return mixed
     * @throws IntegrationException
     */
    function performTransactionQuery(AbstractMerchantIdentity $MerchantIdentity, Array $post, $callback=null) {
        $Request = IntegrationRequestRow::prepareNew(
            __CLASS__,
            $MerchantIdentity->getIntegrationRow()->getID(),
            IntegrationRequestRow::ENUM_TYPE_TRANSACTION_SEARCH,
            $MerchantIdentity->getMerchantRow()->getID()
        );
        $url = $this->getRequestURL($Request);
        $Request->setRequestURL($url);

        $APIUtil = new ElementAPIUtil();
        $request = $APIUtil->prepareTransactionQueryRequest($MerchantIdentity, $post);
        $Request->setRequest($request);

        $this->execute($Request);
        $response = $this->parseResponseData($Request);

        if(empty($response['TransactionQueryResponse']))
            throw new IntegrationException("Invalid TransactionQueryResponse");

        $response = $response['TransactionQueryResponse'];
        $response = $response['response'];
        $code = $response['ExpressResponseCode'];
        $message = $response['ExpressResponseMessage'];
        if(!$response) //  || !$code || !$message)
            throw new IntegrationException("Invalid response data");

        if($code === '101')
            throw new IntegrationException($message);

        if(empty($response['ReportingData']))
            throw new IntegrationException("Invalid ReportingData");

        $data = $response['ReportingData'];

        $data = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $data);
        $xml = new \SimpleXMLElement($data);
        $data = json_decode(json_encode((array)$xml), TRUE);
        $data = $data['Item'];
        foreach($data as $i => $item) {
            try {
                $TransactionRow = TransactionRow::fetchByTransactionID($item['TransactionID']);
                $OrderRow = OrderRow::fetchByID($TransactionRow->getOrderID());
                $this->updateTransactionStatus($OrderRow, $TransactionRow, $item);
                if ($callback !== null)
                    if (false === $callback($OrderRow, $TransactionRow, $item))
                        break;
            } catch (\InvalidArgumentException $ex) {
                // Ignore transactions from other gateways
            }
        }

        return $Request;
    }


    protected function updateTransactionStatus(
        OrderRow $OrderRow,
        TransactionRow $TransactionRow,
        Array $Item) {
        $date = date('Y-m-d G:i:s', strtotime($Item['TimeStamp']));
        $ref = $Item['ReferenceNumber'];
        $ticket = $Item['TicketNumber'];

        switch($Item['TransactionType']) {
            case 'CreditCardSale':
                switch($Item['TransactionStatus']) {
                    case 'Settled':
                        if($OrderRow->getStatus() !== 'Authorized') {
                            $SettledTransaction = $TransactionRow->createSettledTransaction();

                            // Store Transaction Result
                            $SettledTransaction->setAction("Settled");
                            $SettledTransaction->setTransactionDate($date);

                            TransactionRow::insert($SettledTransaction);

                            $OrderRow->setStatus("Settled");
                            OrderRow::update($OrderRow);

                            // TODO: alert that an update was made?
                        }
                        break;
                }
                break;
        }
    }
}

