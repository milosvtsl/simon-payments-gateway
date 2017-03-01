<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/28/2016
 * Time: 1:32 PM
 */
namespace View\Error\Mail;


use System\Config\SiteConfig;

require_once dirname(dirname(dirname(dirname(__DIR__)))) . '/support/PHPMailer/PHPMailerAutoload.php';

class ErrorEmail extends \PHPMailer
{
    public function __construct(\Exception $Ex) {
        parent::__construct();

        $this->Host = SiteConfig::$EMAIL_SERVER_HOST;
        $this->Username = SiteConfig::$EMAIL_USERNAME;
        $this->Password = SiteConfig::$EMAIL_PASSWORD;
        $this->Port = SiteConfig::$EMAIL_SERVER_PORT;
        $this->Timeout = SiteConfig::$EMAIL_TIMEOUT;
        $this->SMTPAuth = SiteConfig::$EMAIL_SMTP_AUTH;
        $this->SMTPSecure = SiteConfig::$EMAIL_SMTP_SECURE;
        if (SiteConfig::$EMAIL_SMTP_AUTH)
            $this->isSMTP();

        if (SiteConfig::$EMAIL_FROM_ADDRESS)
            $this->setFrom(SiteConfig::$EMAIL_FROM_ADDRESS, SiteConfig::$EMAIL_FROM_TITLE);


        $this->addBCC("support@simonpayments.com", "Debug User");

        $this->Subject = "Error: " . $Ex->getMessage();

        $sig = SiteConfig::$SITE_NAME;

        $content = "Exception: " . print_r($Ex, true);
        $content .= "\nSession: " . print_r($_SESSION, true);

        if (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
            $details = json_decode(file_get_contents("http://ipinfo.io/{$ip}/json"));
            foreach ($details as $k => $v)
                $content .= "\n{$k}: {$v}";
        }

        $this->isHTML(true);
        $this->Body = <<<HTML
<html>
    <body>
        An Exception has occurred:<br/>
        <pre>{$content}</pre>
        <br/>

        ____<br/>
        {$sig}
    </body>
</html>
HTML;

        $this->AltBody = <<<TEXT
An Exception has occurred:
{$content}

____
{$sig}
TEXT;


    }
}