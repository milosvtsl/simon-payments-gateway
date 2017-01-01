<?php
namespace View\Error;

use Dompdf\Exception;
use User\Model\UserRow;
use User\Session\SessionManager;
use View\AbstractView;


class ErrorView extends AbstractView {

	private $ex;

	/**
	 * @param Exception $ex
	 */
	public function __construct($ex) {
		parent::__construct("Error: " . $ex->getMessage());
		$this->ex = $ex;
	}

	/**
	 * @return Exception
	 */
	public function getException() {
		return $this->ex;
	}

    protected function renderHTMLHeadLinks() {
//        echo "\t\t<link href='view/error/assets/error.css' type='text/css' rel='stylesheet' />\n";
        parent::renderHTMLHeadLinks();
    }

	public function renderHTMLBody(Array $params) {
		// Render Header
		$this->getTheme()->renderHTMLBodyHeader();

		$SessionManager = new SessionManager();
		$SessionUser = $SessionManager->getSessionUser();

		$Exception = $this->getException();
		$this->getTheme()->printHTMLMenu('error');
?>
		<article class="themed">
			<section class="content dashboard-section">
                <div class="error">An unexpected error has occurred
                    <br/>
                    <?php
                    if($SessionUser->hasAuthority('ROLE_DEBUG'))
                        echo '<pre>', $Exception, '</pre>';
                    else
                        echo '<pre>', $Exception->getMessage(), '</pre>';

                    ?>
                    Support has been informed.<br/>
                    Please try this function again soon.
                    <button onclick="window.history.back()" class="themed" style="padding: 1em; float: right;">Go Back</button>
                </div>
			</section>

		</article>
<?php
		// Render footer
		$this->getTheme()->renderHTMLBodyFooter();
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

