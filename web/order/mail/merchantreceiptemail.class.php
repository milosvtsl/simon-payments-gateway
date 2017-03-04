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

class MerchantReceiptEmail extends AbstractEmail
{
    const TITLE = "Merchant Order Receipt Email";
    const BCC = '';
    const TEMPLATE_SUBJECT = 'Receipt: {$customer_name}';
    const TEMPLATE_BODY = '
<pre>
A successful payment has been made to {$merchant_name} by {$ctype} {$customer_name}.

Order Information
Amount:             ${$amount}
Date:               {$date}
Ref ID:             <a href="{$url}">{$reference_number}</a>

Payment Information
{$payment_information}

{$subscription_information}

You may use this url to view your order at any time:
URL:        <a href="{$url}">{$url}</a>

____
{$sig}
</pre>';

    public function __construct(OrderRow $Order, MerchantRow $Merchant) {
        parent::__construct();

        $SessionManager = new SessionManager();
        $SessionUser = $SessionManager->getSessionUser();

        $pu = parse_url(@$_SERVER['REQUEST_URI']);
        $url = (@$pu["host"]?:SiteConfig::$SITE_URL?:'localhost') . '/order/receipt.php?uid='.$Order->getUID();

        $params = array(
            'url' => $url,
            'date' => $Order->getDate($SessionUser->getTimeZone())->format("M dS Y g:i a e"),
            'amount' => number_format($Order->getAmount(), 2),
            'customer_full_name' => $Order->getCustomerFullName(),
            'customer_email' => $Order->getPayeeEmail(),
            'merchant_name' => $Order->getMerchantName(),
            'username' => $Order->getUsername(),
            'invoice' => $Order->getInvoiceNumber(),
            'payment_info' => null,
            'subscription_status' => $Order->getSubscriptionStatus(),
            'subscription_frequency' => $Order->getSubscriptionFrequency(),
            'subscription_count' => $Order->getSubscriptionCount(),
            'subscription_information' => null,

            'check_account_name' => $Order->getCheckAccountName(),
            'check_account_type' => $Order->getCheckAccountType(),
            'check_account_number' => $Order->getCheckAccountNumber(),
            'check_routing_number' => $Order->getCheckRoutingNumber(),
            'check_type' => $Order->getCheckType(),

            'card_number' => $Order->getCardNumber(),
            'card_type' => $Order->getCardType(),
            'card_exp' => $Order->getCardExp(),

            'sig' => SiteConfig::$SITE_NAME,
            'mtype' => SiteConfig::$SITE_DEFAULT_MERCHANT_NAME,
            'ctype' => SiteConfig::$SITE_DEFAULT_CUSTOMER_NAME,
        );


        if($Order->getEntryMode() == OrderRow::ENUM_ENTRY_MODE_CHECK)
            $params['payment_info'] = <<<HTML
ACH/Check Information
Account Name:       {$Order->getCheckAccountName()}
Account Type:       {$Order->getCheckAccountType()}
Account Number:     {$Order->getCheckAccountNumber()}
Routing Number:     {$Order->getCheckRoutingNumber()}
Type:               {$Order->getCheckType()}
HTML;
        else $params['payment_info'] = <<<HTML
Card Holder Information
Full Name:          {$Order->getCustomerFullName()}
Number:             {$Order->getCardNumber()}
Exp:                {$Order->getCardExp()}
Type:               {$Order->getCardType()}
HTML;


        if(SiteConfig::$EMAIL_FROM_ADDRESS)
            $this->setFrom(SiteConfig::$EMAIL_FROM_ADDRESS, SiteConfig::$EMAIL_FROM_TITLE);

        $this->addAddress($Order->getPayeeEmail(), $Order->getPayeeFullName());
        $this->addBCC(SiteConfig::$EMAIL_FROM_ADDRESS, $Order->getPayeeFullName());

        // Customize Email Template
        $body = static::TEMPLATE_BODY;
        $subject = static::TEMPLATE_SUBJECT;
        $bcc = static::BCC;
        $this->processTemplate($body, $subject, $bcc, $params, $Merchant->getID());
    }
}

