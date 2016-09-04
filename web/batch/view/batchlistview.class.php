<?php
namespace Batch\View;

use Config\DBConfig;
use Batch\Model\BatchRow;
use Batch\Model\BatchStats;
use Batch\Model\BatchQueryStats;
use Merchant\Model\MerchantRow;
use User\Session\SessionManager;
use View\AbstractView;


class BatchListView extends AbstractView {

//Need to be able to pull information by batch, day, card #, amount, MID, TID ect.
// TODO batch id

	public function renderHTMLBody(Array $params) {
		// Add Breadcrumb links
		$this->getTheme()->addCrumbLink($_SERVER['REQUEST_URI'], "Batchs");

		// Render Header
		$this->getTheme()->renderHTMLBodyHeader();


		$sqlParams = array();
		$whereSQL = "WHERE 1";

		if(!empty($params['search'])) {
			$whereSQL .= "\nAND
			(
				b.uid = :exact
				OR b.id = :exact

				OR b.batch_id = :exact
				OR b.batch_status = :exact

				OR m.uid = :exact
			)
			";
			$sqlParams = array(
				'exact' => $params['search'],
			);
		}

		$statsMessage = '';
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

		$SessionManager = new SessionManager();
		$SessionUser = $SessionManager->getSessionUser();

		if($SessionUser->hasAuthority('ROLE_ADMIN', 'ROLE_POST_CHARGE', 'ROLE_VOID_CHARGE', 'ROLE_RUN_REPORTS', 'ROLE_RETURN_CHARGES')) {
		} else {

			// TODO: merchant login?
			$whereSQL .= "\nAND 0\n";
		}

		// Query Statistics
		/** @var BatchQueryStats $Stats */

		$DB = DBConfig::getInstance();
		$countSQL = BatchQueryStats::SQL_SELECT . $whereSQL;
		$Query = $DB->prepare($countSQL);
		$Query->execute($sqlParams);
		/** @noinspection PhpMethodParametersCountMismatchInspection */
		$Query->setFetchMode(\PDO::FETCH_CLASS, BatchQueryStats::_CLASS);
		$Stats = $Query->fetch();
		$Stats->setMessage($statsMessage);
		$Stats->setPage(@$params['page'] ?: 1, @$params['limit'] ?: 50);

		// Query Rows

		$groupSQL = BatchRow::SQL_GROUP_BY;
		$groupSQL .= BatchRow::SQL_ORDER_BY;
		$groupSQL .= "\nLIMIT " . $Stats->getOffset() . ', ' . $Stats->getLimit();

		$mainSQL = BatchRow::SQL_SELECT . $whereSQL . $groupSQL;
		$time = -microtime(true);
		$Query = $DB->prepare($mainSQL);
		/** @noinspection PhpMethodParametersCountMismatchInspection */
		$Query->setFetchMode(\PDO::FETCH_CLASS, BatchRow::_CLASS);
		$Query->execute($sqlParams);
		$time += microtime(true);

		$statsMessage = $Stats->getCount() . " batch entries found in " . sprintf('%0.2f', $time) . ' seconds <br/>' . $statsMessage;
		$Stats->setMessage($statsMessage);

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

