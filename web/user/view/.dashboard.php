<?php
$button_current = 'dashboard';
include '.dashboard.nav.php';
?>

<article class="themed">

    <section class="content">
        <!-- Bread Crumbs -->
        <aside class="bread-crumbs">
            <a href="/" class="nav_dashboard">Home</a>
            <a href="/" class="nav_dashboard">Welcome</a>
        </aside>


        <?php if($this->hasException()) echo "<h5>", $this->getException()->getMessage(), "</h5>"; ?>

        <h5>Dashboard Under Construction...</h5>

    </section>

</article>