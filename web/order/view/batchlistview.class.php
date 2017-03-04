<?php
namespace Order\View;

use Merchant\Model\MerchantRow;
use Order\Model\BatchQueryStats;
use System\Config\DBConfig;
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

		$limit = @$params['limit'] ?: 50;

		// Set up page parameters
		$this->setPageParameters(@$params['page'] ?: 1, $limit);

		$sqlParams = array();
		$whereSQL = ""; // \n\tWHERE 1";
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
		if(!$SessionUser->hasAuthority('ADMIN')) {
			$whereSQL .= "\nAND oi.merchant_id = :merchant_id";
			$sqlParams['merchant_id'] = $SessionUser->getMerchantID();

            if(!$SessionUser->hasAuthority('RUN_REPORTS', 'SUB_ADMIN')) {
				$SessionManager->setMessage(
					"<div class='error'>Authorization required to run reports: RUN_REPORTS</span>"
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
//            $statsMessage .= " by merchant '" . $Merchant->getName() . "' ";
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

        $limitStatsSQL = "\nLIMIT " . $this->getOffset() . ', ' . $this->getLimit();
		if(in_array(strtolower(@$params['action']),
			array('export', 'export-stats', 'export-data')))
			$limitStatsSQL = '';

		$statsSQL = BatchQueryStats::SQL_SELECT
			. $whereSQL
			. BatchQueryStats::SQL_GROUP_BY
			. BatchQueryStats::SQL_ORDER_BY
			. $limitStatsSQL;
		$BatchQuery = $DB->prepare($statsSQL);
		$BatchQuery->execute($sqlParams);
		/** @noinspection PhpMethodParametersCountMismatchInspection */
		$BatchQuery->setFetchMode(\PDO::FETCH_CLASS, BatchQueryStats::_CLASS);


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
				foreach ($BatchQuery as $Batch) {
					/** @var \Order\Model\OrderQueryStats $Batch */
					echo "\n\"" . $Batch->getGroupSpan(),
						'", ' . $Batch->getCount(),
						', $' . $Batch->getTotal(),
						', $' . $Batch->getSettledTotal(),
						', $' . $Batch->getVoidTotal(),
						', $' . $Batch->getReturnTotal(),
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
				<?php if($SessionManager->hasMessage()) echo "<h5>", $SessionManager->popMessage(), "</h5>"; ?>
				<form name="form-order-search" class="themed">
					<fieldset class="search-fields" style="display: inline-block;">
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
                            <?php if($SessionUser->hasAuthority('ADMIN')) { ?>
							<tr>
								<td class="name">Limit</td>
								<td>
									<select name="merchant_id" style="min-width: 20.5em;" >
										<option value="">By Merchant</option>
                                        <?php
                                        $MerchantQuery = MerchantRow::queryAll();
										foreach($MerchantQuery as $Merchant)
											/** @var \Merchant\Model\MerchantRow $Merchant */
											echo "\n\t\t\t\t\t\t\t<option value='", $Merchant->getID(), "' ",
											($Merchant->getID() == @$_GET['merchant_id'] ? 'selected="selected" ' : ''),
											"'>", $Merchant->getName(), "</option>";
										?>
									</select>
								</td>
							</tr>
                            <?php } ?>
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

					<br />

					<fieldset style="">
						<div class="legend">Batch Report</div>
						<table class="table-stats themed small striped-rows">
							<tr>
								<th>Batch ID</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Merchant</th>
							</tr>
							<?php
							$odd = false;
							foreach($BatchQuery as $Batch) {
                                /** @var BatchQueryStats $Batch */
								$report_url = $action_url . '&date_from=' . $Batch->getStartDate() . '&date_to=' . $Batch->getEndDate()
								/** @var \Order\Model\OrderQueryStats $Stats */
								?>
								<tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
									<td><a href="order/batch.php?id=<?php echo $Batch->getBatchID(); ?>"><?php echo $Batch->getBatchID(); ?></a></td>
                                    <td><?php echo date('Y-m-d', strtotime($Batch->getStartDate())); ?></td>
                                    <td>$<?php echo number_format($Batch->getAmount(), 2), ' (', $Batch->getCount(), ')'; ?></td>
                                    <td><a href="merchant?id=<?php echo $Batch->getMerchantID(); ?>"><?php echo $Batch->getMerchantShortName(); ?></a></td>
								</tr>
							<?php } ?>

							<tr>
                                <td colspan="8" class="pagination">
                                    <span style=""><?php $this->printPagination('order/batch.php?'); ?></span>
									<button name="action" type="submit" value="Export-Stats" class="themed" style="float: right;">Export Batch Data (.csv)</button>
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
		$SessionManager = new SessionManager();
		try {
			$SessionManager->setMessage("Unhandled Form Post: " . __CLASS__);
            header("Location: index.php");

		} catch (\Exception $ex) {
			$SessionManager->setMessage($ex->getMessage());
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

