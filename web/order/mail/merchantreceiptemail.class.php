<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/28/2016
 * Time: 1:32 PM
 */
namespace Order\Mail;


use Merchant\Model\MerchantRow;
use Order\Model\OrderRow;
use System\Config\SiteConfig;
use System\Mail\AbstractEmail;
use User\Session\SessionManager;

class MerchantReceiptEmail extends ReceiptEmail
{
    const TITLE = "Merchant Order Receipt Email";
    const BCC = '';
    const TEMPLATE_SUBJECT = '{$customer_name}: Successful Payment';
    const TEMPLATE_BODY = '
A successful payment has been made to {$merchant_name} by {$SITE_DEFAULT_CUSTOMER_NAME} {$customer_name}.<br/>
<br/>
<b>Order Information</b><br/>
{$order_information}<br/>
<br/>
<b>Payment Information</b><br/>
{$payment_information}<br/>
<br/>
{$subscription_information}<br/>
<br/>
You may use this link to view your order at any time:<br/>
<a href="{$url}">{$url}</a><br/>
<br/>
<hr/>
<img src="{$SITE_URL_MERCHANT_LOGO}" alt="{$merchant_name}" /><br />
<style>
dl.inline dd {
    display: inline;
}
dl.inline dd:after{
    display: block;
    content: "";
}
dl.inline dt{
    display: inline-block;
    min-width: 100px;
}
dl.inline dt:after{
    content: ":";
}

</style>
';

}