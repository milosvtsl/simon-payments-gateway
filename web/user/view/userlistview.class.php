<?php
namespace User\View;

use System\Config\DBConfig;
use User\Model\UserQueryStats;
use User\Model\UserRow;
use User\Session\SessionManager;
use View\AbstractListView;


class UserListView extends AbstractListView {


	/**
	 * @param array $params
	 */
	public function renderHTMLBody(Array $params) {
		// Set up page parameters
		$this->setPageParameters(@$params['page'] ?: 1, @$params['limit'] ?: 10);

		$sqlParams = array();
		$whereSQL = "WHERE 1";
		$statsMessage = '';

		// Set up WHERE conditions
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


		// Handle authority
		$SessionUser = SessionManager::get()->getSessionUser();
		if(!$SessionUser->hasAuthority('ROLE_ADMIN')) {
			$whereSQL .= "\nAND u.admin_id = :admin_id\n";
			$sqlParams[':admin_id'] = $SessionUser->getID();
		}

		if(isset($params['merchant_id'])) {
			$whereSQL .= "\nAND u.id = (SELECT um.id_user FROM user_merchants um WHERE um.id_merchant = :id_merchant AND um.id_user = u.id)";
			$sqlParams[':id_merchant'] = $params['merchant_id'];
		}

		// Get Database Instance
		$DB = DBConfig::getInstance();

		// Fetch Stats
		$countSQL = UserQueryStats::SQL_SELECT . $whereSQL;
		$StatsQuery = $DB->prepare($countSQL);
		$StatsQuery->execute($sqlParams);
		/** @noinspection PhpMethodParametersCountMismatchInspection */
		$StatsQuery->setFetchMode(\PDO::FETCH_CLASS, UserQueryStats::_CLASS);
		/** @var UserQueryStats $Stats */
		$Stats = $StatsQuery->fetch();
		$this->setRowCount($Stats->getCount());


		// Calculate GROUP BY
		$groupSQL = UserRow::SQL_GROUP_BY;

		// Calculate ORDER BY
		$orderSQL = UserRow::SQL_ORDER_BY;
		if(!empty($params[self::FIELD_ORDER_BY])) {
			$sortOrder = strcasecmp($params[self::FIELD_ORDER], 'DESC') === 0 ? 'DESC' : 'ASC';
			$sortField = $params[self::FIELD_ORDER_BY];
			if(!in_array($sortField, UserRow::$SORT_FIELDS))
				throw new \InvalidArgumentException("Invalid order-by field");
			$orderSQL = "\nORDER BY {$sortField} {$sortOrder}";
			$statsMessage .= "\nsorted by field '{$sortField}' in " . strtolower($sortOrder) . "ending order";
		}

		// Calculate LIMIT
		$limitSQL = "\nLIMIT " . $this->getOffset() . ', ' . $this->getLimit();

		// Query Rows
		$mainSQL = UserRow::SQL_SELECT . $whereSQL . $groupSQL . $orderSQL . $limitSQL;
		$ListQuery = $DB->prepare($mainSQL);
		/** @noinspection PhpMethodParametersCountMismatchInspection */
		$ListQuery->setFetchMode(\PDO::FETCH_CLASS, UserRow::_CLASS);
		$time = -microtime(true);
		$ListQuery->execute($sqlParams);
		$time += microtime(true);
		$this->setListQuery($ListQuery);

		$statsMessage = $this->getRowCount() . " users found in " . sprintf('%0.2f', $time) . ' seconds <br/>' . $statsMessage;
//		$this->setMessage($statsMessage);

		// Render Page
		$Theme = $this->getTheme();
		$Theme->addPathURL('user',             'Users');
		$Theme->addPathURL('user/list.php',    'Search');
		$Theme->renderHTMLBodyHeader();
		$Theme->printHTMLMenu('user-list');

		$SessionManager = new SessionManager();

?>
<article class="themed">

	<section class="content">

		<?php if($SessionManager->hasMessage()) echo "<h5>", $SessionManager->popMessage(), "</h5>"; ?>

		<form class="form-user-search themed">
			<fieldset class="search-fields">
				<div class="legend">Search</div>
				<table>
					<tr>
						<!--                            <td class="name">Search</td>-->
						<td class="value">
							<input type="text" name="search" value="<?php echo @$_GET['search']; ?>" placeholder="By ID, Name, Email" />
						</td>
						<td class="value">
							<select name="limit">
								<?php
								$limit = @$_GET['limit'] ?: 10;
								foreach(array(10,25,50,100,250) as $opt)
									echo "<option", $limit == $opt ? ' selected="selected"' : '' ,">", $opt, "</option>\n";
								?>
							</select>
						<td class="value"><input type="submit" value="Search" class="themed" /></td>
					</tr>
				</table>
			</fieldset>
			<fieldset>
				<div class="legend">Search Results</div>
				<table class="table-results themed striped-rows small" style="width: 100%;">
					<tr>
						<th><a href="user?<?php echo $this->getSortURL(UserRow::SORT_BY_ID); ?>">ID</a></th>
						<th><a href="user?<?php echo $this->getSortURL(UserRow::SORT_BY_LNAME); ?>">Name</a></th>
						<th><a href="user?<?php echo $this->getSortURL(UserRow::SORT_BY_EMAIL); ?>">Email</a></th>
						<th>Timezone</th>
						<th>Created</th>
						<th>Merchants</th>
					</tr>
					<?php
					/** @var \User\Model\UserRow $User */
					$odd = false;
					foreach($this->getListQuery() as $User) { ?>
						<tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
							<td><a href='user?uid=<?php echo $User->getUID(); ?>'><?php echo $User->getID(); ?></a></td>
							<td><a href='user?uid=<?php echo $User->getUID(); ?>'><?php echo $User->getFullName(); ?></a></td>
							<td><a href='mailto:<?php echo $User->getEmail(); ?>'><?php echo $User->getEmail(); ?></a></td>
							<td><?php echo str_replace('_', '', $User->getTimeZone()); ?></td>
							<td><?php echo $User->getCreateDate() ? date('Y-m-d', strtotime($User->getCreateDate())) : 'N/A'; ?></td>
							<td><a href='merchant/list.php?user_id=<?php echo $User->getID(); ?>'><?php echo $User->getMerchantCount(); ?></a></td>
						</tr>
					<?php } ?>
				</table>
			</fieldset>
			<fieldset class="pagination">
				<div class="legend">Page</div>
				<?php $this->printPagination('user?'); ?>
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
            header("Location: index.php");

		} catch (\Exception $ex) {
			$SessionManager->setMessage($ex->getMessage());
			header("Location: login.php");
		}
	}
}

