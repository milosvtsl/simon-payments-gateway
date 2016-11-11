<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/28/2016
 * Time: 1:32 PM
 */
namespace Support\Mail;


use Support\Model\SupportTicketRow;
use System\Config\SiteConfig;
use Merchant\Model\MerchantRow;
use Order\Model\OrderRow;
use PHPMailer;

@define("PHPMAILER_DIR", dirname(dirname(__DIR__)) . '/system/support/PHPMailer/');
require_once PHPMAILER_DIR . 'PHPMailerAutoload.php';
require_once PHPMAILER_DIR . 'class.smtp.php';

class TicketEmail extends \PHPMailer
{
    public function __construct(SupportTicketRow $SupportTicket) {
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

        if($SupportTicket->getOrderItemID()) {
            $Order = OrderRow::fetchByID($SupportTicket->getOrderItemID());
            $Merchant = MerchantRow::fetchByID($Order->getMerchantID());
            $this->addAddress($SupportTicket->getReplyToEmail(), $Order->getCardHolderFullName());
            $this->addBCC($Merchant->getMainEmailID());

        } else {
            $this->addAddress($SupportTicket->getReplyToEmail());
        }
        $this->addBCC("ari@govpaynetwork.com");

        $this->Subject = "Support Ticket: " . $SupportTicket->getSubject();

        $pu = parse_url(@$_SERVER['REQUEST_URI']);
        $url = (@$pu["host"]?:SiteConfig::$SITE_URL?:'localhost') . '/support/ticket.php?uid='.$SupportTicket->getUID();

        $create_date = date('M jS Y G:i', strtotime($SupportTicket->getDate()) ?: time());

        $content = <<<HTML
Support Ticket
Subject:        {$SupportTicket->getSubject()}
Date:           {$SupportTicket->getDate()}
Status:         {$SupportTicket->getStatus()}

{$SupportTicket->getContent()}
HTML;

        $sig = SiteConfig::$SITE_NAME;

        $this->isHTML(true);
        $this->Body = <<<HTML
<html>
    <body>
        A support ticket was created on {$create_date}.<br/>
        <pre>{$content}</pre>
        <br/>
        If you would like to view your Ticket online, please click the following link:<br/>
        <a href="{$url}">{$url}</a><br/>

        ____<br/>
        {$sig}
    </body>
</html>
HTML;

$this->AltBody = <<<TEXT
A support ticket was created on {$create_date}.

{$content}

If you would like to view your Ticket online, please click the following link:<br/>
{$url}

____
{$sig}
TEXT;

    }
}

