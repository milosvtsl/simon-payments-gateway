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
    const BCC = '';
    const TEMPLATE_SUBJECT = 'Receipt: {$customer_name}';
    const TEMPLATE_BODY = '
Your payment attempt to {$merchant_name} has failed. Please verify your payment information. 
Also, verify with your bank if your account has enough funds and/or is being denied for some other reason before proceeding.<br/>
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