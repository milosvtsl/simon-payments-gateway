<?php
namespace Merchant\View;

use Merchant\Model\MerchantQueryStats;
use Merchant\Model\MerchantRow;
use System\Config\DBConfig;
use User\Session\SessionManager;
use View\AbstractListView;


class MerchantListView extends AbstractListView {


	/**
	 * @param array $params
	 */
	public function renderHTMLBody(Array $params) {

		// Set up page parameters
		$this->setPageParameters(@$params['page'] ?: 1, @$params['limit'] ?: 10);

		$sqlParams = array();
		$whereSQL = MerchantRow::SQL_WHERE;
		$statsMessage = '';

		// Set up WHERE conditions
		if(!empty($params['search'])) {
			$whereSQL .= "\nAND
			(
				m.id = :exact
				OR m.uid = :exact

				OR m.name LIKE :startswith
				OR m.short_name LIKE :startswith

				OR m.main_email_id LIKE :has
			)
			";
			$sqlParams = array(
				'exact' => $params['search'],
				'startswith' => $params['search'].'%',
				'has' => '%'.$params['search'].'%',
			);
		}

		if(!empty($params['user_id'])) {
			$whereSQL .= "\nAND EXISTS (
				SELECT * FROM user_merchants um WHERE um.id_user = :user_id AND um.id_merchant = m.id
			)";
			$sqlParams[':user_id'] = $params['user_id'];
		}


		// Handle authority
		$SessionManager = new SessionManager();
		$SessionUser = $SessionManager->getSessionUser();
		if(!$SessionUser->hasAuthority('ROLE_ADMIN', 'ROLE_SUB_ADMIN')) {
			$whereSQL .= "\nAND 0\n";
		}

		// Query Statistics
		$DB = DBConfig::getInstance();
		$countSQL = MerchantQueryStats::SQL_SELECT . $whereSQL;
		$StatsQuery = $DB->prepare($countSQL);
		$StatsQuery->execute($sqlParams);
		/** @noinspection PhpMethodParametersCountMismatchInspection */
		$StatsQuery->setFetchMode(\PDO::FETCH_CLASS, MerchantQueryStats::_CLASS);
		/** @var MerchantQueryStats $Stats */
		$Stats = $StatsQuery->fetch();
		$this->setRowCount($Stats->getCount());


		// Calculate GROUP BY
		$groupSQL = MerchantRow::SQL_GROUP_BY;

		// Calculate ORDER BY
		$orderSQL = MerchantRow::SQL_ORDER_BY;
		if(!empty($params[self::FIELD_ORDER_BY])) {
			$sortOrder = strcasecmp($params[self::FIELD_ORDER], 'DESC') === 0 ? 'DESC' : 'ASC';
			$sortField = $params[self::FIELD_ORDER_BY];
			if(!in_array($sortField, MerchantRow::$SORT_FIELDS))
				throw new \InvalidArgumentException("Invalid order-by field");
			$orderSQL = "\nORDER BY {$sortField} {$sortOrder}";
			$statsMessage .= "\nsorted by field '{$sortField}' in " . strtolower($sortOrder) . "ending order";
		}

		// Calculate LIMIT
		$limitSQL = "\nLIMIT " . $this->getOffset() . ', ' . $this->getLimit();

		// Query Rows
		$mainSQL = MerchantRow::SQL_SELECT . $whereSQL . $groupSQL . $orderSQL . $limitSQL;
		$ListQuery = $DB->prepare($mainSQL);
		/** @noinspection PhpMethodParametersCountMismatchInspection */
		$ListQuery->setFetchMode(\PDO::FETCH_CLASS, MerchantRow::_CLASS);
		$time = -microtime(true);
		$ListQuery->execute($sqlParams);
		$time += microtime(true);
		$this->setListQuery($ListQuery);

		$statsMessage = $this->getRowCount() . " merchants found in " . sprintf('%0.2f', $time) . ' seconds <br/>' . $statsMessage;
//		$this->setMessage($statsMessage);

		$Theme = $this->getTheme();
		$Theme->addPathURL('merchant',             'Merchants');
		$Theme->addPathURL('merchant/list.php',    'Search');
		$Theme->renderHTMLBodyHeader();
		$Theme->printHTMLMenu('merchant-list');
?>

		<article class="themed">

			<section class="content">

				<?php if($SessionManager->hasMessage()) echo "<h5>", $SessionManager->popMessage(), "</h5>"; ?>

				<form class="form-search themed">
					<fieldset class="search-fields">
						<div class="legend">Search all Merchants</div>

						<table class="themed" >
							<tbody>
								<tr>
									<td class="name">Merchant</td>
									<td>
										<input type="text" name="search" value="<?php echo @$_GET['search']; ?>" placeholder="Name, ID, UID" />
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
					<br/>

					<fieldset>
						<div class="legend">Search Results</div>
						<table class="table-results themed small striped-rows" style="width: 100%;">
							<tr>
								<th><a href="merchant?<?php echo $this->getSortURL(MerchantRow::SORT_BY_ID); ?>">ID</a></th>
								<th><a href="merchant?<?php echo $this->getSortURL(MerchantRow::SORT_BY_NAME); ?>">Name</a></th>
								<th>URL</th>
								<th>State</th>
								<th>Zip</th>
								<th>Users</th>
							</tr>
							<?php
							/** @var \Merchant\Model\MerchantRow $Merchant */
							$odd = false;
							foreach($this->getListQuery() as $Merchant) { ?>
								<tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
									<td><a href='merchant?id=<?php echo $Merchant->getID(); ?>'><?php echo $Merchant->getID(); ?></a></td>
									<td><a href='merchant?id=<?php echo $Merchant->getID(); ?>'><?php echo $Merchant->getShortName(); ?></a></td>
									<td><a target="_blank" href='<?php echo $Merchant->getURL(); ?>'><?php echo preg_replace('/^https?:\/\//i', '', $Merchant->getURL()); ?></a></td>
									<td><?php echo $Merchant->getRegionCode(); ?></td>
									<td><?php echo $Merchant->getZipCode(); ?></td>
									<td><a href='user?merchant_id=<?php echo $Merchant->getID(); ?>'><?php echo $Merchant->getUserCount(); ?></a></td>
								</tr>
							<?php } ?>
						</table>
					</fieldset>

					<fieldset class="pagination">
						<div class="legend">Page</div>
						<?php $this->printPagination('merchant?'); ?>
						<br/>
						<span style="font-size: 0.7em; color: grey; float: left;">
							<?php echo $statsMessage; ?>
						</span>
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
			header("Location: /");

		} catch (\Exception $ex) {
			$SessionManager->setMessage($ex->getMessage());
			header("Location: login.php");
		}
	}
}

