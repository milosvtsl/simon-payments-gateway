<?php
namespace Order\View;

use System\Config\DBConfig;
use Merchant\Model\MerchantRow;
use Order\Model\OrderRow;
use Order\Model\OrderQueryStats;
use User\Session\SessionManager;
use View\AbstractListView;


class OrderListView extends AbstractListView {

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
		$this->setPageParameters(@$params['page'] ?: 1, @$params['limit'] ?: 25);

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

				OR (oi.amount = :exact AND oi.amount > 0)
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
			$statsMessage .= " from " . date("M dS Y G:00", strtotime($params['date_from']) + $offset);
			$export_filename = date("Y-m-d", strtotime($params['date_from']) + $offset) . $export_filename;
		}
		if(!empty($params['date_to'])) {
			$whereSQL .= "\nAND oi.date <= :to";
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

        // Limit to status
        if(!empty($params['status'])) {
            $whereSQL .= "\nAND oi.status = :status";
            $sqlParams['status'] = $params['status'];
            $statsMessage .= " by status '" . $params['status'] . "' ";
        }

		// Query Statistics
		$DB = DBConfig::getInstance();

		$limitStatsSQL = "\nLIMIT 5";
		if(in_array(strtolower(@$params['action']),
			array('export', 'export-stats', 'export-data')))
			$limitStatsSQL = '';

		$statsSQL = OrderQueryStats::SQL_SELECT . $whereSQL
//			. OrderQueryStats::SQL_ORDER_BY
			. $limitStatsSQL;
		$StatsQuery = $DB->prepare($statsSQL);
		$StatsQuery->execute($sqlParams);
		/** @noinspection PhpMethodParametersCountMismatchInspection */
		$StatsQuery->setFetchMode(\PDO::FETCH_CLASS, OrderQueryStats::_CLASS);
		$Stats = $StatsQuery->fetch();
		$this->setRowCount($Stats->getCount());


//        $DailyStats =

		// Calculate GROUP BY
		$groupSQL = OrderRow::SQL_GROUP_BY;

		// Calculate ORDER BY
		$orderSQL = OrderRow::SQL_ORDER_BY;
        if(empty($params[self::FIELD_ORDER_BY]))
            $params[self::FIELD_ORDER_BY] = 'oi.date';
        if(empty($params[self::FIELD_ORDER]))
            $params[self::FIELD_ORDER] = 'DESC';
        $sortOrder = strcasecmp($params[self::FIELD_ORDER], 'DESC') === 0 ? 'DESC' : 'ASC';
        $sortField = $params[self::FIELD_ORDER_BY];
        if(substr($sortField, 0, 3) !== 'oi.')
            $sortField = 'oi.' . $sortField;
        if(!in_array($sortField, OrderRow::$SORT_FIELDS))
            throw new \InvalidArgumentException("Invalid order-by field");
        $orderSQL = "\nORDER BY {$sortField} {$sortOrder}";
        $statsMessage .= "\nsorted by field '{$sortField}' in " . strtolower($sortOrder) . "ending order";

		// Calculate LIMIT
		$limitSQL = "\nLIMIT " . $this->getOffset() . ', ' . $this->getLimit();
		if(strtolower(substr(@$params['action'], 0, 6)) == 'export')
			$limitSQL = '';

		// Query Rows
		$mainSQL = OrderRow::SQL_SELECT . $whereSQL . $groupSQL . $orderSQL . $limitSQL;
		$Query = $DB->prepare($mainSQL);
		/** @noinspection PhpMethodParametersCountMismatchInspection */
		$Query->setFetchMode(\PDO::FETCH_CLASS, OrderRow::_CLASS);
		$time = -microtime(true);
		$Query->execute($sqlParams);
		$time += microtime(true);

		$statsMessage = $this->getRowCount() . " orders found in " . sprintf('%0.2f', $time) . ' seconds ' . $statsMessage;
        $statsMessage .= " (GMT " . $offset/(60*60) . ")";

		if(!$this->getMessage())
			$this->setMessage($statsMessage);

        $action_url = 'order/list.php?' . http_build_query($_GET);

		if(strtolower(substr(@$params['action'], 0, 6)) == 'export') {
			// Export Data

            if(!$export_filename)
                $export_filename = 'export.csv';
            header("Content-Disposition: attachment; filename=\"$export_filename\"");
            header("Content-Type: application/vnd.ms-excel");

            echo '"Span","Count","Authorized","Settled","Void","Returned","",""';

//            if(in_array(strtolower(@$params['action']), array('export', 'export-stats'))) {
//                foreach ($StatsQuery as $Stats) {
//                    /** @var \Order\Model\OrderQueryStats $Stats */
//                    echo "\n\"" . $Stats->getGroupSpan(),
//                        '", ' . $Stats->getCount(),
//                        ', $' . $Stats->getTotal(),
//                        ', $' . $Stats->getSettledTotal(),
//                        ', $' . $Stats->getVoidTotal(),
//                        ', $' . $Stats->getReturnTotal(),
//                    ',,';
//                }
//            }

            echo "\n\"Total" ,
                '", ' . $Stats->getCount(),
                ', $' . $Stats->getTotal(),
                ', $' . $Stats->getSettledTotal(),
                ', $' . $Stats->getVoidTotal(),
                ', $' . $Stats->getReturnTotal(),
            '';

            echo "\n\n";
            echo "\nUID,Amount,Status,Mode,Date,Invoice,Cust ID,Customer";
            $offset = $SessionUser->getTimeZoneOffset();
            foreach($Query as $Order) {
                /** @var OrderRow $Order */
                echo
                "\n", $Order->getUID(false),
                ', $', $Order->getAmount(),
        //            ", $", $Order->getConvenienceFee() ?: 0,
                ', ', $Order->getStatus(),
                ', ', $Order->getEntryMode(),
                ', ', $Order->getDate(),
                ', ', str_replace(',', ';', $Order->getInvoiceNumber()),
                ', ', str_replace(',', ';', $Order->getCustomerID()),
                ', ', str_replace(',', ';', $Order->getCardHolderFullName()),
                '';
            }
		} else {
			// Render Page

			$Theme = $this->getTheme();
			$Theme->addPathURL('order',             'Transactions');
			$Theme->addPathURL('order/list.php',    'Search');
			$Theme->renderHTMLBodyHeader();
			$Theme->printHTMLMenu('order-list');
			?>
		<article class="themed">

			<section class="content">


				<?php if($this->hasSessionMessage()) echo "<h5>", $this->popSessionMessage(), "</h5>"; ?>

				<form name="form-order-search" class="themed">

					<fieldset class="search-fields">
						<div class="legend">Search</div>
						<table class="themed" style="width: 100%;">
							<tbody>
							<tr>
								<td class="name">From</td>
								<td>
									<input type="date" name="date_from" value="<?php echo @$_GET['date_from']; ?>" /> to
									<input type="date" name="date_to"   value="<?php echo @$_GET['date_to']; ?>"  />
								</td>
							</tr>
							<tr>
								<td class="name">Merchant</td>
								<td>
									<select name="merchant_id" style="min-width: 20.5em;" >
										<option value="">By Merchant</option>
										<?php
										if($SessionUser->hasAuthority('ROLE_ADMIN'))
											$MerchantQuery = MerchantRow::queryAll();
										else
											$MerchantQuery = $SessionUser->queryUserMerchants();
										foreach($MerchantQuery as $Merchant)
											/** @var \Merchant\Model\MerchantRow $Merchant */
											echo "\n\t\t\t\t\t\t\t<option value='", $Merchant->getID(), "' ",
											($Merchant->getID() == @$_GET['merchant_id'] ? 'selected="selected" ' : ''),
											"'>", $Merchant->getShortName(), "</option>";
										?>
									</select>
								</td>
							</tr>
							<tr>
								<td class="name">Search</td>
								<td>
									<input type="text" name="search" value="<?php echo @$_GET['search']; ?>" placeholder="ID, UID, MID, Amount, Card, Name, Invoice ID" size="42" />
                                </td>
                            </tr>
                            <tr>
                                <td class="name">Submit</td>
                                <td>
                                    <input name="action" type="submit" value="Search" class="themed" />
                                    <select name="limit">
                                        <?php
                                        $limit = @$_GET['limit'] ?: 25;
                                        foreach(array(10,25,50,100,250) as $opt)
                                            echo "<option", $limit == $opt ? ' selected="selected"' : '' ,">", $opt, "</option>\n";
                                        ?>
                                    </select>
								</td>
							</tr>
							</tbody>
						</table>
					</fieldset>

					<fieldset>
						<div class="legend">Search Results</div>
						<table class="table-results themed small striped-rows" style="width: 100%;">
							<tr>
								<th><a href="order?<?php echo $this->getSortURL(OrderRow::SORT_BY_ID); ?>">ID</a></th>
								<th><a href="order?<?php echo $this->getSortURL(OrderRow::SORT_BY_DATE); ?>">Date</a></th>
								<th>Customer/ID</th>
								<th><a href="order?<?php echo $this->getSortURL(OrderRow::SORT_BY_INVOICE_NUMBER); ?>">Invoice</a></th>
                                <th>Amount</th>
								<th class="hide-on-layout-narrow">Mode</th>
								<th><a href="order?<?php echo $this->getSortURL(OrderRow::SORT_BY_STATUS); ?>">Status</a></th>
                                <th class="hide-on-layout-narrow">Account</th>
                                <th class="hide-on-layout-narrow">Type</th>
                                <?php if($SessionUser->hasAuthority('ROLE_ADMIN', 'ROLE_SUB_ADMIN')) { ?>
									<th class="hide-on-layout-narrow"><a href="order?<?php echo $this->getSortURL(OrderRow::SORT_BY_MERCHANT_ID); ?>">Merchant</a></th>
								<?php } ?>
							</tr>
							<?php
							/** @var \Order\Model\OrderRow $Order */
							$odd = false;

							// Get Timezone diff
							$offset = $SessionUser->getTimeZoneOffset('now');

							foreach($Query as $Order) { ?>
								<tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
									<td><a href='order?uid=<?php echo $Order->getUID(false); ?>'><?php echo $Order->getID(); ?></a></td>
									<td ><?php echo date("M dS h:i A", strtotime($Order->getDate()) + $offset); ?></td>
									<td style="max-width: 8em;"><?php echo $Order->getCardHolderFullName(), ($Order->getCustomerID() ? '/' . $Order->getCustomerID() : ''); ?></td>
                                    <td style="max-width: 8em;"><?php echo $Order->getInvoiceNumber(); ?></td>
                                    <td style=" font-weight: bold;"><?php echo $Order->getAmount(); ?></td>
									<td class="hide-on-layout-narrow"><?php echo ucfirst($Order->getEntryMode()); ?></td>
									<td><?php echo $Order->getStatus(); ?></td>
                                    <td class="hide-on-layout-narrow"><?php echo substr($Order->getCardNumber(), -8); ?></td>
                                    <td class="hide-on-layout-narrow"><?php echo $Order->getCardType(); ?></td>
									<?php if($SessionUser->hasAuthority('ROLE_ADMIN', 'ROLE_SUB_ADMIN')) { ?>
										<td class="hide-on-layout-narrow"><a href='merchant?id=<?php echo $Order->getMerchantID(); ?>'><?php echo $Order->getMerchantShortName(); ?></a></td>
									<?php } ?>
								</tr>
							<?php } ?>

							<tr>
								<td colspan="10" class="pagination">
									<span style=""><?php $this->printPagination('order?'); ?></span>
									<button name="action" type="submit" value="Export-Data" class="themed" style="float:right;">Export Data (.csv)</button>
								</td>
							</tr>
						</table>
					</fieldset>


                    <fieldset class="inline-block-on-layout-full">
                        <div class="legend">Search Stats</div>
                        <table class="table-stats themed small striped-rows" >
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name"><?php echo @$params['stats_group'] ? @$params['stats_group'] . 'ly' : 'Range'; ?></td>
                                <td><?php echo $Stats->getGroupSpan(); ?></td>
                            </tr>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">Authorized</td>
                                <td><a href="<?php echo $action_url; ?>&status="><?php echo number_format($Stats->getTotal(),2), ' (', $Stats->getTotalCount(), ')'; ?></a></td>
                            </tr>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">Void</td>
                                <td><a href="<?php echo $action_url; ?>&status=Void"><?php echo number_format($Stats->getVoidTotal(),2), ' (', $Stats->getVoidCount(), ')'; ?></a></td>
                            </tr>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name"">Returned</td>
                                <td><a href="<?php echo $action_url; ?>&status=Return"><?php echo number_format($Stats->getReturnTotal(),2), ' (', $Stats->getReturnCount(), ')'; ?></a></td>
                            </tr>
                            <?php if($SessionUser->hasAuthority('ROLE_ADMIN')) { ?>
                                <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                    <td class="name">Conv. Fee</td>
                                    <td><?php echo number_format($Stats->getConvenienceFeeTotal(),2), ' (', $Stats->getConvenienceFeeCount(), ')'; ?></td>
                                </tr>
                            <?php } ?>

                            <tr>
                                <td colspan="6" style="text-align: right">
                                    <span style="font-size: 0.7em; color: grey; float: left;">
                                        <?php if($this->hasMessage()) echo $this->getMessage(); ?>
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </fieldset>

                </form>
			</section>
		</article>
			<?php
			$Theme->renderHTMLBodyFooter();
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

