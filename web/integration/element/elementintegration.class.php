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
use Transaction\Model\TransactionRow;

class ElementIntegration extends AbstractIntegration
{
    const _CLASS = __CLASS__;

    const POST_URL_IDENTITIES = "/identities/";
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
            case IntegrationRequestRow::ENUM_TYPE_MERCHANT_IDENTITY:
                if(!empty($data['id']))
                    return true;
                $reason = "Missing 'id' field";
                return false;
            case IntegrationRequestRow::ENUM_TYPE_MERCHANT_PAYMENT:
                if(!empty($data['fingerprint']))
                    return true;
                $reason = "Missing 'fingerprint' field";
                return false;
            case IntegrationRequestRow::ENUM_TYPE_MERCHANT_PROVISION:
                if(!empty($data['identity']))
                    return true;
                $reason = "Missing 'identity' field";
                return false;
            case IntegrationRequestRow::ENUM_TYPE_TRANSACTION:
                if(empty($data['CreditCardSaleResponse']) && empty($data['DebitCardSaleResponse']))
                    throw new IntegrationException("Invalid response array");

                $response = @$data['CreditCardSaleResponse'] ?: @$data['DebitCardSaleResponse'];
                $response = $response['response'];
                $code = $response['ExpressResponseCode'];
                $reason = $response['ExpressResponseMessage'];

                return $code === '0';
        }
        return false;
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
        switch($Request->getIntegrationType()) {
            case IntegrationRequestRow::ENUM_TYPE_TRANSACTION:
                return $APIData->getAPIURLBase() . self::POST_URL_TRANSACTION;
            case IntegrationRequestRow::ENUM_TYPE_MERCHANT_IDENTITY:
                return $APIData->getAPIURLBase() . self::POST_URL_IDENTITIES;
        }
        throw new IntegrationException("No API url for this request type");
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
     * Submit a new transaction
     * @param AbstractMerchantIdentity $MerchantIdentity
     * @param array $post
     * @return TransactionRow
     */
    function submitNewTransaction(AbstractMerchantIdentity $MerchantIdentity, Array $post) {
        $Order = OrderRow::createOrderFromPost($MerchantIdentity, $post);

        // Capture Order
        OrderRow::update($Order);

        // Create Transaction
        $Transaction = TransactionRow::createTransactionFromPost($MerchantIdentity, $Order, $post);
//        try {
            /** @var ElementMerchantIdentity $MerchantIdentity */
            $Request = $this->prepareTransactionRequest($MerchantIdentity, $Transaction, $Order, $post);
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

            if($code === '101')
                throw new IntegrationException($message);

            if($code !== "0")
                $Transaction->setAction("Error");
            else
                $Transaction->setAction("Authorized");
//                throw new IntegrationException($message);

            $date = $response['ExpressTransactionDate'] . ' ' . $response['ExpressTransactionTime'];
            $transactionID = $response['Transaction']['TransactionID'];

            $Transaction->setAuthCodeOrBatchID($code);
            $Transaction->setTransactionID($transactionID);
            $Transaction->setStatus($code, $message);
            // Store Transaction Result
            $Transaction->setTransactionDate($date);

            $Order->setStatus("Settled");
            OrderRow::update($Order);

//        } catch (IntegrationException $Ex) {
            // Catch Integration Exception
//            $Transaction->setAction("Error");
//            $Transaction->setAuthCodeOrBatchID(-1);
//            $Transaction->setStatus(-1, $Ex->getMessage());
//            TransactionRow::insert($Transaction);
//            throw $Ex;
//        }
        TransactionRow::insert($Transaction);
        return $Transaction;
    }


    public function prepareTransactionRequest(
        ElementMerchantIdentity $MerchantIdentity,
        TransactionRow $TransactionRow,
        OrderRow $OrderRow, $post) {

        $NewRequest = IntegrationRequestRow::prepareNew(
            __CLASS__,
            $MerchantIdentity->getIntegrationRow()->getID(),
            IntegrationRequestRow::ENUM_TYPE_TRANSACTION,
            $MerchantIdentity->getMerchantRow()->getID()
        );
        $url = $this->getRequestURL($NewRequest);
//        $url = str_replace(':IDENTITY_ID', $MerchantIdentity->getRemoteID(), $url);
        $NewRequest->setRequestURL($url);

        $APIUtil = new ElementAPIUtil();
        $request = $APIUtil->prepareCreditCardSaleRequest($MerchantIdentity, $TransactionRow, $OrderRow, $post);
        $NewRequest->setRequest($request);

        return $NewRequest;
    }
}

