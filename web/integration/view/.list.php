<?php
use \Integration\Model\IntegrationRow;
/**
 * @var \View\AbstractListView $this
 **/

$button_current = 'integration';
include dirname(dirname(__DIR__)) . '\user\view\.dashboard.nav.php';

?>

    <!-- Bread Crumbs -->
    <aside class="bread-crumbs">
        <a href="integration" class="nav_integration">Integrations</a>
        <a href="integration/list.php" class="nav_integration_list">Search</a>
    </aside>

    <section class="content">
        <?php if($this->hasException()) echo "<h5>", $this->getException()->getMessage(), "</h5>"; ?>

        <form class="form-search themed">
            <fieldset style="display: inline-block;">
                <legend>Integrations</legend>
                <table class="table-results themed small">
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
