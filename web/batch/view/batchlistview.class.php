<?php
namespace Batch\View;

use Config\DBConfig;
use Batch\Model\BatchRow;
use Batch\Model\BatchQueryStats;
use Merchant\Model\MerchantRow;
use User\Session\SessionManager;
use View\AbstractListView;


class BatchListView extends AbstractListView {

//Need to be able to pull information by batch, day, card #, amount, MID, TID ect.
// TODO batch id

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
				b.uid = :exact
				OR b.batch_id = :exact
				OR b.batch_status = :exact

				OR m.uid = :exact
			)
			";
			$sqlParams = array(
				'exact' => $params['search'],
			);
		}

		// Set up Date conditions
		if(!empty($params['date_from'])) {
			$whereSQL .= "\nAND b.date >= :from";
			$sqlParams['from'] = $params['date_from'];
			$statsMessage .= " from " . date("M jS Y", strtotime($params['date_from'])) . ' 00:00:00';
		}
		if(!empty($params['date_to'])) {
			$whereSQL .= "\nAND b.date <= :to";
			$sqlParams['to'] = $params['date_to'];
			$statsMessage .= " to " . date("M jS Y", strtotime($params['date_to'])) . ' 23:59:59';
		}
		if(!empty($params['merchant_id'])) {
			$Merchant = MerchantRow::fetchByID($params['merchant_id']);
			$whereSQL .= "\nAND b.merchant_id = :merchant_id";
			$sqlParams['merchant_id'] = $Merchant->getID();
			$statsMessage .= " by merchant '" . $Merchant->getShortName() . "' ";
		}


		// Handle authority
		$SessionManager = new SessionManager();
		$SessionUser = $SessionManager->getSessionUser();
		if(!$SessionUser->hasAuthority('ROLE_ADMIN', 'ROLE_POST_CHARGE', 'ROLE_VOID_CHARGE', 'ROLE_RUN_REPORTS', 'ROLE_RETURN_CHARGES')) {
			// TODO: merchant login?
			$whereSQL .= "\nAND 0\n";
		}

		// Query Statistics
		$DB = DBConfig::getInstance();
		$countSQL = BatchQueryStats::SQL_SELECT . $whereSQL;
		$StatsQuery = $DB->prepare($countSQL);
		$StatsQuery->execute($sqlParams);
		/** @noinspection PhpMethodParametersCountMismatchInspection */
		$StatsQuery->setFetchMode(\PDO::FETCH_CLASS, BatchQueryStats::_CLASS);
		/** @var BatchQueryStats $Stats */
		$Stats = $StatsQuery->fetch();
		$this->setRowCount($Stats->getCount());
		$this->setMessage($statsMessage);


		// Calculate GROUP BY
		$groupSQL = BatchRow::SQL_GROUP_BY;

		// Calculate ORDER BY
		$orderSQL = BatchRow::SQL_ORDER_BY;
		if(!empty($params[self::FIELD_ORDER_BY])) {
			$sortOrder = strcasecmp($params[self::FIELD_ORDER], 'DESC') === 0 ? 'DESC' : 'ASC';
			$sortField = $params[self::FIELD_ORDER_BY];
			if(!in_array($sortField, BatchRow::$SORT_FIELDS))
				throw new \InvalidArgumentException("Invalid order-by field");
			$orderSQL = "\nORDER BY {$sortField} {$sortOrder}";
			$statsMessage .= "sorted by field '{$sortField}' in " . strtolower($sortOrder) . "ending order";
		}

		// Calculate LIMIT
		$limitSQL = "\nLIMIT " . $this->getOffset() . ', ' . $this->getLimit();

		// Set Query SQL
		$mainSQL = BatchRow::SQL_SELECT . $whereSQL . $groupSQL . $orderSQL . $limitSQL;
		$ListQuery = $DB->prepare($mainSQL);
		/** @noinspection PhpMethodParametersCountMismatchInspection */
		$ListQuery->setFetchMode(\PDO::FETCH_CLASS, BatchRow::_CLASS);
		$time = -microtime(true);
		$ListQuery->execute($sqlParams);
		$time += microtime(true);
		$this->setListQuery($ListQuery);

		$statsMessage = $this->getRowCount() . " batch entries found in " . sprintf('%0.2f', $time) . ' seconds ' . $statsMessage;
		$this->setMessage($statsMessage);

		// Render Page
		include ('.list.php');

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

