<?php
/**
 * Created by PhpStorm.
 * User: Ari
 * Date: 1/15/2017
 * Time: 4:18 PM
 */

namespace Order\Forms;


use Integration\Model\AbstractMerchantIdentity;
use Merchant\Model\MerchantFormRow;
use Order\Model\OrderRow;

abstract class AbstractForm
{
    /**
     * Render custom order form HTML
     * @param MerchantFormRow $MerchantForm
     * @param AbstractMerchantIdentity $MerchantIdentity
     * @param array $params
     * @return mixed
     */
    abstract function renderHTML(MerchantFormRow $MerchantForm, AbstractMerchantIdentity $MerchantIdentity, Array $params);


    /**
     * Render HTML Head content
     * @param MerchantFormRow $MerchantForm
     * @param AbstractMerchantIdentity $MerchantIdentity
     */
    abstract function renderHTMLHeadLinks(MerchantFormRow $MerchantForm, AbstractMerchantIdentity $MerchantIdentity);
    
    /**
     * Process form submission
     * @param MerchantFormRow $MerchantForm
     * @param OrderRow $Order
     * @param array $post
     * @return mixed
     */
    abstract function processFormRequest(MerchantFormRow $MerchantForm, OrderRow $Order, Array $post);

}

