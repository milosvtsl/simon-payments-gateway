<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/28/2016
 * Time: 8:15 PM
 */
namespace View\Theme\Basic;

use Config\SiteConfig;
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
                $this->addNavLink('user?id=' . $SessionUser->getID(), "My Account");
            }

            if($SessionUser->hasAuthority('ROLE_ADMIN', 'ROLE_POST_CHARGE')) {
                $this->addNavLink('transaction/charge.php', "Charge");
            }

//            $this->addNavLink('login.php?action=logout', "Log Out");

        } else {
//            $this->addNavLink('login.php?action=login', "Log In");
        }

    }

    public function renderHTMLBodyHeader()
    {

        ?>
        <body class="basic-theme">
        <header>
            <a href="/">
                <img src="view/theme/basic/assets/img/logo.png" alt="Simon Payments Gateway">
            </a>

        </header>
        <nav>
            <?php
            foreach ($this->getNavLinkHTML() as $html)
                echo "\n\t\t", $html;
            ?>
        </nav>
        <aside class="bread-crumbs">
            <?php
            foreach ($this->getCrumbLinkHTML() as $i=>$html)
                echo ($i>0?' \ ':''), "\n\t\t", $html;
            ?>
        </aside>

        <article class="themed">
        <?php
    }

    public function renderHTMLBodyFooter()
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

    public function renderHTMLHeadScripts() {
    }

    public function renderHTMLHeadLinks() {
        ?>
        <link href='view/theme/basic/assets/basic-theme.css' type='text/css' rel='stylesheet'>
        <link rel="icon" href="view/theme/basic/assets/img/favicon.ico">
        <?php
    }

    public function renderHTMLMetaTags() {
    }
}