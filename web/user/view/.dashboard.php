    <!-- Bread Crumbs -->
    <aside class="bread-crumbs">
        <a href="/" class="nav_dashboard">Home</a>
        <a href="/" class="nav_dashboard">Welcome</a>
    </aside>

<?php
$button_current = 'dashboard';
include dirname(dirname(__DIR__)) . '/user/view/.dashboard.nav.php';
?>

    <section class="content">


        <?php if($this->hasException()) echo "<h5>", $this->getException()->getMessage(), "</h5>"; ?>

        <h5>Dashboard Under Construction...</h5>

    </section>
