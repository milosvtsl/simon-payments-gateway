<?php
namespace Integration\Request\View;

use System\Config\DBConfig;
use Integration\Model\IntegrationRow;
use Merchant\Model\MerchantRow;
use Integration\Request\Model\IntegrationRequestRow;
use Integration\Request\Model\IntegrationRequestQueryStats;
use User\Session\SessionManager;
use View\AbstractListView;


class IntegrationRequestListView extends AbstractListView {
    const VIEW_PATH = 'integrations';

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
				ir.request LIKE :has
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
            $statsMessage .= " from " . date("M dS Y", strtotime($params['date_from']));
        }
        if(!empty($params['date_to'])) {
            $whereSQL .= "\nAND oi.date <= :to";
            $sqlParams['to'] = date("Y-m-d 23:59:59", strtotime($params['date_to']));
            $statsMessage .= " to " . date("M dS Y", strtotime($params['date_to']));
        }

        if(!empty($params['integration_id'])) {
            $Integration = IntegrationRow::fetchByID($params['integration_id']);
            $whereSQL .= "\nAND ir.integration_id = :integration_id";
            $sqlParams['integration_id'] = $params['integration_id'];
            $statsMessage .= " by integration '" . $Integration->getName() . "' ";
        }
        if(!empty($params['type'])) {
            $whereSQL .= "\nAND ir.type = :type";
            $sqlParams['type'] = $params['type'];
            $statsMessage .= " by type '" . $params['type'] . "' ";
        }
        if(!empty($params['type_id'])) {
            $whereSQL .= "\nAND ir.type_id = :type_id";
            $sqlParams['type_id'] = $params['type_id'];
            $statsMessage .= " by type ID '" . $params['type_id'] . "' ";
        }



        // Handle authority
        $SessionManager = new SessionManager();
        $SessionUser = $SessionManager->getSessionUser();
        if(!$SessionUser->hasAuthority('ROLE_ADMIN')) {
            $whereSQL .= "\nAND 0\n";
        }

        // Get Database Instance
        $DB = DBConfig::getInstance();

        // Fetch Stats
        $countSQL = IntegrationRequestQueryStats::SQL_SELECT . $whereSQL;
        $Query = $DB->prepare($countSQL);
        $Query->execute($sqlParams);
		/** @noinspection PhpMethodParametersCountMismatchInspection */
        $Query->setFetchMode(\PDO::FETCH_CLASS, IntegrationRequestQueryStats::_CLASS);
        /** @var IntegrationRequestQueryStats $Stats */
        $Stats = $Query->fetch();
        $this->setRowCount($Stats->getCount());

        // Calculate GROUP BY
        $groupSQL = IntegrationRequestRow::SQL_GROUP_BY;


        // Calculate ORDER BY
        $orderSQL = IntegrationRequestRow::SQL_ORDER_BY;
        if(!empty($params[self::FIELD_ORDER_BY])) {
            $sortOrder = strcasecmp($params[self::FIELD_ORDER], 'DESC') === 0 ? 'DESC' : 'ASC';
            $sortField = $params[self::FIELD_ORDER_BY];
            if(!in_array($sortField, IntegrationRequestRow::$SORT_FIELDS))
                throw new \InvalidArgumentException("Invalid order-by field");
            $orderSQL = "\nORDER BY {$sortField} {$sortOrder}";
            $statsMessage .= "\nsorted by field '{$sortField}' in " . strtolower($sortOrder) . "ending order";
        }

        // Calculate LIMIT
        $limitSQL = "\nLIMIT " . $this->getOffset() . ', ' . $this->getLimit();

        // Query Rows
        $mainSQL = IntegrationRequestRow::SQL_SELECT . $whereSQL . $groupSQL . $orderSQL . $limitSQL;
		$Query = $DB->prepare($mainSQL);
		/** @noinspection PhpMethodParametersCountMismatchInspection */
		$Query->setFetchMode(\PDO::FETCH_CLASS, IntegrationRequestRow::_CLASS);
        $time = -microtime(true);
		$Query->execute($sqlParams);
        $time += microtime(true);

        $statsMessage = $this->getRowCount() . " requests found in " . sprintf('%0.2f', $time) . ' seconds ' . $statsMessage;
        $this->setMessage($statsMessage);

		// Render Page
		include ('.list.php');
	}

	public function processFormRequest(Array $post) {
		try {
			$this->setSessionMessage("Unhandled Form Post");
			header("Location: /integration/request");

		} catch (\Exception $ex) {
			$this->setSessionMessage($ex->getMessage());
			header("Location: login.php");
		}
	}
}

