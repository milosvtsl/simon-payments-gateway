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

require_once dirname(dirname(__DIR__)) . '/system/support/PHPMailer/PHPMailerAutoload.php';

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

        if(SiteConfig::$EMAIL_FROM_ADDRESS)
            $this->setFrom(SiteConfig::$EMAIL_FROM_ADDRESS, SiteConfig::$EMAIL_FROM_TITLE);

        $this->addAddress($Order->getPayeeEmail(), $Order->getCardHolderFullName());

        $this->Subject = "Receipt: " . $Merchant->getName();

        $pu = parse_url($_SERVER['REQUEST_URI']);
        $url = (@$pu["host"]?:SiteConfig::$SITE_URL?:'localhost') . '/transaction/receipt.php?uid='.$Order->getUID();

        $amount = '$' . $Order->getAmount();
        $merchant = $Merchant->getName();
        $date = $Order->getDate();
        $status = $Order->getStatus();
        $full_name = $Order->getCardHolderFullName();
        $card_number = $Order->getCardNumber();
        $card_type = $Order->getCardType();

        $content = <<<HTML
Amount: {$amount}<br />
Merchant: {$merchant}<br />
Date: {$date}<br />
Status: {$status}<br />

Card Holder Information:
Full Name: {$full_name}<br />
Number: {$card_number}<br />
Type: {$card_type}<br />
HTML;


        $sig = SiteConfig::$SITE_NAME;

        $this->isHTML(true);
        $this->Body = <<<HTML
{$content}

If you would like to view your receipt online, please click the following link:<br/>
<a href="{$url}">{$url}</a><br/>

____
{$sig}
HTML;
    }

    public function send() {
        if($_SERVER['HTTP_HOST'] === 'localhost') {
            $log = "<pre>Email was sent from localhost\n". print_r($this, true) . "</pre>";
            echo $log;
            error_log($log);
            return true;
        }
        return parent::send();
    }
}

