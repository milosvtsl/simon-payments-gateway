<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/28/2016
 * Time: 8:15 PM
 */
namespace View\Theme\Basic;

use User\Session\SessionManager;
use View\Theme\AbstractViewTheme;

class DefaultViewTheme extends AbstractViewTheme
{

    public function __construct()
    {
        $SessionManager = new SessionManager();
        $SessionUser = $SessionManager->getSessionUser();

//        $this->addNavLink('home', "Home");
//        $this->addNavLink('news', "News");
//        $this->addNavLink('order', "Orders");
        $this->addNavLink('transaction', "Transactions");

        if($SessionManager->isLoggedIn()) {
            if($SessionUser->hasAuthority('ROLE_ADMIN')) {
                $this->addNavLink('merchant', "Merchant");
//                $this->addNavLink('user', "Users");
//                $this->addNavLink('batch', "Batch");
//                $this->addNavLink('integration', "Integration");

            } else {
                $this->addNavLink('user?uid=' . $SessionUser->getUID(), "My Account");
            }

            if($SessionUser->hasAuthority('ROLE_ADMIN', 'ROLE_POST_CHARGE')) {
                $this->addNavLink('order/charge.php', "Charge");
            }

//            $this->addNavLink('login.php?action=logout', "Log Out");

        } else {
//            $this->addNavLink('login.php?action=login', "Log In");
        }

    }

    public function renderHTMLBodyHeader($flags=0)
    {

        ?>
        <body class="basic-theme">
        <header>
            <a href=".">
                <img src="view/theme/basic/assets/img/logo.png" alt="Simon Payments Gateway">
            </a>

        </header>

        <article class="themed">
        <?php
    }

    public function renderHTMLBodyFooter($flags=0)
    {
        ?>
        </article>
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
        <link href='view/theme/basic/assets/basic-theme.css' type='text/css' rel='stylesheet'>
        <link rel="icon" href="view/theme/basic/assets/img/favicon.ico">
        <?php
    }

    public function renderHTMLMetaTags($flags=0) {
    }

    public function printHTMLMenu($category, $action_url=null) {
        throw new \Exception("Not implemented");
        // TODO: Implement printHTMLMenu() method.
    }

    public function printBreadCrumbs($getFullName, $string) {
        throw new \Exception("Not implemented");
        // TODO: Implement printBreadCrumbs() method.
    }

    /**
     * Add a path (bread crumb) url
     * @param $name
     * @param $url
     * @return mixed
     */
    public function addPathURL($url, $name) {
        throw new \Exception("Not implemented");
        // TODO: Implement addPathURL() method.
    }
}