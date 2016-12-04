<?php
namespace Order\View;

use System\Config\DBConfig;
use Merchant\Model\MerchantRow;
use Order\Model\OrderRow;
use Order\Model\BatchQueryStats;
use User\Session\SessionManager;
use View\AbstractListView;


class BatchListView extends AbstractListView {

	public function renderHTML($params=null) {
		if(in_array(strtolower(@$params['action']), array('export', 'export-stats', 'export-data'))) {
			$this->renderHTMLBody($params);
			return;
		}
		parent::renderHTML($params);
	}

	/**
	 * @param array $params
     */
	public function renderHTMLBody(Array $params) {
        $SessionManager = new SessionManager();
        $SessionUser = $SessionManager->getSessionUser();

		// Set up page parameters
		$this->setPageParameters(@$params['page'] ?: 1, @$params['limit'] ?: 10);

		$sqlParams = array();
		$whereSQL = "WHERE 1";
		$statsMessage = '';

		$action = strtolower(@$params['action'] ?: 'view');
		$export_filename = $action . '.csv';

		// Set up WHERE conditions
		if(!empty($params['search'])) {
			$whereSQL .= "\nAND
			(
				oi.id = :exact
				OR oi.uid = :exact

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

        // Get Timezone diff
        $offset = $SessionUser->getTimeZoneOffset('now');
        $offset = 0;

        // Set up Date conditions
		if(!empty($params['date_from'])) {
			$whereSQL .= "\nAND oi.date >= :from";
			$sqlParams['from'] = date("Y-m-d G:00:00", strtotime($params['date_from']) + $offset);
			$statsMessage .= " from " . date("M jS Y G:00", strtotime($params['date_from']) + $offset);
			$export_filename = date("Y-m-d", strtotime($params['date_from']) + $offset) . $export_filename;
		}
		if(!empty($params['date_to'])) {
			$whereSQL .= "\nAND oi.date <= :to";
			$sqlParams['to'] = date("Y-m-d G:00:00", strtotime($params['date_to']) + $offset);
			$statsMessage .= " to " . date("M jS Y G:00", strtotime($params['date_to']) + $offset);
			$export_filename = date("Y-m-d", strtotime($params['date_to']) + $offset) . $export_filename;
		}


		// Handle authority
		if(!$SessionUser->hasAuthority('ROLE_ADMIN')) {
			$list = $SessionUser->getMerchantList() ?: array(0);
			$whereSQL .= "\nAND oi.merchant_id IN (" . implode(', ', $list) . ")\n";

            if(!$SessionUser->hasAuthority('ROLE_RUN_REPORTS', 'ROLE_SUB_ADMIN')) {
				$this->setMessage(
					"<span class='error'>Authorization required to run reports: ROLE_RUN_REPORTS</span>"
				);
				$whereSQL .= "\nAND 0=1";
			}
		}

        // Limit to merchant
        if(!empty($params['merchant_id'])) {
            $Merchant = MerchantRow::fetchByID($params['merchant_id']);
            $whereSQL .= "\nAND oi.merchant_id = :merchant_id";
            $sqlParams['merchant_id'] = $Merchant->getID();
			$export_filename = $Merchant->getShortName() . $export_filename;
//            $statsMessage .= " by merchant '" . $Merchant->getShortName() . "' ";
        }

		// Query Statistics
		$DB = DBConfig::getInstance();


		$statsSQL = BatchQueryStats::SQL_SELECT . $whereSQL;
		$StatsQuery = $DB->prepare($statsSQL);
		$StatsQuery->execute($sqlParams);
		/** @noinspection PhpMethodParametersCountMismatchInspection */
		$StatsQuery->setFetchMode(\PDO::FETCH_CLASS, BatchQueryStats::_CLASS);
		/** @var BatchQueryStats $Stats */
		$Stats = $StatsQuery->fetch();
		$this->setRowCount($Stats->getCount());

		$groupByStatsSQL = BatchQueryStats::SQL_GROUP_BY;
		$limitStatsSQL = "\nLIMIT 5";
		if(in_array(strtolower(@$params['action']),
			array('export', 'export-stats', 'export-data')))
			$limitStatsSQL = '';

		$statsSQL = BatchQueryStats::SQL_SELECT . $whereSQL
			. $groupByStatsSQL
			. BatchQueryStats::SQL_ORDER_BY
			. $limitStatsSQL;
		$ReportQuery = $DB->prepare($statsSQL);
		$ReportQuery->execute($sqlParams);
		/** @noinspection PhpMethodParametersCountMismatchInspection */
		$ReportQuery->setFetchMode(\PDO::FETCH_CLASS, BatchQueryStats::_CLASS);


		if(in_array(strtolower(@$params['action']),
			array('export', 'export-stats', 'export-data'))) {
			// Render Page
			include ('.batch.export.csv.php');

		} else {
			// Render Page
			include ('.batch.php');
		}

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

	protected function renderHTMLHeadScripts() {
		echo <<<HEAD
        <script src="order/view/assets/order.js"></script>
HEAD;
		parent::renderHTMLHeadScripts();
	}
}

