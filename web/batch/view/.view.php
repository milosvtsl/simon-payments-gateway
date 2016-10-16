<?php
/**
 * @var \Batch\View\BatchView $this
 * @var PDOStatement $OrderQuery
 **/
$Batch = $this->getBatch();
$odd = false;
$action_url = 'batch?id=' . $Batch->getID() . '&action=';
?>
    <!-- Page Navigation -->
    <nav class="page-menu hide-on-print">
        <a href="transaction?" class="button">Transactions <div class="submenu-icon submenu-icon-list"></div></a>
        <a href="order?" class="button">Orders <div class="submenu-icon submenu-icon-list"></div></a>
        <a href="batch?" class="button">Batches <div class="submenu-icon submenu-icon-list"></div></a>
        <a href="<?php echo $action_url; ?>view" class="button current">View <div class="submenu-icon submenu-icon-view"></div></a>
        <a href="<?php echo $action_url; ?>edit" class="button">Edit <div class="submenu-icon submenu-icon-edit"></div></a>
    </nav>

    <article class="themed">
        <section class="content">

            <!-- Bread Crumbs -->
            <aside class="bread-crumbs">
                <a href="/" class="nav_home">Home</a>
                <a href="batch" class="nav_batch">Batches</a>
                <a href="<?php echo $action_url; ?>view" class="nav_batch_view">#<?php echo $Batch->getID(); ?></a>
            </aside>

            <?php if($this->hasException()) echo "<h5>", $this->getException()->getMessage(), "</h5>"; ?>

            <form class="form-view-batch themed" onsubmit="return false;">
                <fieldset style="display: inline-block;">
                    <legend>Batch Information</legend>
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
                            <td>Status</td>
                            <td><?php echo $Batch->getBatchStatus() ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td>Date</td>
                            <td><?php echo date("M jS Y G:i:s", strtotime($Batch->getDate())); ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td>Merchant</td>
                            <td><a href='merchant?id=<?php echo $Batch->getMerchantID(); ?>'><?php echo $Batch->getMerchantShortName(); ?></a></td>
                        </tr>

                    </table>
                </fieldset>
                <fieldset style="display: inline-block;">
                    <legend>Orders</legend>
                    <table class="table-results themed small">
                        <tr>
                            <th>ID</th>
                            <th>Card Holder</th>
                            <th>Date</th>
                            <th>Invoice&nbsp;ID</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Merchant</th>
                        </tr>
                        <?php
                        /** @var \Order\Model\OrderRow $Order */
                        $odd = false;
                        foreach($OrderQuery as $Order) { ?>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td><a href='order?uid=<?php echo $Order->getUID(); ?>'><?php echo $Order->getID(); ?></a></td>
                                <td><?php echo $Order->getCardHolderFullName(); ?></td>
                                <td><?php echo date("M jS Y G:i:s", strtotime($Order->getDate())); ?></td>
                                <td><?php echo $Order->getInvoiceNumber(); ?></td>
                                <td><?php echo $Order->getAmount(); ?></td>
                                <td><?php echo $Order->getStatus(); ?></td>
                                <td><a href='merchant?id=<?php echo $Order->getMerchantID(); ?>'><?php echo $Order->getMerchantShortName(); ?></a></td>

                            </tr>
                        <?php } ?>
                    </table>
                </fieldset>

            </form>
        </section>
    </article>