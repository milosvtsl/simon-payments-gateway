<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/28/2016
 * Time: 1:32 PM
 */
namespace User\Mail;


use Merchant\Model\MerchantRow;
use User\Model\UserRow;
use System\Config\SiteConfig;
use User\Session\SessionManager;

@define("PHPMAILER_DIR", dirname(dirname(dirname(__DIR__))) . '/support/PHPMailer/');
require_once PHPMAILER_DIR . 'PHPMailerAutoload.php';
require_once PHPMAILER_DIR . 'class.smtp.php';

class UserWelcomeEmail extends \PHPMailer
{
    public function __construct(UserRow $User, $password='****') {
        parent::__construct();

//        $SessionManager = new SessionManager();
//        $SessionUser = $SessionManager->getSessionUser();

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
        if($User->getAdminID()) {
            $AdminUser = UserRow::fetchByID($User->getAdminID());
            $this->addAddress($AdminUser->getEmail(), $AdminUser->getFullName());
        }
        $this->addBCC("support@simonpayments.com", $User->getFullName());

        $this->Subject = "Welcome: " . $User->getFullName();


        $ip_details = NULL;
        if(!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
            $details = json_decode(file_get_contents("http://ipinfo.io/{$ip}/json"));
            foreach($details as $k=>$v)
                $ip_details .= "\n{$k}: {$v}" ;
        }


        $params = array(
            'key' => crypt($User->getPasswordHash(), md5(time())),
            'url' => SiteConfig::$SITE_URL,
            'url_reset' => SiteConfig::$SITE_URL . '/reset.php?key='.$key.'&email='.$User->getEmail(),
            'date' => date("M dS Y g:i a e"),
            'site_name' => SiteConfig::$SITE_NAME,
            'user_full_name' => $User->getFullName(),
            'username' => $User->getUsername(),
            'userUID' => $User->getUID(),
            'sig' => SiteConfig::$SITE_NAME,
            'ip_details' => $ip_details,
    );

        $content = <<<'HTML'
<pre>
Welcome to {$site_name}, {$user_full_name}

You may use this url to log in:
URL:        <a href="{$url}">{$url}</a>
Username:   {$username}
Password:   {$password}

If you want to perform a password reset on this account, please click the following link:
Reset:      <a href="{$url_reset}">{$url_reset}</a>

Request Information
Date:       {$date}
UID:        {$userUID}

____
{$sig}
</pre>
HTML;

        // Send template to email customizer

        foreach($params as $name => $value)
            $content = str_replace('{$' . $name . '}', $value, $content);

        if(strpos($content, '{$')>=0)
            error_log("Not all variables were replaced: \n" . $content);



        $content .= <<<HTML
HTML;

        $this->isHTML(true);
        $this->Body = <<<HTML
<html>
    <body>
        {$content}
    </body>
</html>
HTML;

    $this->AltBody = strip_tags($content);

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

