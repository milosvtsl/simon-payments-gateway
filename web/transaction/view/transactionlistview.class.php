<?php
namespace Transaction\View;

use Config\DBConfig;
use Merchant\Model\MerchantRow;
use Transaction\Model\TransactionRow;
use Transaction\Model\TransactionQueryStats;
use User\Session\SessionManager;
use View\AbstractView;


class TransactionListView extends AbstractView {


	public function renderHTMLBody(Array $params) {
		// Add Breadcrumb links
		$this->getTheme()->addCrumbLink($_SERVER['REQUEST_URI'], "Transactions");

		// Render Header
		$this->getTheme()->renderHTMLBodyHeader();

		$sqlParams = array();
		$whereSQL = "WHERE 1";

		if(isset($params['search'])) {
			$whereSQL .= "\nAND
			(
				t.uid = :exact
				OR t.transaction_id = :exact

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

        $statsMessage = '';
        if(isset($params['date_from'])) {
            $whereSQL .= "\nAND t.date >= :from";
            $sqlParams['from'] = $params['date_from'];
            $statsMessage .= " from " . date("M jS Y G:i:s", strtotime($params['date_from']));
        }
        if(isset($params['date_to'])) {
            $whereSQL .= "\nAND t.date <= :to";
            $sqlParams['to'] = $params['date_to'];
            $statsMessage .= " to " . date("M jS Y G:i:s", strtotime($params['date_to']));
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


        $DB = DBConfig::getInstance();
        // Fetch Stats
        $countSQL = TransactionQueryStats::SQL_SELECT . $whereSQL;
        $StatsQuery = $DB->prepare($countSQL);
        $StatsQuery->execute($sqlParams);
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $StatsQuery->setFetchMode(\PDO::FETCH_CLASS, 'Transaction\Model\TransactionQueryStats');
        /** @var TransactionQueryStats $Stats */
        $Stats = $StatsQuery->fetch();
        $Stats->setMessage($statsMessage);
        $Stats->setPage(@$params['page'] ?: 1, @$params['limit'] ?: 50);


        $groupSQL = "\nGROUP BY t.id ";
        $groupSQL .= "\nORDER BY t.id DESC";
        $groupSQL .= "\nLIMIT " . $Stats->getOffset() . ', ' . $Stats->getLimit();

        $mainSQL = TransactionRow::SQL_SELECT . $whereSQL . $groupSQL;
        $time = -microtime(true);
		$Query = $DB->prepare($mainSQL);
		/** @noinspection PhpMethodParametersCountMismatchInspection */
		$Query->setFetchMode(\PDO::FETCH_CLASS, 'Transaction\Model\TransactionRow');
		$Query->execute($sqlParams);
        $time += microtime(true);

        $statsMessage = $Stats->getCount() . " transactions found in " . sprintf('%0.2f', $time) . ' seconds <br/>' . $statsMessage;
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

