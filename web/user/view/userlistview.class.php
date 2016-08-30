<?php
namespace User\View;

use Config\DBConfig;
use View\AbstractView;


class UserListView extends AbstractView {



	public function renderHTMLBody(Array $params) {
		// Add Breadcrumb links
		$this->getTheme()->addCrumbLink($_SERVER['REQUEST_URI'], "Users");

		// Render Header
		$this->getTheme()->renderHTMLBodyHeader();

		$page = intval(@$params['page']) ?: 1;
		$limit = intval(@$params['limit']) ?: 50;
		if($limit > 250) $limit = 250;
		$offset = ($page-1) * $limit;

		$sqlParams = array();
		$sql = "SELECT * FROM USER ";

		if(isset($params['search'])) {
			$sql .= "\nWHERE username LIKE ? OR fname LIKE ? OR lname LIKE ? OR email LIKE ? OR uid = ?";
			$sqlParams = array($params['search'].'%', $params['search'].'%', $params['search'].'%', '%'.$params['search'].'%', $params['search']);
		}

		$sql .= "\nORDER BY ID DESC";
		$sql .= "\nLIMIT {$offset}, {$limit}";

		$DB = DBConfig::getInstance();
		$UserQuery = $DB->prepare($sql);
		/** @noinspection PhpMethodParametersCountMismatchInspection */
		$UserQuery->setFetchMode(\PDO::FETCH_CLASS, 'User\UserRow');
		$UserQuery->execute($sqlParams);



		// Render Page
		include ('.list.php');

		// Render footer
		$this->getTheme()->renderHTMLBodyFooter();
	}

	public function processFormRequest(Array $post) {
		try {
			$this->setSessionMessage("Unhandled Form Post");
			header("Location: home.php");

		} catch (\Exception $ex) {
			$this->setSessionMessage($ex->getMessage());
			header("Location: login.php");
		}
	}
}

