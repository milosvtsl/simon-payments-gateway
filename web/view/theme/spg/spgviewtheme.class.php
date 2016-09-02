<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/28/2016
 * Time: 8:15 PM
 */
namespace View\Theme\SPG;

use User\Session\SessionManager;
use View\Theme\AbstractViewTheme;

class SPGViewTheme extends AbstractViewTheme
{
    public function __construct()
    {
        $SessionManager = new SessionManager();
        $SessionUser = $SessionManager->getSessionUser();

        $this->addNavLink('home', "Home");
        $this->addNavLink('news', "News");
        $this->addNavLink('order', "Orders");
        $this->addNavLink('transaction', "Transactions");

        if($SessionManager->isLoggedIn()) {
            if($SessionUser->hasAuthority('ROLE_ADMIN')) {
                $this->addNavLink('merchant', "Merchant");
                $this->addNavLink('user', "User");
                $this->addNavLink('batch', "Batch");
                $this->addNavLink('transaction/charge.php', "Charge");

            } else if($SessionUser->hasAuthority('ROLE_POST_CHARGE')) {
                $this->addNavLink('transaction/charge.php', "Charge");
            }

            $this->addNavLink('login.php?action=logout', "Log Out");

        } else {
            $this->addNavLink('login.php?action=login', "Log In");
        }

    }

    public function renderHTMLBodyHeader()
    {

        ?>
    <body class="spg-theme">
        <header>
            <a href="/">
                <img src="view/theme/spg/assets/img/logo.png" alt="Simon Payments Gateway">
            </a>

<!--            <img class="nav-user-photo" src="assets/images/bg_3.png" alt="User Profile Image">-->
<!--            <span class="user-info">Welcome, Sherlock Holmes</span>-->
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

        <article>
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
        echo "\t\t<link href='view/theme/spg/assets/spg-theme.css' type='text/css' rel='stylesheet' />\n";
    }

    public function renderHTMLMetaTags() {
    }
}