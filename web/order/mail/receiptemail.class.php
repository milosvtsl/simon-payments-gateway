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
use PHPMailer;
use System\Config\SiteConfig;

@define("PHPMAILER_DIR", dirname(dirname(__DIR__)) . '/system/lib/PHPMailer/');
require_once PHPMAILER_DIR . 'PHPMailerAutoload.php';
require_once PHPMAILER_DIR . 'class.smtp.php';

class ReceiptEmail extends \PHPMailer
{
    public function __construct(OrderRow $Order, MerchantRow $Merchant) {
        parent::__construct();

        $this->Host = SiteConfig::$EMAIL_SERVER_HOST;
        $this->Username = SiteConfig::$EMAIL_USERNAME;
        $this->Password = SiteConfig::$EMAIL_PASSWORD;
        $this->Port = SiteConfig::$EMAIL_SERVER_PORT;
        $this->Timeout = SiteConfig::$EMAIL_TIMEOUT;
        $this->SMTPAuth = SiteConfig::$EMAIL_SMTP_AUTH;
        $this->SMTPSecure = SiteConfig::$EMAIL_SMTP_SECURE;
        if(SiteConfig::$EMAIL_SMTP_AUTH)
            $this->isSMTP();


        if(SiteConfig::$EMAIL_FROM_ADDRESS)
            $this->setFrom(SiteConfig::$EMAIL_FROM_ADDRESS, SiteConfig::$EMAIL_FROM_TITLE);

        $this->addAddress($Order->getPayeeEmail(), $Order->getPayeeFullName());
        $this->addBCC($Merchant->getMainEmailID(), $Order->getPayeeFullName());
        $this->addBCC("support@simonpayments.com", $Order->getPayeeFullName());

        $this->Subject = "Receipt: " . $Merchant->getName();

        $pu = parse_url(@$_SERVER['REQUEST_URI']);
        $url = (@$pu["host"]?:SiteConfig::$SITE_URL?:'localhost') . '/order/receipt.php?uid='.$Order->getUID(false);
        $date = date('M dS Y g:i a', strtotime($Order->getDate()) ?: time());
        $next_date = $Order->getSubscriptionNextDate() ? date('M dS Y G:i', strtotime($Order->getSubscriptionNextDate())) : 'N/A';

        $content = <<<HTML
Order Information
Amount:             \${$Order->getAmount()}
Merchant:           {$Merchant->getName()}
Date:               {$date}
HTML;
// Status:             {$Order->getStatus()}

        if($Order->getSubscriptionID())
            $content .= <<<HTML


Subscription Information
Status:             {$Order->getSubscriptionStatus()}
Frequency:          {$Order->getSubscriptionFrequency()}
Count:              {$Order->getSubscriptionCount()}
Next Date:          {$next_date}
HTML;

        if($Order->getEntryMode() == OrderRow::ENUM_ENTRY_MODE_CHECK)
            $content .= <<<HTML


E-Check Information
Account Name:       {$Order->getCheckAccountName()}
Account Type:       {$Order->getCheckAccountType()}
Account Number:     {$Order->getCheckAccountNumber()}
Routing Number:     {$Order->getCheckRoutingNumber()}
Type:               {$Order->getCheckType()}
HTML;
        else $content .= <<<HTML


Card Holder Information
Full Name:          {$Order->getPayeeFullName()}
Number:             {$Order->getCardNumber()}
Type:               {$Order->getCardType()}
HTML;

        $sig = SiteConfig::$SITE_NAME;

        $this->isHTML(true);
        $this->Body = <<<HTML
<html>
    <body>
        <pre>{$content}</pre>
        <br/>
        If you would like to view your receipt online, please click the following link:<br/>
        <a href="{$url}">{$url}</a><br/>

        ____<br/>
        {$sig}
    </body>
</html>
HTML;

$this->AltBody = <<<TEXT
{$content}

If you would like to view your receipt online, please click the following link:<br/>
{$url}

____
{$sig}
TEXT;

    }

    public function send2() {
        if(@$_SERVER['HTTP_HOST'] === 'localhost' || @$_SERVER['OS'] === 'Windows_NT') {
            $log = "<pre>Email was sent from localhost\n". print_r($this, true) . "</pre>";
            echo $log;
            error_log($log);
            return true;
        }
        return parent::send();
    }
}

