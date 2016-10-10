<?php /** @var \User\View\LoginView $this  **/ ?>
    <section id="intro" class="first">

        <!-- Page Navigation -->
        <nav class="page-menu hide-on-print">
            <a href="home?" class="button current">Dashboard <div class="submenu-icon submenu-icon-dashboard"></div></a>
            <a href="user/account.php" class="button">My Account <div class="submenu-icon submenu-icon-view"></div></a>
            <a href="user/logout.php" class="button">Log Out <div class="submenu-icon submenu-icon-logout"></div></a>
        </nav>

        <!-- Bread Crumbs -->
        <aside class="bread-crumbs">
            <a href="/" class="nav_dashboard">Home</a>
            <a href="home" class="nav_dashboard">Welcome</a>
        </aside>

        <?php if($this->hasException()) echo "<h5>", $this->getException()->getMessage(), "</h5>"; ?>

        <h5>Dashboard Under Construction...</h5>

    </section>
