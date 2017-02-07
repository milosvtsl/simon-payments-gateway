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
use Integration\Model\AbstractMerchantIdentity;
use Integration\Model\Ex\IntegrationException;
use Integration\Model\IntegrationRow;
use Integration\Request\Model\IntegrationRequestRow;
use Merchant\Model\MerchantFormRow;
use Merchant\Model\MerchantRow;
use Order\Model\OrderRow;
use Order\Model\TransactionRow;
use Payment\Model\PaymentRow;
use Subscription\Mail\CancelEmail;
use Subscription\Model\SubscriptionRow;
use User\Model\UserRow;

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
     * @param OrderRow $Order
     * @param UserRow $SessionUser
     * @param array $post
     * @return TransactionRow
     * @throws IntegrationException
     */
    function submitNewTransaction(AbstractMerchantIdentity $MerchantIdentity, OrderRow $Order, UserRow $SessionUser, Array $post) {
        OrderRow::insertOrUpdate($Order);
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
     * Void an existing Transaction
     * @param AbstractMerchantIdentity $MerchantIdentity
     * @param OrderRow $OrderRow
     * @param array $post
     * @return mixed
     */
    function voidTransaction(AbstractMerchantIdentity $MerchantIdentity, OrderRow $OrderRow, UserRow $SessionUser, Array $post) {
        throw new \InvalidArgumentException("TODO: Not yet implemented");
    }

    /**
     * Reverse an existing Transaction
     * @param AbstractMerchantIdentity $MerchantIdentity
     * @param OrderRow $Order
     * @param array $post
     * @return mixed
     */
    function reverseTransaction(AbstractMerchantIdentity $MerchantIdentity, OrderRow $Order, UserRow $SessionUser, Array $post) {
        throw new \InvalidArgumentException("TODO: Not yet implemented");
    }

    /**
     * Return an existing Transaction
     * @param AbstractMerchantIdentity $MerchantIdentity
     * @param OrderRow $Order
     * @param array $post
     * @return mixed
     */
    function returnTransaction(AbstractMerchantIdentity $MerchantIdentity, OrderRow $Order, UserRow $SessionUser, Array $post) {
        throw new \InvalidArgumentException("TODO: Not yet implemented");
    }

    /**
     * Perform health check on remote api
     * @param FinixMerchantIdentity|AbstractMerchantIdentity $MerchantIdentity
     * @param array $post
     * @return IntegrationRequestRow
     * @throws IntegrationException
     */
    function performHealthCheck(AbstractMerchantIdentity $MerchantIdentity, UserRow $SessionUser, Array $post) {
        throw new \InvalidArgumentException("TODO: Not yet implemented");
    }


    /**
     * Perform transaction query on remote api
     * @param AbstractMerchantIdentity $MerchantIdentity
     * @param array $post
     * @param null $callback
     * @return bool
     */
    function performTransactionQuery(AbstractMerchantIdentity $MerchantIdentity, UserRow $SessionUser, Array $post, $callback) {
        throw new \InvalidArgumentException("TODO: Not yet implemented");
    }


    /**
     * Cancel an active subscription
     * @param AbstractMerchantIdentity $MerchantIdentity
     * @param SubscriptionRow $Subscription
     * @param $message
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

