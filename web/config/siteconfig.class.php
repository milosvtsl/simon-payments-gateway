<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/28/2016
 * Time: 8:20 PM
 */
namespace Config;
use View\Theme\Basic\DefaultViewTheme;

/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/28/2016
 * Time: 1:18 PM
 */
class SiteConfig
{
    static $SITE_NAME = 'Simon Payments Gateway';
    static $DEFAULT_THEME = null;
    static $BASE_URL = 'admin.simonpayments.com';
    static $EMAIL_SERVER_HOST = null;
    static $EMAIL_SMTP_USERNAME;
    static $EMAIL_SMTP_PASSWORD;
    static $EMAIL_SERVER_PORT = 587;
    static $EMAIL_FROM_ADDRESS = "admin@simonpayments.com";
    static $EMAIL_FROM_TITLE = "Simon Payments Gateway";

    public static function getDefaultViewTheme() {
        static $default = null;
//        try {
            return $default ?: $default = new SiteConfig::$DEFAULT_THEME;
//        } catch (\Exception $ex) {
//            return $default ?: $default = DefaultViewTheme::get();
//        }
    }
}
include_once __DIR__ .'/../../config.php';