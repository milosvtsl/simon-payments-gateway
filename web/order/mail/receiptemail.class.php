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
    const TITLE = "Order Receipt Email";
    const BCC = '';
    const TEMPLATE_SUBJECT = 'Receipt: {$merchant_name}';
    const TEMPLATE_BODY = '
Hello {$customer_full_name},<br/>
Thank you for your payment to <b>{$merchant_name}</b>.<br/>
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

    public function __construct(OrderRow $Order, MerchantRow $Merchant) {
        parent::__construct();

        $SessionManager = new SessionManager();
        $SessionUser = $SessionManager->getSessionUser();

        $pu = parse_url(@$_SERVER['REQUEST_URI']);
        $url = (@$pu["host"]?:SiteConfig::$SITE_URL?:'localhost') . '/order/receipt.php?uid='.$Order->getUID();

        $params = array(
            'order_information' => null,
            'payment_information' => null,
            'subscription_information' => null,

            'url' => $url,
            'date' => $Order->getDate($SessionUser->getTimeZone())->format("M dS Y g:i a e"),
            'amount' => '$'.number_format($Order->getAmount(), 2),
            'customer_full_name' => $Order->getCustomerFullName(),
            'customer_email' => $Order->getPayeeEmail(),
            'merchant_name' => $Order->getMerchantName(),
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
            'card_number' => $Order->getCardNumber(),
            'card_type' => $Order->getCardType(),
            'card_exp' => $Order->getCardExp(),
        );

        $order_info = <<<'HTML'
<dl class="inline">
    <dt>Amount</dt><dd>{$amount}</dd>
    <dt>Date</dt><dd>{$date}</dd>
    <dt>Ref ID</dt><dd><a href="{$url}">{$reference_number}</a></dd>
HTML;
        if($Order->getInvoiceNumber())
            $order_info .= "\n\t<dt>Invoice</dt><dd>{$Order->getInvoiceNumber()}</dd>";

        foreach($Order->getCustomFieldValues() as $field => $value) {
            $name = ucwords(str_replace('_', ' ', $field));
            $order_info .= "\n\t<dt>{$name}</dt><dd>{$value}</dd>";
            $params['custom_' . $field] = $value;
        }
        $order_info .= "\n</dl>";
        $params['order_information']  = $order_info;



        if($Order->getEntryMode() == OrderRow::ENUM_ENTRY_MODE_CHECK)
            $payment_info = <<<'HTML'
<dl class="inline">
    <dt>Account Name</dt><dd>{$check_account_name}</dd>
    <dt>Account Type</dt><dd>{$check_account_type}</dd>
    <dt>Account Number</dt><dd>{$check_account_number}</dd>
    <dt>Routing Number</dt><dd>{$check_routing_number}</dd>
    <dt>Type</dt><dd>{$check_type}</dd>
</dl>
HTML;
        else $payment_info = <<<'HTML'
<dl class="inline">
    <dt>Full Name</dt><dd>{$card_name}</dd>
    <dt>Number</dt><dd>{$card_number}</dd>
    <dt>Exp</dt><dd>{$card_exp}</dd>
    <dt>Type</dt><dd>{$card_type}</dd>
</dl>
HTML;
        $params['payment_information']  = $payment_info;



        if(SiteConfig::$EMAIL_FROM_ADDRESS)
            $this->setFrom(SiteConfig::$EMAIL_FROM_ADDRESS, SiteConfig::$EMAIL_FROM_TITLE);

        $this->addAddress($Order->getPayeeEmail(), $Order->getPayeeFullName());
        $this->addBCC(SiteConfig::$EMAIL_FROM_ADDRESS, $Order->getPayeeFullName());

        // Customize Email Template
        $body = static::TEMPLATE_BODY;
        $subject = static::TEMPLATE_SUBJECT;
        $bcc = static::BCC;

        $this->processTemplate($body, $subject, $bcc, $params, $Merchant);
    }
}

