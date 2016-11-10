<?php
use Merchant\Model\MerchantRow;
use User\Session\SessionManager;
use Order\Model\OrderRow;
/** @var $this \View\Error\ErrorView */
$SessionManager = new SessionManager();
$SessionUser = $SessionManager->getSessionUser();

$Exception = $this->getException();
?>

    <!-- Page Navigation -->
    <nav class="page-menu hide-on-print">
        <?php if($SessionManager->isLoggedIn()) { ?>
            <a href="/" class="button hide-on-layout-horizontal1">Dashboard <div class="submenu-icon submenu-icon-dashboard"></div></a>
            <?php if($SessionUser->hasAuthority('ROLE_POST_CHARGE', 'ROLE_ADMIN', 'ROLE_SUB_ADMIN')) { ?>
                <a href="transaction/charge.php?" class="button">Charge  <div class="submenu-icon submenu-icon-charge"></div></a>
            <?php } ?>
            <a href="user/account.php" class="button">My Account <div class="submenu-icon submenu-icon-account"></div></a>
            <a href="user/logout.php" class="button">Log out<div class="submenu-icon submenu-icon-logout"></div></a>
        <?php } else { ?>
            <a href="login.php" class="button">Log in<div class="submenu-icon submenu-icon-login"></div></a>
        <?php } ?>

    </nav>

    <article class="themed">

        <section class="content dashboard-section">
            <!-- Bread Crumbs -->
            <aside class="bread-crumbs">
                <a href="/" class="nav_dashboard">Error: <?php echo $Exception->getMessage(); ?></a>
            </aside>

            <div style="text-align: center;">
                <div class="error" style="white-space: pre; padding: 1em; display: inline-block; text-align: left;">An unexpected error has occurred:
<?php echo $Exception->getMessage(); ?>


Support has been informed.
Please try this function again soon.
<button onclick="window.history.back()" class="themed" style="padding: 1em; margin-top: 1em; float: right;">Go Back</button></div>
            </div>
        </section>

    </article>