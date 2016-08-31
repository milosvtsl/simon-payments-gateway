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
    static $SITE_NAME = 'PHP Website';
    static $DEFAULT_THEME = null;
    static $BASE_URL = '/';

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