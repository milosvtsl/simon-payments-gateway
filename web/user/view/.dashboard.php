<?php
use User\Session\SessionManager;
/** @var $this \View\AbstractView*/
$SessionManager = new SessionManager();
$SessionUser = $SessionManager->getSessionUser();

$AppManager = new \App\AppManager($SessionUser);


$this->getTheme()->printHTMLMenu('dashboard');
?>

<article class="themed">
    <!-- Bread Crumbs -->
    <aside class="bread-crumbs">
        <a href="/" class="nav_home"><?php echo $SessionUser->getFullName(); ?></a>
        <a href="/" class="nav_dashboard">Dashboard</a>
    </aside>

    <section class="content dashboard-section">
        <?php
        $AppManager->renderAppHTMLContent();
        ?>
    </section>
</article>