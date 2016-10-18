<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/28/2016
 * Time: 1:32 PM
 */
namespace System\Mail;


use Config\SiteConfig;
use User\Model\UserRow;

require_once dirname(__DIR__) . '/support/PHPMailer/PHPMailerAutoload.php';

class ResetPasswordEmail extends \PHPMailer
{
    public function __construct(UserRow $User) {
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

        $this->addAddress($User->getEmail(), $User->getFullName());

        $this->Subject = "Reset your password";

        $this->isHTML(false);

        $key = crypt($User->getPasswordHash(), md5(time()));
//        $key2 = crypt($User->getPasswordHash(), $key);

        $pu = parse_url($_SERVER['REQUEST_URI']);
        $url = (@$pu["scheme"]?:'https') . "://" . (@$pu["host"]?:SiteConfig::$BASE_URL?:'localhost') . '/reset.php?key='.$key.'&email='.$User->getEmail();
        $username = $User->getUsername();
        $this->Body = <<<HTML
A password reset has been requested for the following account:
Username: {$username}

If you want to perform a password reset on this account, please click the following link:
<a href="{$url}">{$url}</a>
HTML;


    }
}

