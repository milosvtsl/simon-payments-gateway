<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/28/2016
 * Time: 8:15 PM
 */
namespace View\Theme\CourtPay;

use System\Config\SiteConfig;
use User\Model\GuestUser;
use User\Session\SessionManager;
use View\Theme\AbstractViewTheme;

class CourtPayViewTheme extends AbstractViewTheme
{
    private $breadcrumbs = array();

    public function __construct()
    {

    }

    /**
     * Add a path (bread crumb) url
     * @param $name
     * @param $url
     * @return mixed
     */
    public function addPathURL($url, $name) {
        $this->breadcrumbs[] = array($url, $name);
    }

    public function renderHTMLBodyHeader($flags=0)
    {
        static $rendered = false;
        if($rendered)
            return;
        $rendered = true;

        $useragent=$_SERVER['HTTP_USER_AGENT'];
        $body_class = ' layout-horizontal layout-full';
        if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4))) {
            $body_class = ' layout-vertical layout-narrow';
        }

        $SessionManager = new SessionManager();
        if(!$SessionManager->isLoggedIn()) {
            $body_class .= ' layout-guest';
//        $SessionUser = $SessionManager->getSessionUser();
        }

        ?>
    <body class="courtpay-theme <?php echo $body_class; ?>">
        <?php if(!($flags && static::FLAG_HEADER_MINIMAL)) { ?>
        <div class="body-container">

            <header class="themed hide-on-print hide-on-layout-vertical">
                <a href=".">
                    <div class="logo"></div>
                </a>
            </header>

            <?php } ?>
        <?php
    }

    public function renderHTMLBodyFooter($flags=0)
    {
//        </article>
            ?>
        <?php if(!($flags && static::FLAG_HEADER_MINIMAL)) { ?>
        </div>
        <?php } ?>
        <?php if(!($flags && static::FLAG_FOOTER_MINIMAL)) { ?>
        <footer class="hide-on-print">
            <span>&copy; <?php echo date('Y'); ?> CourtPay, LLC. All rights reserved.</span>
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
        $vjs = '?v=' . filemtime(__DIR__ . '/assets/courtpay-theme.js');
        $vcss = '?v=' . filemtime(__DIR__ . '/assets/courtpay-theme.css');
        if(!in_array(@strtoupper($_SERVER['COMPUTERNAME']), array('NOBISERV', 'KADO', 'ROBOS')))
            $vcss = $vjs = '';

        echo <<<HEAD
        <meta name="viewport" content="width=device-width, initial-scale=0.6, maximum-scale=2, user-scalable=1">
        
        <script src="assets/js/date-input/nodep-date-input-polyfill.dist.js"></script>
        
        <link href='view/theme/courtpay/assets/courtpay-theme.css{$vcss}' type='text/css' rel='stylesheet'>
        <script src="view/theme/courtpay/assets/courtpay-theme.js{$vjs}"></script>
        <link rel="icon" href="view/theme/courtpay/assets/img/favicon.ico">
HEAD;
    }

    public function renderHTMLMetaTags($flags=0) {
    }

    public function printHTMLMenu($category, $action_url=null) {
        static $rendered = false;
        if($rendered)
            return;
        $rendered = true;


        $SessionManager = new SessionManager();

        if(!$SessionManager->isLoggedIn())
            return;

        $SessionUser = $SessionManager->getSessionUser();

        list($main) = explode('-', $category, 2);
        $mc = array();
        $mc[@$main] = ' current';
        $mc[@$category] = ' current';
        ?>

        <ul class="page-menu hide-on-print">
            <li class="menu-submenu menu-submenu-order">
                <a href="." class="button<?php echo @$mc['dashboard']; ?>"><div class="menu-icon menu-icon-dashboard"></div>
                    <span>Home</span></a>
            </li>

            <?php if($SessionUser->hasAuthority('ADMIN', 'SUB_ADMIN', 'POST_CHARGE')) { ?>
                <li class="menu-submenu menu-submenu-charge">
                    <a href="order/charge.php" class="button<?php echo @$mc['order-charge']; ?>"><div class="menu-icon menu-icon-charge"></div>
                        <span>Charge Card</span> </a>
                </li>
            <?php } ?>

            <?php if($SessionUser->hasAuthority('ADMIN', 'SUB_ADMIN', 'RUN_REPORTS')) { ?>
                <li class="menu-submenu menu-submenu-search">
                    <a href="order/list.php?date_from=<?php echo date('Y-m-d', time() - 60*60*4); ?>" class="button<?php echo @$mc['order-list']; ?>"><div class="menu-icon menu-icon-list"></div>
                        <span>Search</span> </a>
                </li>
            <?php } ?>

            <?php if($SessionUser->hasAuthority('ADMIN', 'SUB_ADMIN')) { ?>
                <li class="menu-submenu menu-submenu-merchant">
                    <a href="merchant/list.php" class="button<?php echo @$mc['merchant-list']; ?>"><div class="menu-icon menu-icon-list"></div>
                        <span><?php echo SiteConfig::$SITE_DEFAULT_MERCHANT_NAME; ?>s</span> </a>
                </li>
            <?php } ?>


            <?php if($SessionUser->hasAuthority('ADMIN')) { // TODO: merge with merchant ?>
                <li class="menu-submenu menu-submenu-integration">
                    <a href="integration" onclick="if (this.classList.toggle('current')) return false;" class="button<?php echo @$mc['integration']; ?>"> <div class="menu-icon menu-icon-integration"></div>
                        <span>Admin</span></a>
                    <ul>
                        <?php if($SessionUser->hasAuthority('ADMIN', 'SUB_ADMIN')) { ?>
                            <li>
                                <a href="user/list.php" class="button<?php echo @$mc['user-list']; ?>"><div class="menu-icon menu-icon-list"></div>
                                    <span>List Users</span></a>
                            </li>
                            <li>
                                <a href="user/add.php" class="button<?php echo @$mc['user-add']; ?>"> <div class="menu-icon menu-icon-add"></div>
                                    <span>Add User</span></a>
                            </li>
                        <?php } ?>

                        <?php if($SessionUser->hasAuthority('ADMIN')) { ?>
                        <li>
                            <a href="merchant/add.php" class="button<?php echo @$mc['merchant-add']; ?>"><div class="menu-icon menu-icon-add"></div>
                                <span>Add Merchant</span> </a>
                        </li>
                        <?php } ?>

                        <li>
                            <a href="integration" class="button<?php echo @$mc['integration']; ?>"><div class="menu-icon menu-icon-list"></div>
                                <span>API Endpoints</span></a>
                        </li>
                        <li>
                            <a href="integration/request/" class="button<?php echo @$mc['integration-requests']; ?>"><div class="menu-icon menu-icon-list"></div>
                                <span>API Requests</span></a>
                        </li>

                        <li>
                            <a href="merchant/form.php" class="button<?php echo @$mc['merchant-form-list']; ?>"><div class="menu-icon menu-icon-customize"></div>
                                <span>Custom Order Forms</span> </a>
                        </li>
                    </ul>
                </li>
            <?php } ?>
            <li class="menu-submenu " style="float: right;">
                <a href="#" onclick="return false;" class="menu-button-account hide-on-layout-guest">
                    <div class="menu-icon menu-icon-sub-menu"></div>
                    <ul class="menu-sub-menu">
                        <li>
                            <a href="user/account.php" class="button">
                                <div class="menu-icon menu-icon-account"></div>
                                <span>My Account</span></a>
                        </li>
                        <li>
                            <a href="user/account.php?action=edit" class="button">
                                <div class="menu-icon menu-icon-edit"></div>
                                <span>Edit Account</span></a>
                        </li>
                        <li>
                            <a href="user/logout.php" class="button">
                                <div class="menu-icon menu-icon-logout"></div>
                                <span>Log out</span></a>
                        </li>
                    </ul>
                </a>
            </li>

            <li class="menu-submenu" style="float: right;">
                <form action="order" style="display: inline-block; float: right;">
                    <input name="search" tabindex="1" type="text" class="menu-search themed" placeholder="Search TID, MID, Name, Invoice ID..." />
                </form>
            </li>

        </ul>


        <aside class="sub-header hide-on-print">

            <span class="bread-crumbs">
            <?php
            foreach($this->breadcrumbs as $i => $breadcrumb) {
                list($url, $name) = $breadcrumb;
                if($i > 0)
                    echo ' | ';
                echo "\n\t\t\t<a class='breadcrumb' href='", $url, "'>", $name, "</a>";
            }
            ?>
            </span>

        </aside>

        <?php
    }

//    public function printBreadCrumbs($urlHTML) {
//
//        echo <<<HTML
//        <!-- Bread Crumbs -->
//        <aside class="bread-crumbs">
//            {$urlHTML}
//        </aside>
//HTML;
//
//    }
}