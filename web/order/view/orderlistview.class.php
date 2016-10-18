<?php
namespace Order\View;

use Config\DBConfig;
use Merchant\Model\MerchantRow;
use Order\Model\OrderRow;
use Order\Model\OrderQueryStats;
use User\Session\SessionManager;
use View\AbstractListView;


class OrderListView extends AbstractListView {

//Need to be able to pull information by batch, day, card #, amount, MID, TID ect.
// TODO batch id

	/**
	 * @param array $params
     */
	public function renderHTMLBody(Array $params) {
		// Render Header
		$this->getTheme()->renderHTMLBodyHeader();


		// Set up page parameters
		$this->setPageParameters(@$params['page'] ?: 1, @$params['limit'] ?: 50);

		$sqlParams = array();
		$whereSQL = "WHERE 1";
		$statsMessage = '';

		// Set up WHERE conditions
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

		// Set up Date conditions
		if(!empty($params['date_from'])) {
			$whereSQL .= "\nAND oi.date >= :from";
			$sqlParams['from'] = date("Y-m-d 00:00:00", strtotime($params['date_from']));
			$statsMessage .= " from " . date("M jS Y", strtotime($params['date_from']));
		}
		if(!empty($params['date_to'])) {
			$whereSQL .= "\nAND oi.date <= :to";
			$sqlParams['to'] = date("Y-m-d 23:59:59", strtotime($params['date_to']));
			$statsMessage .= " to " . date("M jS Y", strtotime($params['date_to']));
		}


		// Handle authority
		$SessionManager = new SessionManager();
		$SessionUser = $SessionManager->getSessionUser();
		if(!$SessionUser->hasAuthority('ROLE_ADMIN')) {
			$list = $SessionUser->getMerchantList() ?: array(0);
			$whereSQL .= "\nAND oi.merchant_id IN (" . implode(', ', $list) . ")\n";
		}

		if(!empty($params['merchant_id'])) {
			$Merchant = MerchantRow::fetchByID($params['merchant_id']);
			$whereSQL .= "\nAND oi.merchant_id = :merchant_id";
			$sqlParams['merchant_id'] = $Merchant->getID();
			$statsMessage .= " by merchant '" . $Merchant->getShortName() . "' ";
		}

		// Query Statistics
		$DB = DBConfig::getInstance();
		$countSQL = OrderQueryStats::SQL_SELECT . $whereSQL;
		$Query = $DB->prepare($countSQL);
		$Query->execute($sqlParams);
		/** @noinspection PhpMethodParametersCountMismatchInspection */
		$Query->setFetchMode(\PDO::FETCH_CLASS, OrderQueryStats::_CLASS);
		/** @var OrderQueryStats $Stats */
		$Stats = $Query->fetch();
		$this->setRowCount($Stats->getCount());

		// Calculate GROUP BY
		$groupSQL = OrderRow::SQL_GROUP_BY;

		// Calculate ORDER BY
		$orderSQL = OrderRow::SQL_ORDER_BY;
		if(!empty($params[self::FIELD_ORDER_BY])) {
			$sortOrder = strcasecmp($params[self::FIELD_ORDER], 'DESC') === 0 ? 'DESC' : 'ASC';
			$sortField = $params[self::FIELD_ORDER_BY];
			if(!in_array($sortField, OrderRow::$SORT_FIELDS))
				throw new \InvalidArgumentException("Invalid order-by field");
			$orderSQL = "\nORDER BY {$sortField} {$sortOrder}";
			$statsMessage .= "sorted by field '{$sortField}' in " . strtolower($sortOrder) . "ending order";
		}

		// Calculate LIMIT
		$limitSQL = "\nLIMIT " . $this->getOffset() . ', ' . $this->getLimit();

		// Query Rows
		$mainSQL = OrderRow::SQL_SELECT . $whereSQL . $groupSQL . $orderSQL . $limitSQL;
		$Query = $DB->prepare($mainSQL);
		/** @noinspection PhpMethodParametersCountMismatchInspection */
		$Query->setFetchMode(\PDO::FETCH_CLASS, OrderRow::_CLASS);
		$time = -microtime(true);
		$Query->execute($sqlParams);
		$time += microtime(true);

		$statsMessage = $this->getRowCount() . " orders found in " . sprintf('%0.2f', $time) . ' seconds ' . $statsMessage;
		$this->setMessage($statsMessage);

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

