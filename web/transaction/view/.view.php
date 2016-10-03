<?php
/**
 * @var \Transaction\View\TransactionView $this
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
        <a href="<?php echo $action_url; ?>view" class="button current">View #<?php echo $Transaction->getID(); ?></a>
        <a href="<?php echo $action_url; ?>edit" class="button">Edit #<?php echo $Transaction->getID(); ?></a>
    </nav>

    <!-- Bread Crumbs -->
    <aside class="bread-crumbs">
        <a href="home" class="nav_home">Home</a>
        <a href="transaction" class="nav_transaction">Transactions</a>
        <a href="<?php echo $action_url; ?>view" class="nav_transaction_view">#<?php echo $Transaction->getID(); ?></a>
    </aside>

    <section class="content">
        <?php if($this->hasException()) echo "<h5>", $this->getException()->getMessage(), "</h5>"; ?>

        <form class="form-view-transaction themed" onsubmit="return false;">
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
                        <td>Order ID</td>
                        <td><a href='order?id=<?php echo $Transaction->getOrderID(); ?>'>#<?php echo $Transaction->getOrderID(); ?></a></td>
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
                        <td>Date</td>
                        <td><?php echo date("M jS Y G:i:s", strtotime($Transaction->getDate())); ?></td>
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
                        <td>Card Holder</td>
                        <td><?php echo $Transaction->getHolderFullName() ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Order Status</td>
                        <td><?php echo $Transaction->getOrderStatus() ?: 'N/A' ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Merchant</td>
                        <td><a href='merchant?id=<?php echo $Transaction->getMerchantID(); ?>'><?php echo $Transaction->getMerchantShortName(); ?></a></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Integration</td>
                        <td><a href='integration?id=<?php echo $Transaction->getIntegrationID(); ?>'><?php echo $Transaction->getIntegrationName(); ?></a></td>
                    </tr>
                </table>
            </fieldset>
        </form>
    </section>