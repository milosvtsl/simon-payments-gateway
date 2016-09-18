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
    abstract function isRequestSuccessful(IntegrationRequestRow $Request);

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
     * @param IntegrationRow $APIData
     * @return string
     */
    abstract function getRequestURL(IntegrationRequestRow $Request, IntegrationRow $APIData=null);

    /**
     * Get or create a Merchant Identity
     * @param MerchantRow $Merchant
     * @param IntegrationRow $IntegrationRow
     * @return AbstractMerchantIdentity
     */
    abstract function getMerchantIdentity(MerchantRow $Merchant, IntegrationRow $IntegrationRow);

}