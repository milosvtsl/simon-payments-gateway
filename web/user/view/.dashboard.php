<?php
use User\Session\SessionManager;
/** @var $this \View\AbstractView*/
$SessionManager = new SessionManager();
$SessionUser = $SessionManager->getSessionUser();

$AppManager = new \App\AppManager($SessionUser);

$Theme = $this->getTheme();

//$Theme->addPathURL('/', 'Dashboard');
$Theme->renderHTMLBodyHeader();
$Theme->printHTMLMenu('dashboard');
?>

        <article class="themed">

            <section class="content dashboard-section">
                <?php
                $AppManager->renderAppHTMLContent();
                ?>
            </section>
        </article>

        <?php $Theme->renderHTMLBodyFooter(); ?>