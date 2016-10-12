<?php
/**
 * @var \Batch\View\BatchView $this
 * @var PDOStatement $BatchQuery
 **/
$Batch = $this->getBatch();
$odd = false;
$action_url = 'batch?id=' . $Batch->getID() . '&action=';
?>
    <!-- Page Navigation -->
    <nav class="page-menu hide-on-print">
        <a href="transaction?" class="button">Transactions</a>
        <a href="order?" class="button">Orders</a>
        <a href="batch?" class="button">Batches</a>
        <a href="<?php echo $action_url; ?>view" class="button">View <div class="submenu-icon submenu-icon-view"></div></a>
        <a href="<?php echo $action_url; ?>edit" class="button current">Edit <div class="submenu-icon submenu-icon-edit"></div></a>
    </nav>

    <!-- Bread Crumbs -->
    <aside class="bread-crumbs">
        <a href="home" class="nav_home">Home</a>
        <a href="order" class="nav_batch">Batches</a>
        <a href="<?php echo $action_url; ?>view" class="nav_batch_view">#<?php echo $Batch->getID(); ?></a>
        <a href="<?php echo $action_url; ?>edit" class="nav_batch_edit">Edit</a>
    </aside>

    <section class="content">
        <?php if($this->hasException()) echo "<h5>", $this->getException()->getMessage(), "</h5>"; ?>

        <form class="form-view-Batch themed" method="POST">
            <fieldset style="display: inline-block;">
                <legend>Edit Batch Fields</legend>
                <table class="table-batch-info themed">
                    <tr>
                        <th>Field</th>
                        <th>Value</th>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>ID</td>
                        <td><?php echo $Batch->getID(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>UID</td>
                        <td><?php echo $Batch->getUID(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Update</td>
                        <td><input type="submit" value="Update" /></td>
                    </tr>
                </table>
            </fieldset>
        </form>
    </section>