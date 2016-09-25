<?php
/**
 * @var \Transaction\View\TransactionView $this
 * @var PDOStatement $TransactionQuery
 **/
$Transaction = $this->getTransaction();
$odd = false;
$action_url = 'transaction?id=' . $Transaction->getID() . '&action=';
?>
    <section class="content">
        <div class="action-fields">
            <a href="transaction?" class="button">Transactions</a>
            <a href="<?php echo $action_url; ?>view" class="button">View</a>
            <a href="<?php echo $action_url; ?>view" class="button current">Edit</a>
<!--            <a href="--><?php //echo $action_url; ?><!--delete" class="button">Delete</a>-->
            <a href="transaction/charge.php?" class="button">Charge</a>
        </div>

        <h1>Edit Transaction #<?php echo $Transaction->getID(); ?></h1>

        <?php if($this->hasException()) { ?>
            <h5><?php echo $this->hasException(); ?></h5>

        <?php } else if ($this->hasSessionMessage()) { ?>
            <h5><?php echo $this->popSessionMessage(); ?></h5>


        <?php } ?>

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