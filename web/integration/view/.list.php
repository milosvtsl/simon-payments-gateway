<?php
use \Integration\Model\IntegrationRow;
/**
 * @var \View\AbstractListView $this
 **/?>

    <!-- Page Navigation -->
    <nav class="page-menu hide-on-print">
        <a href="integration?" class="button current">Integrations</a>
        <a href="integration/request?" class="button">Requests</a>
        <a href="merchant?" class="button">Merchants</a>
        <a href="user?" class="button">Users</a>
    </nav>

    <!-- Bread Crumbs -->
    <aside class="bread-crumbs">
        <a href="home" class="nav_home">Home</a>
        <a href="integration" class="nav_integration">Integrations</a>
        <a href="integration/list.php" class="nav_integration_list">Search</a>
    </aside>

    <section class="content">
        <?php if($this->hasException()) echo "<h5>", $this->getException()->getMessage(), "</h5>"; ?>

        <form class="form-search themed">
            <fieldset>
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
