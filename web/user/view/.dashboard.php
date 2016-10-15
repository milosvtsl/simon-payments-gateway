<?php
$button_current = 'dashboard';
include '.dashboard.nav.php';
?>

    <!-- Bread Crumbs -->
    <aside class="bread-crumbs">
        <a href="/" class="nav_dashboard">Home</a>
        <a href="home" class="nav_dashboard">Welcome</a>
    </aside>

    <section class="content">


        <?php if($this->hasException()) echo "<h5>", $this->getException()->getMessage(), "</h5>"; ?>

        <h5>Dashboard Under Construction...</h5>

    </section>
