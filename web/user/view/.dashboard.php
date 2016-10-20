<?php
use User\Session\SessionManager;
/**
 * @var \View\AbstractListView $this
 **/

$SessionManager = new SessionManager();
$SessionUser = $SessionManager->getSessionUser();

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
                <div class="stat-large">$0.00 (0)</div>
                <div>Today's Orders</div>
            </div>
            <div class="stat-box stat-box-second">
                <div class="stat-large">$0.00 (0)</div>
                <div>This Week's Orders</div>
            </div>
            <div class="stat-box stat-box-third">
                <div class="stat-large">$0.00 (0)</div>
                <div>This Months's Orders</div>
            </div>
            <div class="stat-box stat-box-fourth">
                <div class="stat-large">$0.00 (0)</div>
                <div>YTD Orders</div>
            </div>
        </div>


    </section>

</article>