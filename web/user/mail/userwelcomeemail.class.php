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
    const TITLE = "User Welcome Email";
    const BCC = '{$admin_email}, support@simonpayments.com';
    const TEMPLATE_SUBJECT = 'Welcome: {$user_full_name}';
    const TEMPLATE_BODY = '
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
            'user_email' => $User->getEmail(),
            'user_uid' => $User->getUID(),
            'sig' => SiteConfig::$SITE_NAME,
            'ip' => $ip,
            'ip_details' => $ip_details,
            'admin_email' => NULL,
        );

        if(SiteConfig::$EMAIL_FROM_ADDRESS)
            $this->from = array(
                SiteConfig::$EMAIL_FROM_ADDRESS => SiteConfig::$EMAIL_FROM_TITLE
            );



        $this->to = array(
            $User->getEmail() => $User->getFullName()
        );
        $this->bcc = array(
            SiteConfig::$EMAIL_FROM_ADDRESS => $User->getFullName()
        );
        if($User->getAdminID()) {
            // Send a copy to admin
            $AdminUser = UserRow::fetchByID($User->getAdminID());
            $params['admin_email'] = $AdminUser->getEmail();
        }

        // Customize Email Template
        $this->processTemplate($params, $User->getMerchantID());
    }

}

