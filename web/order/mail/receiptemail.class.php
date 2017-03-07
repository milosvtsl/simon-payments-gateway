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

class ReceiptEmail extends AbstractEmail
{
    const TITLE = "Customer Payment Success Receipt Email";
    const TEMPLATE_SUBJECT = 'Receipt: {$merchant_name}';
    const TEMPLATE_BODY = '
Hello {$customer_full_name},<br/>
Thank you for your payment to <b>{$merchant_name}</b>.<br/>
<br/> 
<b>Order Information</b><br/>
<div style="display: inline-block; width: 160px;">Amount:</div>   {$amount}<br/>
<div style="display: inline-block; width: 160px;">Date:</div>     {$date}<br/>
<div style="display: inline-block; width: 160px;">Ref ID:</div>   <a href="{$url}">{$reference_number}</a><br/>
{$order_fields}
<br/>
<br/><b>Payment Information</b><br/>
<div style="display: inline-block; width: 160px;">Full Name:</div>   {$customer_full_name}<br/>
{$payment_information}
<br/>
<br/>You may use this link to view your order at any time:<br/>
<a href="{$url}">{$url}</a><br/>
<hr/>
<a href="{$url}">
    <img src="{$SITE_URL_MERCHANT_LOGO}" alt="{$merchant_name}" style="max-width: 512px; max-height: 64px;"/>
</a>
';

    public function __construct(OrderRow $Order, MerchantRow $Merchant) {
        parent::__construct();

        $SessionManager = new SessionManager();
        $SessionUser = $SessionManager->getSessionUser();

        $pu = parse_url(@$_SERVER['REQUEST_URI']);
        $url = (@$pu["host"]?:SiteConfig::$SITE_URL?:'localhost') . '/order/receipt.php?uid='.$Order->getUID();

        $params = array(
            'order_fields' => null,
            'payment_information' => null,
            'subscription_information' => null,

            'url' => $url,
            'date' => $Order->getDate($SessionUser->getTimeZone())->format("M dS Y g:i a e"),
            'amount' => '$'.number_format($Order->getAmount(), 2),
            'customer_full_name' => $Order->getCustomerFullName(),
            'customer_email' => $Order->getPayeeEmail(),
            'merchant_name' => $Merchant->getName() ?: SiteConfig::$SITE_DEFAULT_MERCHANT_NAME,
            'merchant_phone' => $Merchant->getTelephone(),
            'username' => $Order->getUsername(),
            'invoice' => $Order->getInvoiceNumber(),
            'reference_number' => $Order->getReferenceNumber(),

            'subscription_status' => $Order->getSubscriptionStatus(),
            'subscription_frequency' => $Order->getSubscriptionFrequency(),
            'subscription_count' => $Order->getSubscriptionCount(),

            'check_account_name' => $Order->getCheckAccountName(),
            'check_account_type' => $Order->getCheckAccountType(),
            'check_account_number' => $Order->getCheckAccountNumber(),
            'check_routing_number' => $Order->getCheckRoutingNumber(),
            'check_type' => $Order->getCheckType(),

            'card_name' => $Order->getCardHolderFullName(),
            'card_number' => '***'.$Order->getCardNumberTruncated(),
            'card_type' => $Order->getCardType(),
            'card_exp' => $Order->getCardExp(),
        );

        $order_fields = '';
        if($Order->getInvoiceNumber())
            $order_fields .= "<div style='display: inline-block; width: 160px;'>Invoice:</div>       {$Order->getInvoiceNumber()}<br/>";

        foreach($Order->getCustomFieldValues() as $field => $value) {
            $name = ucwords(str_replace('_', ' ', $field));
            $order_fields .= "<div style='display: inline-block; width: 160px;'>{$name}:</div>       {$value}<br/>";
            $params['custom_' . $field] = $value;
        }
        $params['order_fields']  = $order_fields;


        if($Order->getEntryMode() == OrderRow::ENUM_ENTRY_MODE_CHECK)
            $payment_info = <<<'HTML'
<div style="display: inline-block; width: 160px;">Account Name:</div>       {$check_account_name}<br/>
<div style="display: inline-block; width: 160px;">Account Type:</div>       {$check_account_type}<br/>
<div style="display: inline-block; width: 160px;">Account Number:</div>     {$check_account_number}<br/>
<div style="display: inline-block; width: 160px;">Routing Number:</div>     {$check_routing_number}<br/>
<div style="display: inline-block; width: 160px;">Type:</div>               {$check_type}<br/>
HTML;
        else $payment_info = <<<'HTML'
<div style="display: inline-block; width: 160px;">CC Number:</div>          {$card_number}<br/>
<div style="display: inline-block; width: 160px;">CC Exp:</div>             {$card_exp}<br/>
<div style="display: inline-block; width: 160px;">CC Type:</div>            {$card_type}<br/>
HTML;
        $params['payment_information']  = $payment_info;



        if(SiteConfig::$EMAIL_FROM_ADDRESS)
            $this->setFrom(SiteConfig::$EMAIL_FROM_ADDRESS, SiteConfig::$EMAIL_FROM_TITLE);

        $this->addAddress($Order->getPayeeEmail(), $Order->getPayeeFullName());
        $this->addBCC(SiteConfig::$EMAIL_FROM_ADDRESS, $Order->getPayeeFullName());

        // Customize Email Template
        $body = static::TEMPLATE_BODY;
        $subject = static::TEMPLATE_SUBJECT;

        $this->processTemplate($body, $subject, $params, $Merchant);
    }
}

