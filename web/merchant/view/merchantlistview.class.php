<?php
namespace Merchant\View;

use Config\DBConfig;
use Merchant\Model\MerchantRow;
use View\AbstractView;


class MerchantListView extends AbstractView {


	public function renderHTMLBody(Array $params) {
		// Add Breadcrumb links
		$this->getTheme()->addCrumbLink($_SERVER['REQUEST_URI'], "Merchants");

		// Render Header
		$this->getTheme()->renderHTMLBodyHeader();

		$page = intval(@$params['page']) ?: 1;
		$limit = intval(@$params['limit']) ?: 50;
		if($limit > 250) $limit = 250;
		$offset = ($page-1) * $limit;

		$sqlParams = array();
		$sql = MerchantRow::SQL_SELECT;

		if(!empty($params['search'])) {
			$sql .= "\nWHERE m.name LIKE ? OR m.short_name LIKE ? OR m.main_email_id LIKE ? OR m.uid = ?";
			$sqlParams = array($params['search'].'%', $params['search'].'%', '%'.$params['search'].'%', $params['search']);
		}

		$sql .= "\nORDER BY ID DESC";
		$sql .= "\nLIMIT {$offset}, {$limit}";

		$DB = DBConfig::getInstance();
		$MerchantQuery = $DB->prepare($sql);
		/** @noinspection PhpMethodParametersCountMismatchInspection */
		$MerchantQuery->setFetchMode(\PDO::FETCH_CLASS, 'Merchant\Model\MerchantRow');
		$MerchantQuery->execute($sqlParams);



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

