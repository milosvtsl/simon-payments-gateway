<?php
use Merchant\Model\MerchantRow;
use User\Session\SessionManager;
use Order\Model\OrderRow;

$SessionManager = new SessionManager();
$SessionUser = $SessionManager->getSessionUser();


if($SessionUser->hasAuthority('ROLE_ADMIN')) {
    $stats = OrderRow::queryMerchantStats();
} else {
    $stats = OrderRow::queryMerchantStats($SessionUser->getID());
}

$button_current = 'dashboard';
include '.dashboard.nav.php';
?>

<article class="themed">

    <section class="content dashboard-section">
        <!-- Bread Crumbs -->
        <aside class="bread-crumbs">
            <a href="/" class="nav_home"><?php echo $SessionUser->getFullName(); ?></a>
            <a href="/" class="nav_dashboard">Welcome</a>
        </aside>


        <?php if($this->hasMessage()) echo "<h5>", $this->getMessage(), "</h5>"; ?>

        <h5>Dashboard Under Construction...</h5>

        <div class="stat-box-container">
            <div class="stat-box stat-box-first">
                <div class="stat-large">$<?php echo number_format(@$stats['today'], 2); ?> (<?php echo @$stats['today_count']; ?>)</div>
                <div>Today's Orders</div>
            </div>
            <div class="stat-box stat-box-second">
                <div class="stat-large">$<?php echo number_format(@$stats['weekly'], 2); ?> (<?php echo @$stats['weekly_count']; ?>)</div>
                <div>This Week's Orders</div>
            </div>
            <div class="stat-box stat-box-third">
                <div class="stat-large">$<?php echo number_format(@$stats['monthly'], 2); ?> (<?php echo @$stats['monthly_count']; ?>)</div>
                <div>This Months's Orders</div>
            </div>
            <div class="stat-box stat-box-fourth">
                <div class="stat-large">$<?php echo number_format(@$stats['yearly'], 2); ?> (<?php echo @$stats['yearly_count']; ?>)</div>
                <div>YTD Orders</div>
            </div>
        </div>


    </section>

</article>