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
        $useragent=$_SERVER['HTTP_USER_AGENT'];
        $body_class = ' layout-horizontal';
        if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4))) {
            $body_class = ' layout-vertical';
        }

        $SessionManager = new SessionManager();
        $SessionUser = $SessionManager->getSessionUser();

        ?>
    <body class="spg-theme <?php echo $body_class; ?>">
        <?php if(!($flags && static::FLAG_HEADER_MINIMAL)) { ?>

        <header class="hide-on-print hide-on-layout-vertical">
            <a href="/">
                <img src="view/theme/spg/assets/img/logo.png" alt="Simon Payments Gateway" style="">
            </a>
            <nav class="site-menu hide-on-print">

                <div class="site-welcome-text hide-on-print">
                    <?php echo $SessionUser->getFullName()?:$SessionUser->getUsername(); ?>
                    <br />
                    Welcome
                </div>
            <?php if($SessionManager->isLoggedIn()) { ?>
    <!--            <a href="user/dashboard.php" class="nav-login"><div class="nav-icon nav-dashboard-icon"></div><br /> Dashboard </a>-->
                <?php if($SessionUser->hasAuthority('ROLE_POST_CHARGE')) { ?>
                    <a href="transaction/charge.php" class="nav-charge">  <div class="nav-icon nav-charge-icon"></div><br/>Charge</a>
                <?php } ?>

                <a href="user/logout.php" class="nav-logout"> <div class="nav-icon nav-logout-icon"></div><br/>Log Out</a>
            <?php } else { ?>
    <!--            <a href="/" class="nav-login"><div class="nav-icon nav-home-icon"></div><br/> Home</a>-->
    <!--            <a href="signup.php" class="nav-login"> <div class="nav-icon nav-signup-icon"></div><br/> Signup </a>-->
                <a href="login.php" class="nav-login"> <div class="nav-icon nav-login-icon"></div><br/>Login </a>
            <?php } ?>

            </nav>

            <hr class="themed hide-on-print" style="clear: both;"/>
        </header>
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