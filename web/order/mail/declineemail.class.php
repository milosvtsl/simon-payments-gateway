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

class DeclineEmail extends ReceiptEmail
{
    const TITLE = "Customer Payment Failed Email";
    const TEMPLATE_SUBJECT = 'Receipt: {$customer_full_name}';
    const TEMPLATE_BODY = '
Your payment attempt to <strong>{$merchant_name}</strong> has failed. Please verify your payment information. <br/>
Also, verify with your bank if your account has enough funds and/or <br/>
is being denied for some other reason before proceeding.<br/>
<br/>
<hr/>
<a href="{$url}">
    <img src="{$SITE_URL_MERCHANT_LOGO}" alt="{$merchant_name}" style="max-width: 512px; max-height: 64px;"/>
</a>
';

}