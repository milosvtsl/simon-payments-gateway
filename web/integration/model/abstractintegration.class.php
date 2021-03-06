<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/12/2016
 * Time: 1:25 PM
 */
namespace Integration\Model;

use Integration\Model\Ex\IntegrationException;
use Integration\Request\Model\IntegrationRequestRow;
use Merchant\Model\MerchantFormRow;
use Merchant\Model\MerchantRow;
use Order\Model\OrderRow;
use Order\Model\TransactionRow;
use Payment\Model\PaymentRow;
use Subscription\Model\SubscriptionRow;
use User\Model\UserRow;

abstract class AbstractIntegration
{

    /**
     * Execute a prepared request
     * @param AbstractMerchantIdentity $MerchantIdentity
     * @param IntegrationRequestRow $Request
     */
    abstract function execute(AbstractMerchantIdentity $MerchantIdentity, IntegrationRequestRow $Request);

    /**
     * Was this request successful?
     * @param IntegrationRequestRow $Request
     * @param null $reason
     * @param null $code
     * @return bool
     */
//    abstract function isRequestSuccessful(IntegrationRequestRow $Request, &$reason = null, &$code = null);

    /**
     * Print an HTML form containing the request fields
     * @param IntegrationRequestRow $Request
     * @return void
     * @throws IntegrationException if the form failed to print
     */
//    abstract function printFormHTML(IntegrationRequestRow $Request);

    /**
     * Parse the response data and return a data object
     * @param IntegrationRequestRow $Request
     * @return mixed
     * @throws IntegrationException if response failed to parse
     */
//    abstract function parseResponseData(IntegrationRequestRow $Request);

    /**
     * Return the API Request URL for this request
     * @param AbstractMerchantIdentity $MerchantIdentity
     * @param IntegrationRequestRow $Request
     * @return string
     */
//    abstract function getRequestURL(AbstractMerchantIdentity $MerchantIdentity, IntegrationRequestRow $Request);

    /**
     * Get or create a Merchant Identity
     * @param MerchantRow $Merchant
     * @param IntegrationRow $IntegrationRow
     * @return AbstractMerchantIdentity
     */
    abstract function getMerchantIdentity(MerchantRow $Merchant, IntegrationRow $IntegrationRow);


    /**
     * Create or resume an order item
     * @param AbstractMerchantIdentity $MerchantIdentity
     * @param array $post
     * @return OrderRow
     */
    // abstract function createOrResumeOrder(AbstractMerchantIdentity $MerchantIdentity, Array $post);

    /**
     * Create a new order, optionally set up a new payment entry with the remote integration
     * @param AbstractMerchantIdentity $MerchantIdentity
     * @param PaymentRow $PaymentInfo
     * @param MerchantFormRow $OrderForm
     * @param array $post Order Information
     * @return OrderRow
     */
    abstract function createNewOrder(AbstractMerchantIdentity $MerchantIdentity, PaymentRow $PaymentInfo, MerchantFormRow $OrderForm, Array $post);


    /**
     * Submit a new transaction
     * @param AbstractMerchantIdentity $MerchantIdentity
     * @param UserRow $SessionUser
     * @param OrderRow $Order
     * @param array $post
     * @return TransactionRow
     */
    abstract function submitNewTransaction(AbstractMerchantIdentity $MerchantIdentity, OrderRow $Order, UserRow $SessionUser, Array $post);

    /**
     * Void an existing Transaction
     * @param AbstractMerchantIdentity $MerchantIdentity
     * @param OrderRow $OrderRow
     * @param UserRow $SessionUser
     * @param array $post
     * @return mixed
     */
    abstract function voidTransaction(AbstractMerchantIdentity $MerchantIdentity, OrderRow $OrderRow, UserRow $SessionUser, Array $post);

    /**
     * Reverse an existing Transaction
     * @param AbstractMerchantIdentity $MerchantIdentity
     * @param OrderRow $Order
     * @param UserRow $SessionUser
     * @param array $post
     * @return mixed
     */
    abstract function reverseTransaction(AbstractMerchantIdentity $MerchantIdentity, OrderRow $Order, UserRow $SessionUser, Array $post);

    /**
     * Return an existing Transaction
     * @param AbstractMerchantIdentity $MerchantIdentity
     * @param OrderRow $Order
     * @param UserRow $SessionUser
     * @param array $post
     * @return mixed
     */
    abstract function returnTransaction(AbstractMerchantIdentity $MerchantIdentity, OrderRow $Order, UserRow $SessionUser, Array $post);

    /**
     * Perform health check on remote api
     * @param AbstractMerchantIdentity $MerchantIdentity
     * @param UserRow $SessionUser
     * @param array $post
     * @return mixed
     */
    abstract function performHealthCheck(AbstractMerchantIdentity $MerchantIdentity, UserRow $SessionUser, Array $post);

    /**
     * Perform transaction query on remote api
     * @param AbstractMerchantIdentity $MerchantIdentity
     * @param UserRow $SessionUser
     * @param array $post
     * @param null $callback
     * @return array
     */
    abstract function performTransactionQuery(AbstractMerchantIdentity $MerchantIdentity, UserRow $SessionUser, Array $post, $callback);

    /**
     * Cancel an active subscription
     * @param AbstractMerchantIdentity $MerchantIdentity
     * @param SubscriptionRow $Subscription
     * @param UserRow $SessionUser
     * @param $message
     * @return
     */
    abstract function cancelSubscription(AbstractMerchantIdentity $MerchantIdentity, SubscriptionRow $Subscription, UserRow $SessionUser, $message);

    /**
     * Render Charge Form Integration Headers
     * @param AbstractMerchantIdentity $MerchantIdentity
     * @return
     */
    abstract function renderChargeFormHTMLHeadLinks(AbstractMerchantIdentity $MerchantIdentity);

    /**
     * Render Charge Form Hidden Fields
     * @param AbstractMerchantIdentity $MerchantIdentity
     * @return
     */
    abstract function renderChargeFormHiddenFields(AbstractMerchantIdentity $MerchantIdentity);
}