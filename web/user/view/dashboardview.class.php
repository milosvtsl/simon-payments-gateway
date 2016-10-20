<?php
namespace User\View;

use View\AbstractView;


class DashboardView extends AbstractView {

    protected function renderHTMLHeadLinks() {
        echo "\t\t<link href='user/view/assets/dashboard.css' type='text/css' rel='stylesheet' />\n";
        parent::renderHTMLHeadLinks();
    }

	public function renderHTMLBody(Array $params) {
        $Theme = $this->getTheme();

        // Add Breadcrumb links
//		$Theme->addCrumbLink('home', "Home");

		// Render Header
        $Theme->renderHTMLBodyHeader();

		// Render Page
		include ('.dashboard.php');

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

