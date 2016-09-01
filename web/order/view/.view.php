<?php
/**
 * @var \Order\View\OrderView $this
 * @var PDOStatement $OrderQuery
 **/
$Order = $this->getOrder();
$odd = false;
$action_url = 'order?id=' . $Order->getID() . '&action=';
?>
    <section class="message">
        <h1>View Order</h1>

        <?php if($this->hasException()) { ?>
            <h5><?php echo $this->hasException(); ?></h5>

        <?php } else if ($this->hasSessionMessage()) { ?>
            <h5><?php echo $this->popSessionMessage(); ?></h5>

        <?php } else { ?>
            <h5>View a order...</h5>

        <?php } ?>
    </section>

    <section class="content">
        <form class="form-view-order themed" onsubmit="return false;">
            <fieldset class="action-fields">
                <legend>Actions</legend>
                <a href="order?" class="button">Order List</a>
                <a href="<?php echo $action_url; ?>edit" class="button">Edit</a>
            </fieldset>
            <fieldset>
                <legend>Order Information</legend>
                <table class="table-order-info themed">
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
                        <td><?php echo $Order->getHolderFullFullName() ?></td>
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
                <legend>Transactions</legend>
                <table class="table-results themed">
                    <tr>
                        <th>ID</th>
                        <th>Card Holder / TID</th>
                        <th>Customer ID</th>
                        <th>Invoice ID</th>
                        <th>User Name</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Merchant</th>
                    </tr>
                    <?php
                    /** @var PDO $TransactionQuery */
                    /** @var \Transaction\Model\TransactionRow $Transaction */
                    $odd = false;
                    foreach($TransactionQuery as $Transaction) { ?>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td><a href='transaction?id=<?php echo $Transaction->getID(); ?>'><?php echo $Transaction->getID(); ?></a></td>
                            <td>
                                <?php echo $Transaction->getHolderFullFullName(); ?>  <br/>
                                <?php echo $Transaction->getTransactionID(); ?>
                            </td>
                            <td><?php echo $Transaction->getCustomerID(); ?></td>
                            <td><?php echo $Transaction->getInvoiceNumber(); ?></td>
                            <td><?php echo $Transaction->getUsername(); ?></td>
                            <td><?php echo $Transaction->getAmount(); ?></td>
                            <td><?php echo $Transaction->getStatus(); ?></td>
                            <td><a href='merchant?id=<?php echo $Transaction->getMerchantID(); ?>'><?php echo $Transaction->getMerchantShortName(); ?></a></td>

                        </tr>
                    <?php } ?>
                </table>
            </fieldset>
        </form>
    </section>