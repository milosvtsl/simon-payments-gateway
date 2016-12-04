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

		$limitStatsSQL = "\nLIMIT 5";
		if(in_array(strtolower(@$params['action']),
			array('export', 'export-stats', 'export-data')))
			$limitStatsSQL = '';

		$statsSQL = BatchQueryStats::SQL_SELECT
			. $whereSQL
			. BatchQueryStats::SQL_GROUP_BY
			. BatchQueryStats::SQL_ORDER_BY
			. $limitStatsSQL;
		$ReportQuery = $DB->prepare($statsSQL);
		$ReportQuery->execute($sqlParams);
		/** @noinspection PhpMethodParametersCountMismatchInspection */
		$ReportQuery->setFetchMode(\PDO::FETCH_CLASS, BatchQueryStats::_CLASS);


		if(in_array(strtolower(@$params['action']),
			array('export', 'export-stats', 'export-data'))) {
			// Render Page
//			include ('.batch.export.csv.php');

			if(!$export_filename)
				$export_filename = 'export.csv';
			header("Content-Disposition: attachment; filename=\"$export_filename\"");
			header("Content-Type: application/vnd.ms-excel");

			echo '"Span","Count","Authorized","Settled","Void","Returned","",""';

			if(in_array(strtolower(@$params['action']), array('export', 'export-stats'))) {
				foreach ($ReportQuery as $Report) {
					/** @var \Order\Model\OrderQueryStats $Report */
					echo "\n\"" . $Report->getGroupSpan(),
						'", ' . $Report->getCount(),
						', $' . $Report->getTotal(),
						', $' . $Report->getSettledTotal(),
						', $' . $Report->getVoidTotal(),
						', $' . $Report->getReturnTotal(),
					',,';
				}
			}

		} else {
			// Render Page
//			include ('.batch.php');
			$action_url = 'order/list.php?' . http_build_query($_GET);

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
						<table class="themed">
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
								<td class="name">Report</td>
								<td>
									<select name="stats_group">
										<?php
										$stats_group = @$_GET['stats_group'];
										foreach(array('Day', 'Week', 'Month', 'Year') as $opt)
											echo "<option value='{$opt}' ", $stats_group == $opt ? ' selected="selected"' : '' ,">By ", $opt, "</option>\n";
										?>
									</select>
								</td>
							</tr>
							<tr>
								<td class="name">Value</td>
								<td>
									<input type="text" name="search" value="<?php echo @$_GET['search']; ?>" placeholder="ID, UID, MID, Amount, Card, Name, Invoice ID" size="27" />
									<input name="action" type="submit" value="Search" class="themed" />
								</td>
							</tr>
							</tbody>
						</table>
					</fieldset>

					<fieldset>
						<div class="legend">Search Report</div>
						<table class="table-stats themed small striped-rows">
							<tr>
								<th>Batch ID</th>
								<th>Status</th>
                                <th>Date</th>
                                <th>Count</th>
                                <th>Merchant</th>
							</tr>
							<?php
							$odd = false;
							foreach($ReportQuery as $Report) {
                                /** @var BatchQueryStats $Report */
								$report_url = $action_url . '&date_from=' . $Report->getStartDate() . '&date_to=' . $Report->getEndDate()
								/** @var \Order\Model\OrderQueryStats $Stats */
								?>
								<tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
									<td><a href="order/batch.php?id=<?php echo $Report->getBatchID(); ?>"><?php echo $Report->getBatchID(); ?></a></td>
                                    <td><?php echo $Report->getStatus(); ?></td>
                                    <td><?php echo $Report->getStartDate(); ?></td>
                                    <td><?php echo $Report->getCount(); ?></td>
                                    <td><?php echo $Report->getMerchantShrtName(); ?></td>
								</tr>
							<?php } ?>

							<tr>
								<td colspan="6" style="text-align: right">
                                    <span style="font-size: 0.7em; color: grey; float: left;">
                                        <?php if($this->hasMessage()) echo $this->getMessage(); ?>
                                    </span>
									<button name="action" type="submit" value="Export-Stats" class="themed">Export Reporting (.csv)</button>
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

