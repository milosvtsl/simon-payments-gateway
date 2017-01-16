<?php
/**
 * Created by PhpStorm.
 * User: Ari
 * Date: 1/15/2017
 * Time: 4:18 PM
 */

namespace Order\Forms;


use Merchant\Model\MerchantFormRow;
use Merchant\Model\MerchantRow;
use Order\Model\OrderRow;

abstract class AbstractForm
{
    /**
     * Render custom order form HTML 
     * @param MerchantFormRow $MerchantForm
     * @param MerchantRow $Merchant
     * @param array $params
     * @return mixed
     */
    abstract function renderHTML(MerchantFormRow $MerchantForm, MerchantRow $Merchant, Array $params);


    /**
     * Render HTML Head content
     */
    abstract function renderHTMLHeadLinks();
    
    /**
     * Process form submission
     * @param MerchantFormRow $MerchantForm
     * @param OrderRow $Order
     * @param array $post
     * @return mixed
     */
    abstract function processFormRequest(MerchantFormRow $MerchantForm, OrderRow $Order, Array $post);

}

