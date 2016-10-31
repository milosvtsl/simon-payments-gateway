<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/28/2016
 * Time: 1:32 PM
 */
namespace Transaction\Mail;


use Config\SiteConfig;
use Merchant\Model\MerchantRow;
use Order\Model\OrderRow;
use PHPMailer;
use Transaction\Model\TransactionRow;
use User\Model\UserRow;

define("PHPMAILER_DIR", dirname(dirname(__DIR__)) . '/system/support/PHPMailer/');
require_once PHPMAILER_DIR . 'PHPMailerAutoload.php';
require_once PHPMAILER_DIR . 'class.smtp.php';

class ReceiptEmail extends \PHPMailer
{
    public function __construct(OrderRow $Order, MerchantRow $Merchant) {
        parent::__construct();

        $this->Host = SiteConfig::$EMAIL_SERVER_HOST;
        if(SiteConfig::$EMAIL_SMTP_USERNAME) {
            $this->Username = SiteConfig::$EMAIL_SMTP_USERNAME;
            $this->Password = SiteConfig::$EMAIL_SMTP_PASSWORD;
            $this->SMTPSecure = 'tls';
            $this->Port = SiteConfig::$EMAIL_SERVER_PORT;
        }

        $this->SMTPAuth = SiteConfig::$EMAIL_SMTP_AUTH;
        $this->SMTPSecure = SiteConfig::$EMAIL_SMTP_SECURE;

        if(SiteConfig::$EMAIL_FROM_ADDRESS)
            $this->setFrom(SiteConfig::$EMAIL_FROM_ADDRESS, SiteConfig::$EMAIL_FROM_TITLE);

        $this->addAddress($Order->getPayeeEmail(), $Order->getCardHolderFullName());
        $this->addBCC($Merchant->getMainEmailID(), $Order->getCardHolderFullName());
        $this->addBCC("ari@govpaynetwork.com", $Order->getCardHolderFullName());

        $this->Subject = "Receipt: " . $Merchant->getName();

        $pu = parse_url(@$_SERVER['REQUEST_URI']);
        $url = (@$pu["host"]?:SiteConfig::$SITE_URL?:'localhost') . '/transaction/receipt.php?uid='.$Order->getUID();

        $amount = '$' . $Order->getAmount();
        $merchant = $Merchant->getName();
        $date = $Order->getDate();
        $status = $Order->getStatus();
        $full_name = $Order->getCardHolderFullName();
        $card_number = $Order->getCardNumber();
        $card_type = $Order->getCardType();

        $content = <<<HTML
Amount: {$amount}
Merchant: {$merchant}
Date: {$date}
Status: {$status}

Card Holder Information:
Full Name: {$full_name}
Number: {$card_number}
Type: {$card_type}
HTML;

        $content_html = nl2br($content);

        $sig = SiteConfig::$SITE_NAME;




        $this->isHTML(true);
        $this->Body = <<<HTML
<html>
    <body>
        {$content_html}<br/>
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

    public function send() {
        if(@$_SERVER['HTTP_HOST'] === 'localhost' || @$_SERVER['OS'] === 'Windows_NT') {
            $log = "<pre>Email was sent from localhost\n". print_r($this, true) . "</pre>";
            echo $log;
            error_log($log);
            return true;
        }
        return parent::send();
    }
}

