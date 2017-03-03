<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/28/2016
 * Time: 1:32 PM
 */
namespace System\Mail;


use Merchant\Model\MerchantRow;
use User\Model\UserRow;
use System\Config\SiteConfig;
use User\Session\SessionManager;

@define("PHPMAILER_DIR", dirname(dirname(dirname(__DIR__))) . '/support/PHPMailer/');
require_once PHPMAILER_DIR . 'PHPMailerAutoload.php';
require_once PHPMAILER_DIR . 'class.smtp.php';

abstract class AbstractEmail extends \PHPMailer
{
    public function __construct() {
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

    }

    protected function processTemplate(&$body, &$subject, Array &$params) {
        // Query email template


        foreach($params as $name => $value)
            $body = str_replace('{$' . $name . '}', $value, $body);
        foreach($params as $name => $value)
            $subject = str_replace('{$' . $name . '}', $value, $subject);

        if(strpos($body, '{$')>=0)
            error_log("Not all variables were replaced: \n" . $body);
        if(strpos($subject, '{$')>=0)
            error_log("Not all variables were replaced: \n" . $subject);
    }
}

