<?php
namespace View\Home;

use View\AbstractView;


class HomeView extends AbstractView {

//    protected function renderHTMLHeadLinks() {
//        echo "\t\t<link href='assets/css/main.css' type='text/css' rel='stylesheet' />\n";
//        parent::renderHTMLHeadLinks();
//    }

	public function renderHTMLBody(Array $params)
	{
        // Add Breadcrumb links
        $this->getTheme()->addCrumbLink($_SERVER['REQUEST_URI'], "Home");

		// Render Header
		$this->getTheme()->renderHTMLBodyHeader();

		// Render Page
		include ('.welcome.php');

		// Render footer
		$this->getTheme()->renderHTMLBodyFooter();
	}

	protected function processRequest(Array $post) {
		// Render on POST
		$this->renderHTML();
	}
}

