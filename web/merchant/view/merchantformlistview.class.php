<?php
namespace Merchant\View;

use System\Config\DBConfig;
use Merchant\Model\MerchantQueryStats;
use Merchant\Model\MerchantFormRow;
use User\Session\SessionManager;
use View\AbstractListView;


class MerchantFormListView extends AbstractListView {


	/**
	 * @param array $params
	 */
	public function renderHTMLBody(Array $params) {

		// Set up page parameters
		$this->setPageParameters(@$params['page'] ?: 1, @$params['limit'] ?: 10);

		$DB = DBConfig::getInstance();

		$sqlParams = array();
		$whereSQL = MerchantFormRow::SQL_WHERE;
		$statsMessage = '';

		// Set up WHERE conditions
		if(!empty($params['search'])) {
			$whereSQL .= "\nAND
			(
				mf.id = :exact
				OR mf.uid = :exact

				OR mf.title LIKE :startswith
			)
			";
			$sqlParams = array(
				'exact' => $params['search'],
				'startswith' => $params['search'].'%',
//				'has' => '%'.$params['search'].'%',
			);
		}


		// Calculate GROUP BY
		$groupSQL = MerchantFormRow::SQL_GROUP_BY;

		// Calculate ORDER BY
		$orderSQL = MerchantFormRow::SQL_ORDER_BY;
		if(!empty($params[self::FIELD_ORDER_BY])) {
			$sortOrder = strcasecmp($params[self::FIELD_ORDER], 'DESC') === 0 ? 'DESC' : 'ASC';
			$sortField = $params[self::FIELD_ORDER_BY];
			if(!in_array($sortField, MerchantFormRow::$SORT_FIELDS))
				throw new \InvalidArgumentException("Invalid order-by field");
			$orderSQL = "\nORDER BY {$sortField} {$sortOrder}";
			$statsMessage .= "\nsorted by field '{$sortField}' in " . strtolower($sortOrder) . "ending order";
		}

		// Calculate LIMIT
		$limitSQL = "\nLIMIT " . $this->getOffset() . ', ' . $this->getLimit();

		// Query Rows
		$mainSQL = MerchantFormRow::SQL_SELECT . $whereSQL . $groupSQL . $orderSQL . $limitSQL;
		$ListQuery = $DB->prepare($mainSQL);
		/** @noinspection PhpMethodParametersCountMismatchInspection */
		$ListQuery->setFetchMode(\PDO::FETCH_CLASS, MerchantFormRow::_CLASS);
		$time = -microtime(true);
		$ListQuery->execute($sqlParams);
		$time += microtime(true);
		$this->setListQuery($ListQuery);

		$statsMessage = $this->getRowCount() . " merchants found in " . sprintf('%0.2f', $time) . ' seconds <br/>' . $statsMessage;
//		$this->setMessage($statsMessage);

		$Theme = $this->getTheme();
		$Theme->addPathURL('merchant',             'Merchants');
		$Theme->addPathURL('merchant/form.php',    'Forms');
		$Theme->renderHTMLBodyHeader();
		$Theme->printHTMLMenu('merchant-form');
?>
		<article class="themed">
			<section class="content">
				<?php if($this->hasSessionMessage()) echo "<h5>", $this->popSessionMessage(), "</h5>"; ?>
				<form class="form-search themed">
					<fieldset class="search-fields">
						<div class="legend">Search Merchant Forms</div>
						<table class="themed" >
							<tbody>
								<tr>
									<td class="name">Search</td>
									<td>
										<input type="text" name="search" value="<?php echo @$_GET['search']; ?>" placeholder="Title, ID, UID" />
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
						<table class="table-results themed small striped-rows">
							<tr>
								<th><a href="merchant/form.php?<?php echo $this->getSortURL(MerchantFormRow::SORT_BY_ID); ?>">ID</a></th>
								<th><a href="merchant/form.php?<?php echo $this->getSortURL(MerchantFormRow::SORT_BY_TITLE); ?>">Name</a></th>
								<th>UID</th>
							</tr>
							<?php
							/** @var \Merchant\Model\MerchantFormRow $Form */
							$odd = false;
							foreach($this->getListQuery() as $Form) { ?>
								<tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
									<td><a href='merchant/form.php?uid=<?php echo $Form->getUID(); ?>'><?php echo $Form->getID(); ?></a></td>
									<td><a href='merchant/form.php?uid=<?php echo $Form->getUID(); ?>'><?php echo $Form->getTitle(); ?></a></td>
									<td><?php echo $Form->getUID(); ?></td>
								</tr>
							<?php } ?>
						</table>
					</fieldset>

					<fieldset class="pagination">
						<div class="legend">Page</div>
						<?php $this->printPagination('merchant/form.php?'); ?>

						<?php if($this->hasMessage()) echo "<h5>", $this->getMessage(), "</h5>"; ?>

					</fieldset>
				</form>
			</section>
		</article>
<?php
		$Theme->renderHTMLBodyFooter();
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

