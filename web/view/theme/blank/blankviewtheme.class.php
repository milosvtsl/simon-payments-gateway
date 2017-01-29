<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/28/2016
 * Time: 8:15 PM
 */
namespace View\Theme\Blank;

use View\Theme\AbstractViewTheme;

class BlankViewTheme extends AbstractViewTheme
{

    public function __construct()
    {
    }

    public function renderHTMLBodyHeader($flags=0)
    {

        ?>
        <body class="blank-theme">
        <?php
    }

    public function renderHTMLBodyFooter($flags=0)
    {
        ?>
        </body>
        <?php
    }

    // Static

    public static function get()
    {
        static $inst = null;
        return $inst ?: $inst = new static();
    }

    public function renderHTMLHeadScripts($flags=0) {
    }

    public function renderHTMLHeadLinks($flags=0) {
        ?>
        <link href='view/theme/blank/assets/blank-theme.css' type='text/css' rel='stylesheet'>
        <?php
    }

    public function renderHTMLMetaTags($flags=0) {
    }

    public function printHTMLMenu($category, $action_url=null) {

    }

    public function printBreadCrumbs($getFullName, $string) {
        // TODO: Implement printBreadCrumbs() method.
    }

    /**
     * Add a path (bread crumb) url
     * @param $name
     * @param $url
     * @return mixed
     */
    public function addPathURL($url, $name) {
        // TODO: Implement addPathURL() method.
    }
}