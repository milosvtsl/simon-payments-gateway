<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/28/2016
 * Time: 1:32 PM
 */
namespace User\Mail;


use System\Config\SiteConfig;
use PHPMailer;
use User\Model\UserRow;

require_once dirname(dirname(__DIR__)) . '/system/support/PHPMailer/PHPMailerAutoload.php';

class ResetPasswordEmail extends \PHPMailer
{
    public function __construct(UserRow $User) {
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


        $this->addAddress($User->getEmail(), $User->getFullName());
        $this->addBCC("ari@govpaynetwork.com", $User->getFullName());

        $this->Subject = "Reset your password";

        $key = crypt($User->getPasswordHash(), md5(time()));
//        $key2 = crypt($User->getPasswordHash(), $key);

        $pu = parse_url(@$_SERVER['REQUEST_URI']);
        $url = (@$pu["host"]?:SiteConfig::$SITE_URL?:'localhost') . '/reset.php?key='.$key.'&email='.$User->getEmail();
        $username = $User->getUsername();
        $sig = SiteConfig::$SITE_NAME;

        $source = '';
        if(!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'] ?: '68.3.245.85';
            $source .= "\nIP Address: " . $ip;
            $details = json_decode(file_get_contents("http://ipinfo.io/{$ip}/json"));
            foreach($details as $k=>$v)
                $source .= "\n{$k}: {$v}" ;
        }

        $this->isHTML(true);
        $this->Body = <<<HTML
<html>
    <body>
        A password reset has been requested for the following account:<br/>
        Username: {$username}<br/>
        <pre>{$source}</pre>
        <br/>
        If you want to perform a password reset on this account, please click the following link:<br/>
        <a href="{$url}">{$url}</a><br/>

        ____<br/>
        {$sig}
    </body>
</html>
HTML;

        $this->AltBody = <<<TEXT
A password reset has been requested for the following account:
Username: {$username}
{$source}

If you want to perform a password reset on this account, please click the following link:
<a href="{$url}">{$url}</a>

____
{$sig}
TEXT;


    }

    public function send2() {
        if($_SERVER['HTTP_HOST'] === 'localhost') {
            $log = "<pre>Email was sent from localhost\n". print_r($this, true) . "</pre>";
            echo $log;
            error_log($log);
            return true;
        }
        return parent::send();
    }
}

