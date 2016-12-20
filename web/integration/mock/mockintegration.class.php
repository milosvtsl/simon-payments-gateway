<?php
/**
 * Created by PhpStorm.
 * Merchant: ari
 * Date: 9/2/2016
 * Time: 11:13 AM
 */
namespace Integration\Mock;

use Integration\Model;
use Integration\Model\AbstractIntegration;
use Integration\Model\IntegrationRow;
use Integration\Model\Ex\IntegrationException;
use Integration\Request\Model\IntegrationRequestRow;
use Merchant\Model\MerchantRow;
use Integration\Model\AbstractMerchantIdentity;
use Order\Model\OrderRow;
use Subscription\Model\SubscriptionRow;
use Order\Model\TransactionRow;
use User\Model\UserRow;

class MockIntegration extends AbstractIntegration
{
    const _CLASS = __CLASS__;


    /**
     * @param MerchantRow $Merchant
     * @param IntegrationRow $integrationRow
     * @return AbstractMerchantIdentity
     */
    public function getMerchantIdentity(MerchantRow $Merchant, IntegrationRow $integrationRow) {
        return new MockMerchantIdentity($Merchant, $integrationRow);
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
//        if($Request->getResponse())
//            throw new IntegrationException("This request instance already has a response");

        // Save the response
        $Request->setResponse($Request->getRequest());
        $Request->setResult(IntegrationRequestRow::ENUM_RESULT_SUCCESS);

        // Insert Request
        IntegrationRequestRow::insert($Request);

    }

    /**
     * Was this request successful?
     * @param IntegrationRequestRow $Request
     * @param null $reason
     * @param null $code
     * @return bool
     */
    function isRequestSuccessful(IntegrationRequestRow $Request, &$reason = null, &$code = null) {
        return true;
    }

    /**
     * Print an HTML form containing the request fields
     * @param IntegrationRequestRow $Request
     * @return void
     * @throws IntegrationException if the form failed to print
     */
    function printFormHTML(IntegrationRequestRow $Request) {

    }

    /**
     * Return the API Request URL for this request
     * @param IntegrationRequestRow $Request
     * @return string
     * @throws IntegrationException
     */
    function getRequestURL(IntegrationRequestRow $Request) {
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

        if(empty($data['entity']))
            throw new IntegrationException("Missing response key: 'entity'");
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
        if(!$Order->getID())
            throw new \InvalidArgumentException("Order must exist in the database");

        // Create Transaction
        $Transaction = TransactionRow::createTransactionFromPost($MerchantIdentity, $Order, $post);

        // Store Transaction Result
        $Transaction->setAction("Mock Authorized");
        $Transaction->setAuthCodeOrBatchID("Authorized");
        $Transaction->setStatus("Success", "Mock Transaction Approved");

        $Order->setStatus("Mock Authorized");
        TransactionRow::insert($Transaction);
        OrderRow::update($Order);
        return $Transaction;

    }

    /**
     * Create or resume an order item
     * @param AbstractMerchantIdentity $MerchantIdentity
     * @param array $post
     * @return OrderRow
     */
    function createOrResumeOrder(AbstractMerchantIdentity $MerchantIdentity, Array $post) {
        $Order = OrderRow::createOrderFromPost($MerchantIdentity, $post);
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
     * @param MockMerchantIdentity|AbstractMerchantIdentity $MerchantIdentity
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
     * @return mixed
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
    }
}

