<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/28/2016
 * Time: 8:20 PM
 */
namespace System\Config;

/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/28/2016
 * Time: 1:18 PM
 */
class SiteConfig
{
    static $SITE_NAME = 'Simon Payments Gateway';
    static $SITE_URL = 'https://access.simonpayments.com';
    static $DEFAULT_THEME = null;
    static $BASE_HREF = '/';
    static $EMAIL_SERVER_HOST = null;
    static $EMAIL_USERNAME;
    static $EMAIL_PASSWORD;
    static $EMAIL_SERVER_PORT = 587;
    static $EMAIL_FROM_ADDRESS = "admin@simonpayments.com";
    static $EMAIL_FROM_TITLE = "Simon Payments Gateway";
    static $MAX_TRANSACTION_AMOUNT = 5000;
    static $EMAIL_SMTP_AUTH = false;
    static $EMAIL_SMTP_SECURE = 'ssl'; // 'tls';
    static $EMAIL_TIMEOUT = 10;

    public static function getDefaultViewTheme() {
        static $default = null;
//        try {
            return $default ?: $default = new SiteConfig::$DEFAULT_THEME;
//        } catch (\Exception $ex) {
//            return $default ?: $default = DefaultViewTheme::get();
//        }
    }
}

require_once 'dbconfig.class.php';
include_once __DIR__ .'/../../../config.php';