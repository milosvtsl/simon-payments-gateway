<?php
namespace View\Error;

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
		// Render Header
		$this->getTheme()->renderHTMLBodyHeader();

		// Render Page
		include ('.error.php');

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

