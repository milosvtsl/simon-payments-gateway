<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/28/2016
 * Time: 1:32 PM
 */
namespace User\Mail;

use System\Mail\AbstractEmail;
use User\Model\UserRow;
use System\Config\SiteConfig;

class UserWelcomeEmail extends AbstractEmail
{
    const TEMPLATE_HTML = '
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
UID:        {$user_uid}
IP:         {$ip}
{$ip_details}

____
{$sig}
</pre>';

    
    public function __construct(UserRow $User, $password='****') {
        parent::__construct();

        $this->addAddress($User->getEmail(), $User->getFullName());
        if($User->getAdminID()) {
            // Send a copy to admin
            $AdminUser = UserRow::fetchByID($User->getAdminID());
            $this->addAddress($AdminUser->getEmail(), $AdminUser->getFullName());
        }

        // Send a copy to support
        $this->addBCC("support@simonpayments.com", $User->getFullName());

        // Get IP Details
        $ip_details = NULL;
        $ip = NULL;
        if(!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
            $details = @json_decode(file_get_contents("http://ipinfo.io/{$ip}/json")) ?: array();
            foreach($details as $k=>$v)
                $ip_details .= "\n{$k}: {$v}" ;
        }

        $key = crypt($User->getPasswordHash(), md5(time()));
        $params = array(
            'key' => $key,
            'url' => SiteConfig::$SITE_URL,
            'url_reset' => SiteConfig::$SITE_URL . '/reset.php?key='.$key.'&email='.$User->getEmail(),
            'date' => date("M dS Y g:i a e"),
            'site_name' => SiteConfig::$SITE_NAME,
            'user_full_name' => $User->getFullName(),
            'username' => $User->getUsername(),
            'user_uid' => $User->getUID(),
            'sig' => SiteConfig::$SITE_NAME,
            'ip' => $ip,
            'ip_details' => $ip_details,
    );

        // Customize Email Template
        $body = static::TEMPLATE_HTML;
        $subject = "Welcome: " . $User->getFullName();
        $this->processTemplate($body, $subject, $params);
        $this->Subject = $subject;


        $this->isHTML(true);
        $this->Body = <<<HTML
<html>
    <body>
        {$body}
    </body>
</html>
HTML;

        $this->AltBody = strip_tags($body);
    }

}

