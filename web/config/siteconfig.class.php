<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/28/2016
 * Time: 8:20 PM
 */
namespace Config;
use View\Theme\SPG\DefaultViewTheme;

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

    public static function getDefaultViewTheme()
    {
        return static::$DEFAULT_THEME ?: DefaultViewTheme::get();
    }
}
include_once __DIR__ .'/../../config.php';