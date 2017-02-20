<?php
namespace View\Error;

use User\Model\AuthorityRow;
use User\Model\UserRow;
use User\Session\SessionManager;
use View\AbstractView;


class ErrorView extends AbstractView {

    private $ex;

    /**
     * @param \Exception $ex
     */
    public function __construct($ex) {
        parent::__construct("Error: " . $ex->getMessage());
        $this->ex = $ex;
    }

    /**
     * @return \Exception
     */
    public function getException() {
        return $this->ex;
    }

    protected function renderHTMLHeadLinks() {
        echo "\t\t<link href='view/error/assets/error.css' type='text/css' rel='stylesheet' />\n";
        parent::renderHTMLHeadLinks();
    }

    public function renderHTMLBody(Array $params) {
        $Theme = $this->getTheme();

        // Render Header
        $Theme->renderHTMLBodyHeader();

        // Render Page

        /** @var $this \View\Error\ErrorView */
        $SessionManager = new SessionManager();
        $SessionUser = $SessionManager->getSessionUser();

        $Exception = $this->getException();
        $Theme->printHTMLMenu('error');

        $message = $Exception->getMessage();
        if($SessionUser->hasAuthority("ROLE_DEBUG"))
            $message = $Exception;

        ?>
        <article class="themed">

            <section class="content dashboard-section">

                <div style="text-align: center;">
                    <div class="error" style="white-space: pre-wrap; padding: 1em; display: inline-block; text-align: left;">An unexpected error has occurred:
                        <?php echo $message; ?>


                        Support has been informed.
                        Please try this function again soon.
                        <button onclick="window.history.back()" class="themed" style="padding: 1em; margin-top: 1em; float: right;">Go Back</button></div>
                </div>
            </section>

        </article>
        <?php
        // Render footer
        $Theme->renderHTMLBodyFooter();
    }

    public function processFormRequest(Array $post) {
        try {
            $this->setSessionMessage("Unhandled Form Post");
            header("Location: /");

        } catch (\Exception $ex) {
            $this->setSessionMessage($ex->getMessage());
            header("Location: login.php");
        }
    }
}

