<?php
namespace User\View;

use App\AppManager;
use User\Session\SessionManager;
use View\AbstractView;


class DashboardView extends AbstractView {

    protected function renderHTMLHeadLinks() {
		$SessionManager = new SessionManager();
		$SessionUser = $SessionManager->getSessionUser();
		$AppManager = new AppManager($SessionUser);
		$AppManager->renderHTMLHeadContent();

		echo "\t\t<link href='user/view/assets/dashboard.css' type='text/css' rel='stylesheet' />\n";
        parent::renderHTMLHeadLinks();
    }

	public function renderHTMLBody(Array $params) {
		// Render Page
		$SessionManager = new SessionManager();
		$SessionUser = $SessionManager->getSessionUser();

		$AppManager = new AppManager($SessionUser);

		$Theme = $this->getTheme();
		$Theme->addPathURL('/', $SessionUser->getFullName());
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
		<?php
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

