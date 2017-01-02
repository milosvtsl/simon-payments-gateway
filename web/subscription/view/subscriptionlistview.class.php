<?php
namespace Subscription\View;

use Subscription\Model\SubscriptionRow;
use System\Config\DBConfig;
use Merchant\Model\MerchantRow;
use User\Session\SessionManager;
use View\AbstractListView;


class SubscriptionListView extends AbstractListView {

//Need to be able to pull information by batch, day, card #, amount, MID, TID ect.
// TODO batch id

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
				s.id = :exact
				OR s.uid = :exact
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

        $offset = 0;
        // Set up Date conditions
		if(!empty($params['date_from'])) {
			$whereSQL .= "\nAND s.date >= :from";
			$sqlParams['from'] = date("Y-m-d G:00:00", strtotime($params['date_from']) + $offset);
			$statsMessage .= " from " . date("M dS Y G:00", strtotime($params['date_from']) + $offset);
			$export_filename = date("Y-m-d", strtotime($params['date_from']) + $offset) . $export_filename;
		}
		if(!empty($params['date_to'])) {
			$whereSQL .= "\nAND s.date <= :to";
			$sqlParams['to'] = date("Y-m-d G:00:00", strtotime($params['date_to']) + $offset);
			$statsMessage .= " to " . date("M dS Y G:00", strtotime($params['date_to']) + $offset);
			$export_filename = date("Y-m-d", strtotime($params['date_to']) + $offset) . $export_filename;
		}


		// Handle authority
		if(!$SessionUser->hasAuthority('ROLE_ADMIN')) {
			$list = $SessionUser->getMerchantList() ?: array(0);
			$whereSQL .= "\nAND oi.merchant_id IN (" . implode(', ', $list) . ")\n";

            if(!$SessionUser->hasAuthority('ROLE_RUN_REPORTS', 'ROLE_SUB_ADMIN')) {
				$this->setMessage(
					"<div class='error'>Authorization required to run reports: ROLE_RUN_REPORTS</div>"
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

        // Limit to status
        if(!empty($params['status'])) {
            $whereSQL .= "\nAND oi.status = :status";
            $sqlParams['status'] = $params['status'];
            $statsMessage .= " by status '" . $params['status'] . "' ";
        }

		$DB = DBConfig::getInstance();

		// Calculate GROUP BY
		$groupSQL = SubscriptionRow::SQL_GROUP_BY;

		// Calculate SUBSCRIPTION BY
		$subscriptionSQL = SubscriptionRow::SQL_ORDER_BY;
		if(!empty($params[self::FIELD_ORDER_BY])) {
			$sortSubscription = strcasecmp($params[self::FIELD_ORDER], 'DESC') === 0 ? 'DESC' : 'ASC';
			$sortField = $params[self::FIELD_ORDER_BY];
			if(substr($sortField, 0, 3) !== 'oi.')
				$sortField = 'oi.' . $sortField;
			if(!in_array($sortField, SubscriptionRow::$SORT_FIELDS))
				throw new \InvalidArgumentException("Invalid subscription-by field");
			$subscriptionSQL = "\nORDER BY {$sortField} {$sortSubscription}";
			$statsMessage .= "\nsorted by field '{$sortField}' in " . strtolower($sortSubscription) . "ending subscription";
		}

		// Calculate LIMIT
		$limitSQL = "\nLIMIT " . $this->getOffset() . ', ' . $this->getLimit();
		if(in_array(strtolower(@$params['action']),
			array('export', 'export-stats', 'export-data')))
			$limitSQL = '';

		// Query Rows
		$mainSQL = SubscriptionRow::SQL_SELECT . $whereSQL . $groupSQL . $subscriptionSQL . $limitSQL;
		$Query = $DB->prepare($mainSQL);
		/** @noinspection PhpMethodParametersCountMismatchInspection */
		$Query->setFetchMode(\PDO::FETCH_CLASS, SubscriptionRow::_CLASS);
		$time = -microtime(true);
		$Query->execute($sqlParams);
		$time += microtime(true);


		$statsMessage = $this->getRowCount() . " subscriptions found in " . sprintf('%0.2f', $time) . ' seconds ' . $statsMessage;
        $statsMessage .= " (GMT " . $offset/(60*60) . ")";

		if(!$this->getMessage())
			$this->setMessage($statsMessage);

		if(in_array(strtolower(@$params['action']),
			array('export', 'export-stats', 'export-data'))) {
			// Render Page
			include ('.export.csv.php');

		} else {
			// Render Page
			include ('.list.php');
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

//	protected function renderHTMLHeadScripts() {
//		echo <<<HEAD
//        <script src="subscription/view/assets/subscription.js"></script>
//HEAD;
//		parent::renderHTMLHeadScripts();
//	}
}

