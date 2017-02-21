<?php
namespace Integration\Request\View;

use Integration\Model\IntegrationRow;
use Integration\Request\Model\IntegrationRequestQueryStats;
use Integration\Request\Model\IntegrationRequestRow;
use System\Config\DBConfig;
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
        $mainSQL = IntegrationRequestRow::SQL_SELECT_PARTIAL . $whereSQL . $groupSQL . $orderSQL . $limitSQL;
		$Query = $DB->prepare($mainSQL);
		/** @noinspection PhpMethodParametersCountMismatchInspection */
		$Query->setFetchMode(\PDO::FETCH_CLASS, IntegrationRequestRow::_CLASS);
        $time = -microtime(true);
		$Query->execute($sqlParams);
        $time += microtime(true);

        $statsMessage = $this->getRowCount() . " requests found in " . sprintf('%0.2f', $time) . ' seconds ' . $statsMessage;
        $SessionManager->setMessage($statsMessage);

		// Render Page
        $Theme = $this->getTheme();
        $Theme->addPathURL('integration',                   'Integration');
        $Theme->addPathURL('integration/request',           'Requests');
        $Theme->addPathURL('integration/request/list.php',    'Search');
        $Theme->renderHTMLBodyHeader();
        $Theme->printHTMLMenu('integration-request-list');
        ?>

        <article class="themed">

            <section class="content">

                <?php if($SessionManager->hasMessage()) echo "<h5>", $SessionManager->popMessage(), "</h5>"; ?>

                <form class="form-search themed">
                    <fieldset class="search-fields">
                        <div class="legend">Search</div>
                        <!--                    <legend>Search</legend>-->
                        <table>
                            <tbody>
                            <tr>
                                <td class="name">From</td>
                                <td>
                                    <input type="date" name="date_from" value="<?php echo @$_GET['date_from']; ?>" /> to
                                    <input type="date" name="date_to"   value="<?php echo @$_GET['date_to']; ?>"  />
                                </td>
                            </tr>
                            <tr>
                                <td class="name">Limit</td>
                                <td>
                                    <select name="limit">
                                        <?php
                                        $limit = @$_GET['limit'] ?: 10;
                                        foreach(array(10,25,50,100,250) as $opt)
                                            echo "<option", $limit == $opt ? ' selected="selected"' : '' ,">", $opt, "</option>\n";
                                        ?>
                                    </select>
                                    <select name="type" style="min-width: 11.1em;" >
                                        <option value="">By Any Type</option>
                                        <option value="transaction">Transaction</option>
                                        <option value="merchant">Merchant</option>
                                    </select>
                                    <select name="integration_id" >
                                        <option value="">By Integration</option>
                                        <?php
                                        $IntegrationQuery = IntegrationRow::queryAll();
                                        foreach($IntegrationQuery as $Integration)
                                            /** @var IntegrationRow $Integration */
                                            echo "\n\t\t\t\t\t\t\t<option value='", $Integration->getID(), "' ",
                                            ($Integration->getID() == @$_GET['integration_id'] ? 'selected="selected" ' : ''),
                                            "'>", $Integration->getName(), "</option>";
                                        ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td class="name">Value</td>
                                <td>
                                    <input type="text" name="search" value="<?php echo @$_GET['search']; ?>" placeholder="All Fields" size="33" />

                                    <input type="submit" value="Search" class="themed" />
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </fieldset>
                    <fieldset>
                        <div class="legend">Search Results</div>
                        <!--                    <legend>Search Results</legend>-->
                        <table class="table-results themed small striped-rows" style="width: 100%;">
                            <tr>
                                <th><a href="integration/request?<?php echo $this->getSortURL(IntegrationRequestRow::SORT_BY_ID); ?>">ID</a></th>
                                <th><a href="integration/request?<?php echo $this->getSortURL(IntegrationRequestRow::SORT_BY_INTEGRATION_ID); ?>">Integration</a></th>
                                <th><a href="integration/request?<?php echo $this->getSortURL(IntegrationRequestRow::SORT_BY_TYPE); ?>">Type</a></th>
                                <th><a href="integration/request?<?php echo $this->getSortURL(IntegrationRequestRow::SORT_BY_RESULT); ?>">Result</a></th>
                                <th><a href="integration/request?<?php echo $this->getSortURL(IntegrationRequestRow::SORT_BY_RESPONSE_MESSAGE); ?>">Message</a></th>
                                <th><a href="integration/request?<?php echo $this->getSortURL(IntegrationRequestRow::SORT_BY_RESPONSE_CODE); ?>">Code</a></th>
                                <th><a href="integration/request?<?php echo $this->getSortURL(IntegrationRequestRow::SORT_BY_DATE); ?>">Date</a></th>
                                <th class="hide-on-layout-narrow"><a href="merchant?<?php echo $this->getSortURL(IntegrationRequestRow::SORT_BY_MERCHANT_ID); ?>">Merchant</a></th>
                                <th><a href="order?<?php echo $this->getSortURL(IntegrationRequestRow::SORT_BY_ORDER_ITEM_ID); ?>">Order</a></th>
                                <th class="hide-on-layout-narrow"><a href="transaction?<?php echo $this->getSortURL(IntegrationRequestRow::SORT_BY_TRANSACTION_ID); ?>">Transaction</a></th>
                                <th class="hide-on-layout-narrow"><a href="user?<?php echo $this->getSortURL(IntegrationRequestRow::SORT_BY_USER_ID); ?>">User</a></th>
                                <th class="hide-on-layout-narrow">Duration</th>
                            </tr>
                            <?php
                            /** @var IntegrationRequestRow $Request */
                            $odd = false;
                            foreach($Query as $Request) { ?>
                                <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                    <td><a href='integration/request?id=<?php echo $Request->getID(); ?>'><?php echo $Request->getID(); ?></a></td>
                                    <td><a href='integration?id=<?php echo $Request->getIntegrationID(); ?>'><?php echo $Request->getIntegrationName(); ?></a></td>
                                    <td><?php echo $Request->getIntegrationType(); ?></td>
                                    <td><?php echo $Request->getResult(); ?></td>
                                    <td><?php echo $Request->getResponseMessage(); ?></td>
                                    <td><?php echo $Request->getResponseCode(); ?></td>
                                    <td><?php echo date("M dS Y G:i:s", strtotime($Request->getDate())); ?></td>
                                    <td class="hide-on-layout-narrow"><a href='merchant?id=<?php echo $Request->getMerchantID(); ?>'><?php echo $Request->getMerchantName(); ?></a></td>
                                    <td><a href='order?uid=<?php echo $Request->getOrderItemUID(); ?>'><?php echo $Request->getOrderItemID(); ?></a></td>
                                    <td class="hide-on-layout-narrow"><a href='order?uid=<?php echo $Request->getOrderItemUID(); ?>'><?php echo $Request->getTransactionID(); ?></a></td>
                                    <td class="hide-on-layout-narrow"><a href='user?uid=<?php echo $Request->getUserUID(); ?>'><?php echo $Request->getUserName(); ?></a></td>
                                    <td class="hide-on-layout-narrow"><?php echo round($Request->getDuration(), 3); ?>s</td>
                                </tr>
                            <?php } ?>
                        </table>
                    </fieldset>
                    <fieldset class="pagination">
                        <div class="legend">Page</div>
                        <!--                    <legend>Page</legend>-->
                        <?php $this->printPagination('integration/request?'); ?>
                    </fieldset>
                </form>
            </section>
        </article>

<?php
        $Theme->renderHTMLBodyFooter();
	}

	public function processFormRequest(Array $post) {
        $SessionManager = new SessionManager();
		try {
			$SessionManager->setMessage("Unhandled Form Post: " . __CLASS__);
			header("Location: integration/request");

		} catch (\Exception $ex) {
			$SessionManager->setMessage($ex->getMessage());
			header("Location: login.php");
		}
	}
}

