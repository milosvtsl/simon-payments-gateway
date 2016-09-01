<?php
namespace Order\View;

use Config\DBConfig;
use Merchant\Model\MerchantRow;
use Order\Model\OrderRow;
use User\Session\SessionManager;
use View\AbstractView;


class OrderListView extends AbstractView {

//What kind of report and queries are possible?
//Need to be able to pull information by batch, day, card #, amount, MID, TID ect.

	public function renderHTMLBody(Array $params) {
		// Add Breadcrumb links
		$this->getTheme()->addCrumbLink($_SERVER['REQUEST_URI'], "Orders");

		// Render Header
		$this->getTheme()->renderHTMLBodyHeader();

		$page = intval(@$params['page']) ?: 1;
		$limit = intval(@$params['limit']) ?: 50;
		if($limit > 250) $limit = 250;
		$offset = ($page-1) * $limit;

		$sqlParams = array();
		$sql = OrderRow::SQL_SELECT . "WHERE 1";

		if(isset($params['search'])) {
			$sql .= "\nAND
			(
				oi.uid = :exact

				OR oi.amount = :exact
				OR oi.invoice_number = :exact
				OR oi.customer_id = :exact
				OR oi.username = :exact

                OR SUBSTRING(oi.card_number, -4) = :exact

				OR oi.customer_first_name LIKE :startswith
				OR oi.customer_last_name LIKE :startswith

				OR m.uid = :exact
			)
			";
			$sqlParams = array(
				'exact' => $params['search'],
				'startswith' => $params['search'].'%',
				'endswith' => '%'.$params['search'],
			);
		}

		$SessionManager = new SessionManager();
		$SessionUser = $SessionManager->getSessionUser();
		if($SessionUser->hasAuthority('ROLE_ADMIN')) {
		} else if($SessionUser->hasAuthority('ROLE_POST_CHARGE')) {
		} else if($SessionUser->hasAuthority('ROLE_VOID_CHARGE')) {
		} else if($SessionUser->hasAuthority('ROLE_RUN_REPORTS')) {
		} else if($SessionUser->hasAuthority('ROLE_RETURN_CHARGES')) {
		} else {

			// TODO: merchant login?
			$sql .= "\nAND 0\n";
		}

		// $sql .= "\nGROUP BY oi.id ";
		$sql .= "\nORDER BY oi.id DESC";
		$sql .= "\nLIMIT {$offset}, {$limit}";

		$DB = DBConfig::getInstance();
		$OrderQuery = $DB->prepare($sql);
		/** @noinspection PhpMethodParametersCountMismatchInspection */
		$OrderQuery->setFetchMode(\PDO::FETCH_CLASS, 'Order\Model\OrderRow');
		$OrderQuery->execute($sqlParams);


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

