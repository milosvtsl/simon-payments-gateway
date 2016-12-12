<?php
use Merchant\Model\MerchantRow;
use User\Session\SessionManager;
use Order\Model\OrderRow;
/** @var $this \View\Error\ErrorView */
$SessionManager = new SessionManager();
$SessionUser = $SessionManager->getSessionUser();

$Exception = $this->getException();
$this->getTheme()->printHTMLMenu('error');
?>


    <article class="themed">

        <section class="content dashboard-section">

            <div style="text-align: center;">
                <div class="error" style="white-space: pre-wrap; padding: 1em; display: inline-block; text-align: left;">An unexpected error has occurred:
<?php echo $Exception->getMessage(); ?>


Support has been informed.
Please try this function again soon.
<button onclick="window.history.back()" class="themed" style="padding: 1em; margin-top: 1em; float: right;">Go Back</button></div>
            </div>
        </section>

    </article>