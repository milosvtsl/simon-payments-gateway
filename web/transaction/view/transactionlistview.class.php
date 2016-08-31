<?php
namespace Transaction\View;

use Config\DBConfig;
use Merchant\Model\MerchantRow;
use Transaction\Model\TransactionRow;
use View\AbstractView;


class TransactionListView extends AbstractView {


	public function renderHTMLBody(Array $params) {
		// Add Breadcrumb links
		$this->getTheme()->addCrumbLink($_SERVER['REQUEST_URI'], "Transactions");

		// Render Header
		$this->getTheme()->renderHTMLBodyHeader();

		$page = intval(@$params['page']) ?: 1;
		$limit = intval(@$params['limit']) ?: 50;
		if($limit > 250) $limit = 250;
		$offset = ($page-1) * $limit;

		$sqlParams = array();
		$sql = TransactionRow::SQL_SELECT;

		if(isset($params['search'])) {
			$sql .= "\nWHERE
			t.id = :exact
			OR t.uid = :exact
			OR oi.id = :exact
			OR oi.uid = :exact

			OR t.order_item_id = :exact
			OR invoice_number = :exact
			OR customer_id = :exact
			OR username = :exact
			OR customer_id = :exact

			OR transaction_id LIKE :startswith
			OR customer_first_name LIKE :startswith
			OR customer_last_name LIKE :startswith
			";
			$sqlParams = array(
				'exact' => $params['search'],
				'startswith' => $params['search'].'%',
			);
		}

		$sql .= "\nGROUP BY t.id ";
		$sql .= "\nORDER BY t.id DESC";
		$sql .= "\nLIMIT {$offset}, {$limit}";

		$DB = DBConfig::getInstance();
		$TransactionQuery = $DB->prepare($sql);
		/** @noinspection PhpMethodParametersCountMismatchInspection */
		$TransactionQuery->setFetchMode(\PDO::FETCH_CLASS, 'Transaction\Model\TransactionRow');
		$TransactionQuery->execute($sqlParams);


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

