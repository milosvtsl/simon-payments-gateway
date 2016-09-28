<?php
/**
 * @var \Transaction\View\TransactionView $this
 * @var PDOStatement $TransactionQuery
 **/
$Transaction = $this->getTransaction();
$odd = false;
$action_url = 'transaction?id=' . $Transaction->getID() . '&action=';
?>

    <!-- Page Navigation -->
    <nav class="page-menu">
        <a href="transaction?" class="button">Transactions</a>
        <a href="order?" class="button">Orders</a>
        <a href="transaction/charge.php?" class="button">Charge</a>
        <a href="<?php echo $action_url; ?>view" class="button">View #<?php echo $Transaction->getID(); ?></a>
        <a href="<?php echo $action_url; ?>edit" class="button current">Edit #<?php echo $Transaction->getID(); ?></a>
    </nav>

    <!-- Bread Crumbs -->
    <aside class="bread-crumbs">
        <a href="home" class="nav_home">Home</a>
        <a href="transaction" class="nav_transaction">Transactions</a>
        <a href="<?php echo $action_url; ?>view" class="nav_transaction_view">#<?php echo $Transaction->getID(); ?></a>
        <a href="<?php echo $action_url; ?>edit" class="nav_transaction_edit">Edit</a>
    </aside>

    <section class="content">
        <?php if($this->hasException()) echo "<h5>", $this->getException()->getMessage(), "</h5>"; ?>

        <form class="form-view-Transaction themed" method="POST">
            <fieldset>
                <legend>Edit Transaction Fields</legend>
                <table class="table-transaction-info themed">
                    <tr>
                        <th>Field</th>
                        <th>Value</th>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>ID</td>
                        <td><?php echo $Transaction->getID(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>UID</td>
                        <td><?php echo $Transaction->getUID(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Update</td>
                        <td><input type="submit" value="Update" /></td>
                    </tr>
                </table>
            </fieldset>
        </form>
    </section>