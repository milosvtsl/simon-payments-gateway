<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/28/2016
 * Time: 8:15 PM
 */
namespace View\Theme\SPG;

use User\SessionManager;
use View\Theme\AbstractViewTheme;

class SPGViewTheme extends AbstractViewTheme
{
    public function __construct()
    {
        $SessionManager = new SessionManager();
        $this->addNavLink('home.php', "Home");

        if($SessionManager->isLoggedIn()) {
            $this->addNavLink('merchant.php', "Merchant");
            $this->addNavLink('user.php', "User");
            $this->addNavLink('news.php', "News");
            $this->addNavLink('charge.php', "Charge");
            $this->addNavLink('search.php', "Search");
            $this->addNavLink('logout.php', "Log Out");

        } else {
            $this->addNavLink('login.php', "Log In");
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
        <aside>
            <?php
            foreach ($this->getCrumbLinkHTML() as $html)
                echo "\n\t\t", $html;
            ?>
        </aside>
        <nav>
            <?php
            foreach ($this->getNavLinkHTML() as $html)
                echo "\n\t\t", $html;
            ?>
        </nav>

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