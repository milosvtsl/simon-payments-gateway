<?php
namespace User\View;

use Config\DBConfig;
use User\Model\UserQueryStats;
use User\Model\UserRow;
use User\Session\SessionManager;
use View\AbstractView;


class UserListView extends AbstractView {


	/**
	 * @param array $params
	 */
	public function renderHTMLBody(Array $params) {
		// Add Breadcrumb links
		$this->getTheme()->addCrumbLink($_SERVER['REQUEST_URI'], "Users");

		// Render Header
		$this->getTheme()->renderHTMLBodyHeader();

		$sqlParams = array();
		$whereSQL = "WHERE 1";

		if(!empty($params['search'])) {
			$whereSQL .= "\nAND
			(
				u.id = :exact
				OR u.uid = :exact

				OR u.username LIKE :startswith
				OR u.fname LIKE :startswith
				OR u.lname LIKE :startswith
				OR u.email LIKE :has
			)
			";
			$sqlParams = array(
				'exact' => $params['search'],
				'startswith' => $params['search'].'%',
				'has' => '%'.$params['search'].'%',
			);
		}

		$statsMessage = '';
		if(!empty($params['date_from'])) {
			$whereSQL .= "\nAND u.date >= :from";
			$sqlParams['from'] = $params['date_from'];
			$statsMessage .= " from " . date("M jS Y G:i:s", strtotime($params['date_from']));
		}
		if(!empty($params['date_to'])) {
			$whereSQL .= "\nAND u.date <= :to";
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

		// Query Statistics
		/** @var UserQueryStats $Stats */

		$DB = DBConfig::getInstance();
		// Fetch Stats
		$countSQL = UserQueryStats::SQL_SELECT . $whereSQL;
		$Query = $DB->prepare($countSQL);
		$Query->execute($sqlParams);
		/** @noinspection PhpMethodParametersCountMismatchInspection */
		$Query->setFetchMode(\PDO::FETCH_CLASS, UserQueryStats::_CLASS);
		$Stats = $Query->fetch();
		unset ($Query);
		$Stats->setMessage($statsMessage);
		$Stats->setPage(@$params['page'] ?: 1, @$params['limit'] ?: 50);

		// Query Rows

		// $groupSQL = "\nGROUP BY u.id ";
		$groupSQL = UserRow::SQL_GROUP_BY;
		$groupSQL .= UserRow::SQL_ORDER_BY;
		$groupSQL .= "\nLIMIT " . $Stats->getOffset() . ', ' . $Stats->getLimit();

		$mainSQL = UserRow::SQL_SELECT . $whereSQL . $groupSQL;
		$time = -microtime(true);
		$Query = $DB->prepare($mainSQL);
		/** @noinspection PhpMethodParametersCountMismatchInspection */
		$Query->setFetchMode(\PDO::FETCH_CLASS, UserRow::_CLASS);
		$Query->execute($sqlParams);
		$time += microtime(true);

		$statsMessage = $Stats->getCount() . " users found in " . sprintf('%0.2f', $time) . ' seconds <br/>' . $statsMessage;
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

