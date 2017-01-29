<?php
namespace Integration\View;

use Integration\Model\IntegrationRow;
use System\Config\DBConfig;
use User\Session\SessionManager;
use View\AbstractListView;


class IntegrationListView extends AbstractListView {


	/**
	 * @param array $params
	 */
	public function renderHTMLBody(Array $params) {

		$sqlParams = array();
		$whereSQL = IntegrationRow::SQL_WHERE;
		$statsMessage = '';

		// Handle authority
		$SessionManager = new SessionManager();
		$SessionUser = $SessionManager->getSessionUser();
		if(!$SessionUser->hasAuthority('ROLE_ADMIN')) {
			$whereSQL .= "\nAND 0\n";
		}


		// Calculate GROUP BY
		$groupSQL = IntegrationRow::SQL_GROUP_BY;

		// Calculate ORDER BY
		$orderSQL = IntegrationRow::SQL_ORDER_BY;
		if(!empty($params[self::FIELD_ORDER_BY])) {
			$sortOrder = strcasecmp($params[self::FIELD_ORDER], 'DESC') === 0 ? 'DESC' : 'ASC';
			$sortField = $params[self::FIELD_ORDER_BY];
			if(!in_array($sortField, IntegrationRow::$SORT_FIELDS))
				throw new \InvalidArgumentException("Invalid order-by field");
			$orderSQL = "\nORDER BY {$sortField} {$sortOrder}";
			$statsMessage .= "\nsorted by field '{$sortField}' in " . strtolower($sortOrder) . "ending order";
		}

		// Calculate LIMIT
		$limitSQL = "\nLIMIT " . $this->getOffset() . ', ' . $this->getLimit();

		// Query Rows
		$mainSQL = IntegrationRow::SQL_SELECT . $whereSQL . $groupSQL . $orderSQL . $limitSQL;
		$DB = DBConfig::getInstance();
		$ListQuery = $DB->prepare($mainSQL);
		/** @noinspection PhpMethodParametersCountMismatchInspection */
		$ListQuery->setFetchMode(\PDO::FETCH_CLASS, IntegrationRow::_CLASS);
		$time = -microtime(true);
		$ListQuery->execute($sqlParams);
		$time += microtime(true);
		$this->setListQuery($ListQuery);
		$this->setRowCount($ListQuery->rowCount());

		$statsMessage = $this->getRowCount() . " integrations found in " . sprintf('%0.2f', $time) . ' seconds <br/>' . $statsMessage;
		$this->setMessage($statsMessage);

		// Render Page
		$Theme = $this->getTheme();
		$Theme->addPathURL('integration',             'Integration');
		$Theme->addPathURL('integration/list.php',    'API Endpoints');
		$Theme->renderHTMLBodyHeader();
		$Theme->printHTMLMenu('integration-list');
?>


		<article class="themed">
			<section class="content">

				<?php if($this->hasSessionMessage()) echo "<h5>", $this->popSessionMessage(), "</h5>"; ?>

				<form class="form-search themed">
					<fieldset>
						<div class="legend">Integration</div>
						<table class="table-results themed small striped-rows">
							<tr>
								<th><a href="integration?<?php echo $this->getSortURL(IntegrationRow::SORT_BY_ID); ?>">ID</a></th>
								<th><a href="integration?<?php echo $this->getSortURL(IntegrationRow::SORT_BY_NAME); ?>">Name</a></th>
								<th>Success</th>
								<th>Fail</th>
								<th>Notes</th>
							</tr>
							<?php
							/** @var \Integration\Model\IntegrationRow $Integration */
							$odd = false;
							foreach($this->getListQuery() as $Integration) { ?>
								<tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
									<td><a href='integration?id=<?php echo $Integration->getID(); ?>'><?php echo $Integration->getID(); ?></a></td>
									<td><a href='integration?id=<?php echo $Integration->getID(); ?>'><?php echo $Integration->getName(); ?></a></td>
									<td><a href='integration/request?result=success&integration_id=<?php echo $Integration->getID(); ?>'><?php echo $Integration->getSuccessCount(); ?></a></td>
									<td><a href='integration/request?result=fail&integration_id=<?php echo $Integration->getID(); ?>'><?php echo $Integration->getFailCount(); ?></a></td>
									<td><?php echo $Integration->getNotes(); ?></td>

								</tr>
							<?php } ?>
						</table>
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
			header("Location: /");

		} catch (\Exception $ex) {
			$this->setSessionMessage($ex->getMessage());
			header("Location: login.php");
		}
	}
}

