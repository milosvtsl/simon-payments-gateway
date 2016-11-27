<?php
namespace Support\View;

use Support\Model\SupportTicketQueryStats;
use System\Config\DBConfig;
use Support\Model\SupportTicketRow;
use User\Session\SessionManager;
use View\AbstractListView;


class SupportTicketListView extends AbstractListView {
    const VIEW_PATH = 'supports';

	/**
	 * @param array $params
     */
	public function renderHTMLBody(Array $params) {

		$sqlParams = array();
		$whereSQL = "WHERE 1";

        // Set up page parameters
        $this->setPageParameters(@$params['page'] ?: 1, @$params['limit'] ?: 10);

        // Set up WHERE conditions
		if(!empty($params['search'])) {
			$whereSQL .= "\nAND
			(
				st.ticket LIKE :has
			)
			";
			$sqlParams = array(
                'has' => '%'.$params['search'].'%',
			);
		}

        $statsMessage = '';

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

        if(!empty($params['category'])) {
            $whereSQL .= "\nAND st.category LIKE :category";
            $sqlParams['category'] = $params['category'];
            $statsMessage .= " by category '" . $params['category'] . "' ";
        }

        // Handle authority
        $SessionManager = new SessionManager();
        $SessionUser = $SessionManager->getSessionUser();
        if(!$SessionUser->hasAuthority('ROLE_ADMIN')) {
            $whereSQL .= "\nAND 0\n";
            // TODO: merchant tickets
        }

        // Get Database Instance
        $DB = DBConfig::getInstance();

        // Fetch Stats
        $countSQL = SupportTicketQueryStats::SQL_SELECT . $whereSQL;
        $Query = $DB->prepare($countSQL);
        $Query->execute($sqlParams);
		/** @noinspection PhpMethodParametersCountMismatchInspection */
        $Query->setFetchMode(\PDO::FETCH_CLASS, SupportTicketQueryStats::_CLASS);
        /** @var SupportTicketQueryStats $Stats */
        $Stats = $Query->fetch();
        $this->setRowCount($Stats->getCount());

        // Calculate GROUP BY
        $groupSQL = SupportTicketRow::SQL_GROUP_BY;


        // Calculate ORDER BY
        $orderSQL = SupportTicketRow::SQL_ORDER_BY;
        if(!empty($params[self::FIELD_ORDER_BY])) {
            $sortOrder = strcasecmp($params[self::FIELD_ORDER], 'DESC') === 0 ? 'DESC' : 'ASC';
            $sortField = $params[self::FIELD_ORDER_BY];
            if(!in_array($sortField, SupportTicketRow::$SORT_FIELDS))
                throw new \InvalidArgumentException("Invalid order-by field");
            $orderSQL = "\nORDER BY {$sortField} {$sortOrder}";
            $statsMessage .= "\nsorted by field '{$sortField}' in " . strtolower($sortOrder) . "ending order";
        }

        // Calculate LIMIT
        $limitSQL = "\nLIMIT " . $this->getOffset() . ', ' . $this->getLimit();

        // Query Rows
        $mainSQL = SupportTicketRow::SQL_SELECT . $whereSQL . $groupSQL . $orderSQL . $limitSQL;
		$Query = $DB->prepare($mainSQL);
		/** @noinspection PhpMethodParametersCountMismatchInspection */
		$Query->setFetchMode(\PDO::FETCH_CLASS, SupportTicketRow::_CLASS);
        $time = -microtime(true);
		$Query->execute($sqlParams);
        $time += microtime(true);

        $statsMessage = $this->getRowCount() . " tickets found in " . sprintf('%0.2f', $time) . ' seconds ' . $statsMessage;
        $this->setMessage($statsMessage);

		// Render Page
		include ('.list.php');
	}

	public function processFormTicket(Array $post) {
		try {
			$this->setSessionMessage("Unhandled Form Post");
			header("Location: /support/ticket");

		} catch (\Exception $ex) {
			$this->setSessionMessage($ex->getMessage());
			header("Location: login.php");
		}
	}
}

