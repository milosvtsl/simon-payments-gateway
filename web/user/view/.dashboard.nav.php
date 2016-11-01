<?php
use User\Session\SessionManager;
/** @var \User\View\LoginView $this  **/

$SessionManager = new SessionManager();
$SessionUser = $SessionManager->getSessionUser();
$button_current = @$button_current ?: 'dashboard';
$ca = array();
$ca[@$button_current] = ' current';
?>

    <!-- Page Navigation -->
    <nav class="page-menu hide-on-print">
        <a href="/" class="button<?php echo @$ca['dashboard']; ?>">Dashboard <div class="submenu-icon submenu-icon-dashboard"></div></a>
        <?php if($SessionUser->hasAuthority('ROLE_ADMIN', 'ROLE_POST_CHARGE')) { ?>
            <a href="transaction/charge.php" class="button<?php echo @$ca['charge']; ?>">Charge<div class="submenu-icon submenu-icon-charge"></div></a>
        <?php } ?>
        <a href="user/account.php" class="button<?php echo @$ca['account']; ?>">My Account <div class="submenu-icon submenu-icon-view"></div></a>

        <?php if($SessionUser->hasAuthority('ROLE_ADMIN', 'ROLE_RUN_REPORTS')) { ?>
            <a href="order?" class="button<?php echo @$ca['order']; ?>">Transactions <div class="submenu-icon submenu-icon-list"></div></a>
        <?php } ?>
        <?php if($SessionUser->hasAuthority('ROLE_ADMIN')) { ?>
            <a href="merchant?" class="button<?php echo @$ca['merchant']; ?>">Merchants <div class="submenu-icon submenu-icon-list"></div></a>
            <a href="user?" class="button<?php echo @$ca['user']; ?>">Users <div class="submenu-icon submenu-icon-list"></div></a>
            <a href="integration?" class="button<?php echo @$ca['integration']; ?>">Integration <div class="submenu-icon submenu-icon-list"></div></a>
        <?php } ?>
    </nav>

