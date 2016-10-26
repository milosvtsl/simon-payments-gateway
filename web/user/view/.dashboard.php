<?php
use Merchant\Model\MerchantRow;
use User\Session\SessionManager;
use Order\Model\OrderRow;
/** @var $this \View\AbstractView*/
$SessionManager = new SessionManager();
$SessionUser = $SessionManager->getSessionUser();

$stats = null;
if(!empty($_SESSION[__FILE__])) {
    $stats = $_SESSION[__FILE__];
    if($stats['_time']<time() - 60*10)
        $stats = null;
}
if(!$stats) {
    if($SessionUser->hasAuthority('ROLE_ADMIN')) {
        $stats = OrderRow::queryMerchantStats();
    } else {
        $stats = OrderRow::queryMerchantStats($SessionUser->getID());
    }
    $stats['_time'] = time();
    $_SESSION[__FILE__] = $stats;
    $this->setMessage("Calculated stats in " . number_format($stats['duration'], 2) . " s");
}

$year_to_date = date('Y-01-01 00:00:00');
$yearly  = date('Y-m-d 00:00:00', time() - 24*60*60*365);

$month_to_date = date('Y-m-01 00:00:00');
$monthly  = date('Y-m-d 00:00:00', time() - 24*60*60*30);

$week_to_date = date('Y-m-d 00:00:00', time() - 24*60*60*date('w'));
$weekly  = date('Y-m-d 00:00:00', time() - 24*60*60*7);

$today = date('Y-m-d 00:00:00', time() - 24*60*60);

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


        <div class="stat-box-container">
            <a href="order?date_from=<?php echo $today; ?>&order=asc&order-by=id" class="stat-box stat-box-first">
                <div class="stat-large">$<?php echo number_format(@$stats['today'], 2); ?></div>
                <div>Today's Orders (<?php echo number_format(@$stats['today_count']); ?>)</div>
            </a>
            <a href="order?date_from=<?php echo $week_to_date; ?>&order=asc&order-by=id" class="stat-box stat-box-second">
                <div class="stat-large">$<?php echo number_format(@$stats['weekly'], 2); ?></div>
                <div>This Week's Orders (<?php echo number_format(@$stats['weekly_count']); ?>)</div>
            </a>
            <a href="order?date_from=<?php echo $month_to_date; ?>&order=asc&order-by=id" class="stat-box stat-box-third">
                <div class="stat-large">$<?php echo number_format(@$stats['monthly'], 2); ?></div>
                <div>This Months's Orders (<?php echo number_format(@$stats['monthly_count']); ?>)</div>
            </a>
            <a href="order?date_from=<?php echo $year_to_date; ?>&order=asc&order-by=id" class="stat-box stat-box-fourth">
                <div class="stat-large">$<?php echo number_format(@$stats['yearly'], 2); ?></div>
                <div>YTD Orders (<?php echo number_format(@$stats['yearly_count']); ?>)</div>
            </a>
        </div>

        <?php if($this->hasMessage()) echo "<h5>", $this->getMessage(), "</h5>"; else echo "<h5>Dashboard Under Construction</h5>"; ?>

    </section>

</article>