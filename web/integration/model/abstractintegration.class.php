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
use Merchant\Model\MerchantRow;
use Order\Model\OrderRow;
use Transaction\Model\TransactionRow;

abstract class AbstractIntegration
{

    /**
     * Execute a prepared request
     * @param IntegrationRequestRow $Request
     * @return void
     * @throws IntegrationException if the request execution failed
     */
    abstract function execute(IntegrationRequestRow $Request);

    /**
     * Was this request successful?
     * @param IntegrationRequestRow $Request
     * @return bool
     * @throws IntegrationException if the request status could not be processed
     */
    abstract function isRequestSuccessful(IntegrationRequestRow $Request, &$reason=null);

    /**
     * Print an HTML form containing the request fields
     * @param IntegrationRequestRow $Request
     * @return void
     * @throws IntegrationException if the form failed to print
     */
    abstract function printFormHTML(IntegrationRequestRow $Request);

    /**
     * Parse the response data and return a data object
     * @param IntegrationRequestRow $Request
     * @return mixed
     * @throws IntegrationException if response failed to parse
     */
    abstract function parseResponseData(IntegrationRequestRow $Request);

    /**
     * Return the API Request URL for this request
     * @param IntegrationRequestRow $Request
     * @return string
     */
    abstract function getRequestURL(IntegrationRequestRow $Request);

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
    abstract function createOrResumeOrder(AbstractMerchantIdentity $MerchantIdentity, Array $post);


    /**
     * Submit a new transaction
     * @param AbstractMerchantIdentity $MerchantIdentity
     * @param OrderRow $Order
     * @param array $post
     * @return TransactionRow
     * @throws IntegrationException
     */
    abstract function submitNewTransaction(AbstractMerchantIdentity $MerchantIdentity, OrderRow $Order, Array $post);

    /**
     * Void an existing Transaction
     * @param AbstractMerchantIdentity $MerchantIdentity
     * @param OrderRow $OrderRow
     * @param array $post
     * @return mixed
     */
    abstract function voidTransaction(AbstractMerchantIdentity $MerchantIdentity, OrderRow $OrderRow, Array $post);

    /**
     * Reverse an existing Transaction
     * @param AbstractMerchantIdentity $MerchantIdentity
     * @param OrderRow $Order
     * @param array $post
     * @return mixed
     */
    abstract function reverseTransaction(AbstractMerchantIdentity $MerchantIdentity, OrderRow $Order, Array $post);

    /**
     * Return an existing Transaction
     * @param AbstractMerchantIdentity $MerchantIdentity
     * @param OrderRow $Order
     * @param array $post
     * @return mixed
     */
    abstract function returnTransaction(AbstractMerchantIdentity $MerchantIdentity, OrderRow $Order, Array $post);

    /**
     * Perform health check on remote api
     *
     * @param AbstractMerchantIdentity $MerchantIdentity
     * @param array $post
     * @return mixed
     */
    abstract function performHealthCheck(AbstractMerchantIdentity $MerchantIdentity, Array $post);
}