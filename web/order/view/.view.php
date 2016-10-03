<?php
/**
 * @var \Order\View\OrderView $this
 * @var PDOStatement $OrderQuery
 **/
$Order = $this->getOrder();
$odd = false;
$action_url = 'order?id=' . $Order->getID() . '&action=';
?>
    <!-- Page Navigation -->
    <nav class="page-menu">
        <a href="transaction?" class="button">Transactions</a>
        <a href="order?" class="button">Orders</a>
        <a href="transaction/charge.php?" class="button">Charge</a>
        <a href="<?php echo $action_url; ?>view" class="button current">View #<?php echo $Order->getID(); ?></a>
        <a href="<?php echo $action_url; ?>edit" class="button">Edit #<?php echo $Order->getID(); ?></a>
    </nav>
    
    <!-- Bread Crumbs -->
    <aside class="bread-crumbs">
        <a href="home" class="nav_home">Home</a>
        <a href="order" class="nav_order">Orders</a>
        <a href="<?php echo $action_url; ?>view" class="nav_order_view">#<?php echo $Order->getID(); ?></a>
    </aside>
    
    <section class="content">
        <?php if($this->hasException()) echo "<h5>", $this->getException()->getMessage(), "</h5>"; ?>

        <form class="form-view-order themed" onsubmit="return false;">
            <fieldset>
                <legend>Order Information</legend>
                <table class="table-order-info themed" style="float: left;">
                    <tr>
                        <th>Field</th>
                        <th>Value</th>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>ID</td>
                        <td><?php echo $Order->getID(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>UID</td>
                        <td><?php echo $Order->getUID(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Status</td>
                        <td><?php echo $Order->getStatus() ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Amount</td>
                        <td><?php echo $Order->getAmount(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Date</td>
                        <td><?php echo date("M jS Y G:i:s", strtotime($Order->getDate())); ?></td>
                    </tr>
                </table>
                <table class="table-order-info themed">
                    <tr>
                        <th>Field</th>
                        <th>Value</th>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Invoice</td>
                        <td><?php echo $Order->getInvoiceNumber() ?: 'N/A'; ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Customer</td>
                        <td><?php echo $Order->getCustomerID() ?: 'N/A' ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Username</td>
                        <td><?php echo $Order->getUsername() ?: 'N/A' ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Card Holder</td>
                        <td><?php echo $Order->getCardHolderFullName() ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Order Status</td>
                        <td><?php echo $Order->getStatus() ?: 'N/A' ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Merchant</td>
                        <td><a href='merchant?id=<?php echo $Order->getMerchantID(); ?>'><?php echo $Order->getMerchantShortName(); ?></a></td>
                    </tr>

                </table>
            </fieldset>

            <fieldset>
                <legend>Transactions: Order #<?php echo $Order->getID(); ?></legend>
                <table class="table-results themed small">
                    <tr>
                        <th>ID</th>
                        <th>Order</th>
                        <th>Batch</th>
                        <th>Card Holder</th>
                        <th>Date</th>
                        <th>Invoice ID</th>
                        <th>User Name</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Merchant</th>
                    </tr>
                    <?php
                    /** @var \Transaction\Model\TransactionRow $Transaction */
                    $DB = \Config\DBConfig::getInstance();
                    $TransactionQuery = $DB->prepare(\Transaction\Model\TransactionRow::SQL_SELECT . "WHERE t.order_item_id = ? LIMIT 100");
                    /** @noinspection PhpMethodParametersCountMismatchInspection */
                    $TransactionQuery->setFetchMode(\PDO::FETCH_CLASS, \Transaction\Model\TransactionRow::_CLASS);
                    $TransactionQuery->execute(array($this->getOrder()->getID()));
                    $odd = false;
                    foreach($TransactionQuery as $Transaction) { ?>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td><a href='transaction?id=<?php echo $Transaction->getID(); ?>'><?php echo $Transaction->getID(); ?></a></td>
                            <td><?php if($Transaction->getOrderID()) { ?><a href='order?id=<?php echo $Transaction->getOrderID(); ?>'><?php echo $Transaction->getOrderID(); ?></a><?php } else echo 'N/A'; ?></td>
                            <td><?php if($Transaction->getBatchID()) { ?><a href='batch?id=<?php echo $Transaction->getBatchID(); ?>'><?php echo $Transaction->getBatchID(); ?></a><?php } else echo 'N/A'; ?></td>
                            <td><?php echo $Transaction->getHolderFullName(); ?></td>
                            <td><?php echo date("M jS Y G:i:s", strtotime($Transaction->getTransactionDate())); ?></td>
                            <td><?php echo $Transaction->getInvoiceNumber(); ?></td>
                            <td><?php echo $Transaction->getUsername(); ?></td>
                            <td><?php echo $Transaction->getAmount(); ?></td>
                            <td><?php echo $Transaction->getOrderStatus(); ?></td>
                            <td><a href='merchant?id=<?php echo $Transaction->getMerchantID(); ?>'><?php echo $Transaction->getMerchantShortName(); ?></a></td>
                        </tr>
                    <?php } ?>
                </table>
            </fieldset>
        </form>
    </section>