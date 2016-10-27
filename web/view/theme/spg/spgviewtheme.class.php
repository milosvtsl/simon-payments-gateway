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

    public function renderHTMLBodyHeader($flags=0)
    {
        $SessionManager = new SessionManager();
        $SessionUser = $SessionManager->getSessionUser();
        ?>
    <body class="spg-theme">
        <?php if(!($flags && static::FLAG_HEADER_MINIMAL)) { ?>

        <header class="hide-on-print hide-on-layout-vertical">
            <a href="/">
                <img src="view/theme/spg/assets/img/logo.png" alt="Simon Payments Gateway" style="width:22em;">
            </a>
        </header>
        <nav class="site-menu hide-on-print">

        <?php if($SessionManager->isLoggedIn()) { ?>
            <a href="user/dashboard.php" class="nav-login"><div class="nav-icon nav-dashboard-icon"></div><br /> Dashboard </a>
            <a href="transaction/charge.php" class="nav-charge">  <div class="nav-icon nav-charge-icon"></div><br/>Charge</a>
            <a href="user/account.php" class="nav-account"><div class="nav-icon nav-account-icon"></div><br/> My Account </a>
        <?php } else { ?>
            <a href="/" class="nav-login"><div class="nav-icon nav-home-icon"></div><br/> Home</a>
            <a href="signup.php" class="nav-login"> <div class="nav-icon nav-signup-icon"></div><br/> Signup </a>
            <a href="login.php" class="nav-login"> <div class="nav-icon nav-login-icon"></div><br/>Login </a>
        <?php } ?>

        </nav>

        <span class="site-welcome-text hide-on-print">
            Welcome, <?php echo $SessionUser->getFirstName()?:$SessionUser->getUsername(); ?>
            <a href="user/logout.php" style="font-size:small; text-decoration: none;">(Log out)</a>
        </span>

        <hr class="themed hide-on-print" style="clear: both;"/>
        <?php } ?>
        <?php
    }

    public function renderHTMLBodyFooter($flags=0)
    {
//        </article>
        ?>
        <?php if(!($flags && static::FLAG_FOOTER_MINIMAL)) { ?>
        <footer class="hide-on-print">
            &copy; 2016 Simon Payments, LLC. All rights reserved.
        </footer>
        <?php } ?>
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
        echo <<<HEAD
        <script src="assets/js/date-input/nodep-date-input-polyfill.dist.js"></script>
        <link href='view/theme/spg/assets/spg-theme.css' type='text/css' rel='stylesheet'>
        <script src="view/theme/spg/assets/spg-theme.js"></script>
        <link rel="icon" href="view/theme/spg/assets/img/favicon.ico">
HEAD;
    }

    public function renderHTMLMetaTags($flags=0) {
    }
}