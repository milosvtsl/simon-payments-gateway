<?php
namespace Order\View;

use Config\DBConfig;
use Merchant\Model\MerchantRow;
use Order\Model\OrderRow;
use Order\Model\OrderStats;
use Order\Model\OrderQueryStats;
use User\Session\SessionManager;
use View\AbstractView;


class OrderListView extends AbstractView {

//Need to be able to pull information by batch, day, card #, amount, MID, TID ect.
// TODO batch id

	public function renderHTMLBody(Array $params) {
		// Add Breadcrumb links
		$this->getTheme()->addCrumbLink($_SERVER['REQUEST_URI'], "Orders");

		// Render Header
		$this->getTheme()->renderHTMLBodyHeader();


		$sqlParams = array();
		$whereSQL = "WHERE 1";

		if(!empty($params['search'])) {
			$whereSQL .= "\nAND
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

		$statsMessage = '';
		if(!empty($params['date_from'])) {
			$whereSQL .= "\nAND oi.date >= :from";
			$sqlParams['from'] = $params['date_from'];
			$statsMessage .= " from " . date("M jS Y G:i:s", strtotime($params['date_from']));
		}
		if(!empty($params['date_to'])) {
			$whereSQL .= "\nAND oi.date <= :to";
			$sqlParams['to'] = $params['date_to'];
			$statsMessage .= " to " . date("M jS Y G:i:s", strtotime($params['date_to']));
		}

		if(!empty($params['merchant_id'])) {
			$Merchant = MerchantRow::fetchByID($params['merchant_id']);
			$whereSQL .= "\nAND oi.merchant_id = :merchant_id";
			$sqlParams['merchant_id'] = $Merchant->getID();
			$statsMessage .= " by merchant '" . $Merchant->getShortName() . "' ";
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
			$whereSQL .= "\nAND 0\n";
		}

		// Query Statistics
		/** @var OrderQueryStats $Stats */

		$DB = DBConfig::getInstance();
		$countSQL = OrderQueryStats::SQL_SELECT . $whereSQL;
		$Query = $DB->prepare($countSQL);
		$Query->execute($sqlParams);
		/** @noinspection PhpMethodParametersCountMismatchInspection */
		$Query->setFetchMode(\PDO::FETCH_CLASS, OrderQueryStats::_CLASS);
		$Stats = $Query->fetch();
		$Stats->setMessage($statsMessage);
		$Stats->setPage(@$params['page'] ?: 1, @$params['limit'] ?: 50);

		// Query Rows

		$groupSQL = OrderRow::SQL_GROUP_BY;
		$groupSQL .= OrderRow::SQL_ORDER_BY;
		$groupSQL = "\nORDER BY oi.id DESC";
		$groupSQL .= "\nLIMIT " . $Stats->getOffset() . ', ' . $Stats->getLimit();

		$mainSQL = OrderRow::SQL_SELECT . $whereSQL . $groupSQL;
		$time = -microtime(true);
		$Query = $DB->prepare($mainSQL);
		/** @noinspection PhpMethodParametersCountMismatchInspection */
		$Query->setFetchMode(\PDO::FETCH_CLASS, OrderRow::_CLASS);
		$Query->execute($sqlParams);
		$time += microtime(true);

		$statsMessage = $Stats->getCount() . " orders found in " . sprintf('%0.2f', $time) . ' seconds <br/>' . $statsMessage;
		$Stats->setMessage($statsMessage);

		// Query Merchant List
		$sql = "SELECT m.id, m.short_name FROM merchant m ORDER BY m.id DESC";
		$DB = DBConfig::getInstance();
		$MerchantQuery = $DB->prepare($sql);
		/** @noinspection PhpMethodParametersCountMismatchInspection */
		$MerchantQuery->setFetchMode(\PDO::FETCH_CLASS, 'Merchant\Model\MerchantRow');
		$MerchantQuery->execute();

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

