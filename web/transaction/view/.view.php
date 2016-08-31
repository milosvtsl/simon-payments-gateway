<?php
/**
 * @var \Transaction\View\TransactionView $this
 * @var PDOStatement $TransactionQuery
 **/
$Transaction = $this->getTransaction();
$odd = false;
$action_url = 'transaction?id=' . $Transaction->getID() . '&action=';
?>
    <section class="message">
        <h1>View Transaction</h1>

        <?php if($this->hasException()) { ?>
            <h5><?php echo $this->hasException(); ?></h5>

        <?php } else if ($this->hasSessionMessage()) { ?>
            <h5><?php echo $this->popSessionMessage(); ?></h5>

        <?php } else { ?>
            <h5>View a transaction...</h5>

        <?php } ?>
    </section>

    <section class="content">
        <form class="form-view-transaction themed" onsubmit="return false;">
            <fieldset class="action-fields">
                <legend>Actions</legend>
                <a href="transaction?" class="button">Transaction List</a>
                <a href="<?php echo $action_url; ?>edit" class="button">Edit</a>
                <a href="transaction/charge.php?" class="button">Charge</a>
            </fieldset>
            <fieldset>
                <legend>Transaction Information</legend>
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
                        <td>Transaction</td>
                        <td><?php echo $Transaction->getTransactionID() ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Amount</td>
                        <td><?php echo $Transaction->getAmount(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Invoice</td>
                        <td><?php echo $Transaction->getInvoiceNumber() ?: 'N/A'; ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Customer</td>
                        <td><?php echo $Transaction->getCustomerID() ?: 'N/A' ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Username</td>
                        <td><?php echo $Transaction->getUsername() ?: 'N/A' ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Order ID</td>
                        <td><a href='order?id=<?php echo $Transaction->getOrderID(); ?>'><?php echo $Transaction->getOrderID(); ?></a></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Card Holder</td>
                        <td><?php echo $Transaction->getHolderFullFullName() ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Order Status</td>
                        <td><?php echo $Transaction->getStatus() ?: 'N/A' ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Merchant</td>
                        <td><a href='merchant?id=<?php echo $Transaction->getMerchantID(); ?>'><?php echo $Transaction->getMerchantShortName(); ?></a></td>
                    </tr>

                </table>
            </fieldset>
        </form>
    </section>