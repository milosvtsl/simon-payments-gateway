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

class MerchantDeclineEmail extends DeclineEmail
{
    const TITLE = "Merchant Payment Failed Email";
    const BCC = '';
    const TEMPLATE_SUBJECT = '{$customer_full_name}: Payment Failure';
    const TEMPLATE_BODY = '
A transaction failure occured in a payment attempt to <strong>{$merchant_name}</strong>.<br/> 
The attempting customer\'s information is below for your records.<br/>
<br/> 
<b>Order Information</b><br/>
<div style="display: inline-block; width: 160px;">Amount:</div>   {$amount}<br/>
<div style="display: inline-block; width: 160px;">Date:</div>     {$date}<br/>
<div style="display: inline-block; width: 160px;">Ref ID:</div>   <a href="{$url}">{$reference_number}</a><br/>
{$order_fields}<br/>
<br/>
<br/><b>Payment Information</b><br/>
<div style="display: inline-block; width: 160px;">Full Name:</div>   {$customer_full_name}<br/>
{$payment_information}
<br/>
<br/>You may use this link to view your order at any time:<br/>
<a href="{$url}">{$url}</a><br/>
<br/>
<hr/>
<a href="{$url}">
    <img src="{$SITE_URL_MERCHANT_LOGO}" alt="{$merchant_name}" style="max-width: 512px; max-height: 64px;"/>
</a>
<br />
';

}