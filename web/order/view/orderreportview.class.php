<?php
namespace Order\View;

use System\Config\DBConfig;
use Merchant\Model\MerchantRow;
use Order\Model\OrderRow;
use Order\Model\OrderQueryStats;
use User\Session\SessionManager;
use View\AbstractListView;

class OrderReportView extends AbstractListView {

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


		// TODO decide date range

		$limitStatsSQL = "\nLIMIT 12";
		if(in_array(strtolower(@$params['action']),
			array('export', 'export-stats', 'export-data')))
			$limitStatsSQL = '';
		switch(@$params['stats_group']) {
			default:
				$params['stats_group'] = 'Day';
			case 'Day': $groupByStatsSQL = "\n\tGROUP BY DATE_FORMAT(oi.date, '%Y%m%d')"; break;
			case 'Week': $groupByStatsSQL = "\n\tGROUP BY DATE_FORMAT(oi.date, '%Y%u')"; break;
			case 'Month': $groupByStatsSQL = "\n\tGROUP BY DATE_FORMAT(oi.date, '%Y%m')"; break;
			case 'Year': $groupByStatsSQL = "\n\tGROUP BY DATE_FORMAT(oi.date, '%Y')"; break;
		}

		$statsSQL = OrderQueryStats::SQL_SELECT . $whereSQL
			. OrderQueryStats::SQL_ORDER_BY
			. $limitStatsSQL;
		$StatsQuery = $DB->prepare($statsSQL);
		$StatsQuery->execute($sqlParams);
		/** @noinspection PhpMethodParametersCountMismatchInspection */
		$StatsQuery->setFetchMode(\PDO::FETCH_CLASS, OrderQueryStats::_CLASS);
		$Stats = $StatsQuery->fetch();
//		$this->setRowCount($Stats->getCount());


		$limitReportSQL = "\nLIMIT " . $this->getOffset() . ', ' . $this->getLimit();
		$reportSQL = OrderQueryStats::SQL_SELECT . $whereSQL
			. $groupByStatsSQL
			. OrderQueryStats::SQL_ORDER_BY
			. $limitReportSQL;
		$ReportQuery = $DB->prepare($reportSQL);
		$ReportQuery->execute($sqlParams);
		/** @noinspection PhpMethodParametersCountMismatchInspection */
		$ReportQuery->setFetchMode(\PDO::FETCH_CLASS, OrderQueryStats::_CLASS);

		$action_url = 'order/list.php?' . http_build_query($_GET);

		if(in_array(strtolower(@$params['action']),
			array('export', 'export-stats', 'export-data'))) {
			// Render Page

			if(!$export_filename)
				$export_filename = 'export.csv';
			header("Content-Disposition: attachment; filename=\"$export_filename\"");
			header("Content-Type: application/vnd.ms-excel");

			$s = ",";
			echo "Span-Group{$s}Count{$s}Authorized{$s}Settled{$s}Void{$s}Returned{$s}{$s}";

			if(in_array(strtolower(@$params['action']), array('export', 'export-stats'))) {
				foreach ($ReportQuery as $Report) {
					/** @var \Order\Model\OrderQueryStats $Report */
					echo "\n\"" . $Report->getGroupSpan(),
						"\"{$s}" . $Report->getCount(),
						"{$s}\$" . $Report->getTotal(),
						"{$s}\$" . $Report->getSettledTotal(),
						"{$s}\$" . $Report->getVoidTotal(),
						"{$s}\$" . $Report->getReturnTotal(),
					"{$s}{$s}";
				}
			}
		} else {
			// Render Page

			$Theme = $this->getTheme();
			$Theme->addPathURL('order',             'Transactions');
			$Theme->addPathURL('order/list.php',    'Search');
			$Theme->renderHTMLBodyHeader();
			$Theme->printHTMLMenu('order-report');
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
								<td class="name">Submit</td>
								<td>
									<select name="stats_group">
										<?php
										$stats_group = @$_GET['stats_group'];
										foreach(array('Day', 'Week', 'Month', 'Year') as $opt)
											echo "<option value='{$opt}' ", $stats_group == $opt ? ' selected="selected"' : '' ,">By ", $opt, "</option>\n";
										?>
									</select>
									<select name="limit">
										<?php
										$limit = @$_GET['limit'] ?: 25;
										foreach(array(10,25,50,100,250) as $opt)
											echo "<option", $limit == $opt ? ' selected="selected"' : '' ,">", $opt, "</option>\n";
										?>
									</select>
									<input name="action" type="submit" value="Generate" class="themed" />
								</td>
							</tr>
							</tbody>
						</table>
					</fieldset>

					<fieldset>
						<div class="legend">Report Results</div>
						<table class="table-stats themed small striped-rows" style='width: 100%;'>
							<tr>
								<th>Range: <?php echo @$params['stats_group'] ? @$params['stats_group'] : 'N/A'; ?></th>
								<th>Authorized</th>
								<th>Void</th>
								<th>Returned</th>
								<?php if($SessionUser->hasAuthority('ROLE_ADMIN')) { ?>
									<th>Conv. Fee</th>
								<?php } ?>
							</tr>
							<?php
							$odd = false;
							foreach($ReportQuery as $Report) {
								/** @var OrderQueryStats $Report */
								$report_url = $action_url . '&date_from=' . $Report->getStartDate() . '&date_to=' . $Report->getEndDate()
								/** @var \Order\Model\OrderQueryStats $Stats */
								?>
								<tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
									<td><a href="<?php echo $report_url; ?>&status="><?php echo $Report->getGroupSpan(); ?></a></td>
									<td><a href="<?php echo $report_url; ?>&status="><?php echo number_format($Report->getTotal(),2), ' (', $Report->getTotalCount(), ')'; ?></a></td>
									<!--                            <td><a href="--><?php //echo $report_url; ?><!--&status=Settled">--><?php //echo number_format($Report->getSettledTotal(),2), ' (', $Report->getSettledCount(), ')'; ?><!--</a></td>-->
									<td><a href="<?php echo $report_url; ?>&status=Void"><?php echo number_format($Report->getVoidTotal(),2), ' (', $Report->getVoidCount(), ')'; ?></a></td>
									<td><a href="<?php echo $report_url; ?>&status=Return"><?php echo number_format($Report->getReturnTotal(),2), ' (', $Report->getReturnCount(), ')'; ?></a></td>
									<?php if($SessionUser->hasAuthority('ROLE_ADMIN')) { ?>
										<td><?php echo number_format($Report->getConvenienceFeeTotal(),2), ' (', $Report->getConvenienceFeeCount(), ')'; ?></td>
									<?php } ?>
								</tr>
							<?php } ?>
							<tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>" style="font-weight: bold;">
								<td><?php echo $Stats->getGroupSpan(); ?></td>
								<td><a href="<?php echo $action_url; ?>&status="><?php echo number_format($Stats->getTotal(),2), ' (', $Stats->getTotalCount(), ')'; ?></a></td>
								<!--                            <td><a href="--><?php //echo $action_url; ?><!--&status=Settled">--><?php //echo number_format($Stats->getSettledTotal(),2), ' (', $Stats->getSettledCount(), ')'; ?><!--</a></td>-->
								<td><a href="<?php echo $action_url; ?>&status=Void"><?php echo number_format($Stats->getVoidTotal(),2), ' (', $Stats->getVoidCount(), ')'; ?></a></td>
								<td><a href="<?php echo $action_url; ?>&status=Return"><?php echo number_format($Stats->getReturnTotal(),2), ' (', $Stats->getReturnCount(), ')'; ?></a></td>
								<?php if($SessionUser->hasAuthority('ROLE_ADMIN')) { ?>
									<td><?php echo number_format($Stats->getConvenienceFeeTotal(),2), ' (', $Stats->getConvenienceFeeCount(), ')'; ?></td>
								<?php } ?>
							</tr>

							<tr>
								<td colspan="10" class="pagination">
									<span style=""><?php $this->printPagination('order/report.php?'); ?></span>
									<button name="action" type="submit" value="Export-Stats" class="themed" style="float: right;">Export Report (.csv)</button>
								</td>
							</tr>

						</table>
					</fieldset>
				</form>
			</section>
		</article>

		<?php $Theme->renderHTMLBodyFooter(); ?>

<?php



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

