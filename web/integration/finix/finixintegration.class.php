<?php
/**
 * Created by PhpStorm.
 * Merchant: ari
 * Date: 9/2/2016
 * Time: 11:13 AM
 */
namespace Integration\Finix;

use Integration\Model;
use Integration\Model\AbstractIntegration;
use Integration\Model\IntegrationRow;
use Integration\Model\Ex\IntegrationException;
use Integration\Request\Model\IntegrationRequestRow;
use Merchant\Model\MerchantRow;
use Integration\Model\AbstractMerchantIdentity;
use Order\Model\OrderRow;
use Transaction\Model\TransactionRow;

// https://finix-payments.github.io/simonpay-docs/?shell#step-1-create-an-identity-for-a-merchant
class FinixIntegration extends AbstractIntegration
{
    const _CLASS = __CLASS__;

    const POST_URL_IDENTITIES = "/identities/";
    const POST_URL_PAYMENT_INSTRUMENT = "/payment_instruments/";
    const POST_URL_MERCHANT_PROVISION = "/identities/:IDENTITY_ID/merchants/";


    /**
     * @param MerchantRow $Merchant
     * @param IntegrationRow $integrationRow
     * @return AbstractMerchantIdentity
     */
    public function getMerchantIdentity(MerchantRow $Merchant, IntegrationRow $integrationRow) {
        return new FinixMerchantIdentity($Merchant, $integrationRow);
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
            "Content-Type: application/vnd.json+api",
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

        $error = null;
        try {
            // Try parsing the response
            $Request->parseResponseData();
            $Request->setResult(IntegrationRequestRow::ENUM_RESULT_FAIL);
            if($Request->isRequestSuccessful($error))
                $Request->setResult(IntegrationRequestRow::ENUM_RESULT_SUCCESS);

        } catch (IntegrationException $ex) {
            $error = $ex->getMessage();
            $Request->setResult(IntegrationRequestRow::ENUM_RESULT_ERROR);
        }

        // Insert Request
        IntegrationRequestRow::insert($Request);

        if($Request->getResult() !== IntegrationRequestRow::ENUM_RESULT_SUCCESS)
            throw new IntegrationException($error);
    }

    /**
     * Was this request successful?
     * @param IntegrationRequestRow $Request
     * @param null $reason
     * @return bool
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
                return false;
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
            case IntegrationRequestRow::ENUM_TYPE_MERCHANT_IDENTITY:
                return $APIData->getAPIURLBase() . self::POST_URL_IDENTITIES;
            case IntegrationRequestRow::ENUM_TYPE_MERCHANT_PAYMENT:
                return $APIData->getAPIURLBase() . self::POST_URL_PAYMENT_INSTRUMENT;
            case IntegrationRequestRow::ENUM_TYPE_MERCHANT_PROVISION:
                return $APIData->getAPIURLBase() . self::POST_URL_MERCHANT_PROVISION; // TODO: parse? no, elsewhere
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
        $data = json_decode($response, true);
        if(!$data)
            throw new IntegrationException("Response failed to parse JSON");

        $errorMessage = null;
        if(!empty($data['_embedded'])) {
            if(!empty($data['_embedded']['errors'])) {
                foreach($data['_embedded']['errors'] as $i => $errInfo) {
                    $errorMessage .= ($errorMessage ? "\n" : '') . '#' . ($i+1) . ' ' . $errInfo['code'] . ': ' . $errInfo['message'];
                }
            }
        }

        if($errorMessage)
            throw new IntegrationException($errorMessage);

//        if(empty($data['entity']))
//            throw new IntegrationException("Missing response key: 'entity'");
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
        OrderRow::insert($Order);

        // Create Transaction
        $Transaction = TransactionRow::createTransactionFromPost($MerchantIdentity, $Order, $post);
        try {
            // Store Transaction Result
            $Transaction->setAction("Authorized");
            $Transaction->setAuthCodeOrBatchID("Authorized");
            $Transaction->setStatus("Success", "Mock Transaction Approved");

        } catch (IntegrationException $Ex) {
            // Catch Integration Exception
            $Transaction->setAction("Error");
            $Transaction->setAuthCodeOrBatchID("Authorized");
            $Transaction->setStatus("Error", $Ex->getMessage());

        }
        TransactionRow::insert($Transaction);
        return $Transaction;
    }
}

