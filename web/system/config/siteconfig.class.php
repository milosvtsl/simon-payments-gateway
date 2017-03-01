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
    static $SITE_LIVE = FALSE;
    static $SITE_NAME = 'Simon Payments Gateway';
    static $SITE_URL = 'https://access.simonpayments.com';
    static $SITE_MAX_TRANSACTION_AMOUNT = 20000;
    static $SITE_DEFAULT_CUSTOMER_NAME = "Customer";
    static $SITE_DEFAULT_MERCHANT_NAME = "Merchant";
    static $SITE_AUTO_LOGIN_ENABLED = false;
    static $SITE_AUTO_LOGIN_ACCOUNT = 'guest';
    static $SITE_MAX_LOGO_WIDTH = 600;
    static $SITE_MAX_LOGO_HEIGHT = 300;
    static $MAX_UPLOAD_SIZE = 102400;

    static $DEFAULT_THEME = null;

    static $BASE_HREF = '/';

    static $DEBUG_MODE = false;

    static $EMAIL_SERVER_HOST = null;
    static $EMAIL_USERNAME;
    static $EMAIL_PASSWORD;
    static $EMAIL_SERVER_PORT = 587;
    static $EMAIL_FROM_ADDRESS = "support@simonpayments.com";
    static $EMAIL_FROM_TITLE = "Simon Payments Gateway";
    static $EMAIL_SMTP_AUTH = false;
    static $EMAIL_SMTP_SECURE = 'ssl'; // 'tls';
    static $EMAIL_TIMEOUT = 10;
    static $SITE_UID_PREFIX = 'SP';


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