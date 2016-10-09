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


        $CURRENT_URL = ltrim($_SERVER["REQUEST_URI"], '/');
//        if(preg_match('/^(transaction|order|batch)/i',$CURRENT_URL)) {
//
//            if($SessionUser->hasAuthority('ROLE_ADMIN', 'ROLE_POST_CHARGE')) {
//                $this->addNavLink('transaction/charge.php', "Charge");
//            } else {
//                $this->addNavLink('home', "Home");
//            }
//            $this->addNavLink('transaction', "Transactions");
//            $this->addNavLink('login.php?action=logout', "Log Out");
//
//        } else if(preg_match('/^(merchant)/i',$CURRENT_URL)) {
//            $this->addNavLink('home', "Home");
//            $this->addNavLink('merchant', "Merchants");
//            $this->addNavLink('login.php?action=logout', "Log Out");
//
//        } else if(preg_match('/^(user)/i',$CURRENT_URL)) {
//            $this->addNavLink('home', "Home");
//
//            if($SessionUser->hasAuthority('ROLE_ADMIN')) {
//                $this->addNavLink('user', "Users");
//            } else {
//                $this->addNavLink('user?id=' . $SessionUser->getID(), "My Account");
//            }
//
//            $this->addNavLink('login.php?action=logout', "Log Out");
//
//        } else {
//
//            if($SessionUser->hasAuthority('ROLE_ADMIN')) {
//                $this->addNavLink('merchant', "Merchants");
//                $this->addNavLink('transaction', "Transactions");
//                $this->addNavLink('user', "Users");
//
//            } else if($SessionUser->hasAuthority('ROLE_POST_CHARGE')) {
//                $this->addNavLink('transaction/charge.php', "Charge");
//                $this->addNavLink('transaction', "Transactions");
//                $this->addNavLink('user?id=' . $SessionUser->getID(), "My Account");
//
//            } else {
//                $this->addNavLink('user?id=' . $SessionUser->getID(), "My Account");
//                $this->addNavLink('transaction', "Transactions");
//                $this->addNavLink('login.php?action=logout', "Log Out");
//            }


//        }


//        $this->addNavLink('home', "Home");
//        $this->addNavLink('news', "News");
//        $this->addNavLink('order', "Orders");
//        $this->addNavLink('transaction', "Orders");
//                $this->addNavLink('user', "Users");
//                $this->addNavLink('batch', "Batch");
//                $this->addNavLink('integration', "Integration");

    }

    public function renderHTMLBodyHeader()
    {

        $SessionManager = new SessionManager();
        $SessionUser = $SessionManager->getSessionUser();
        ?>
    <body class="spg-theme">
        <header class="small-on-print">
            <a href="/home">
                <img src="view/theme/spg/assets/img/logo.png" alt="Simon Payments Gateway" style="width:22em;">
            </a>
        </header>
        <nav class="site-menu hide-on-print">
            <a href="order" class="nav_order">Orders <br/> <div class="nav_icon nav_order_icon"></div> </a>
        <?php if($SessionUser->hasAuthority('ROLE_ADMIN')) { ?>
            <a href="merchant" class="nav_merchant">Merchants <br/> <div class="nav_icon nav_merchant_icon"></div></a>
        <?php } else { ?>
            <a href="user" class="nav_user">My Account <br/> <div class="nav_icon nav_user_icon"></div></a>
        <?php } ?>

        <?php if($SessionUser->hasAuthority('ROLE_ADMIN', 'ROLE_POST_CHARGE')) { ?>
            <a href="transaction/charge.php" class="nav_charge">Charge <br/> <div class="nav_icon nav_charge_icon"></div></a>
        <?php } ?>

        </nav>

        <hr class="themed" style="clear: both;"/>
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
        <link href='view/theme/spg/assets/spg-theme.css' type='text/css' rel='stylesheet'>
        <link rel="icon" href="view/theme/spg/assets/img/favicon.ico">
<?php
    }

    public function renderHTMLMetaTags() {
    }
}