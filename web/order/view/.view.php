<?php
use Order\Model\OrderRow;
/** @var \Order\View\OrderView $this*/
$Order = $this->getOrder();
$odd = true;
$action_url = 'order/receipt.php?uid=' . $Order->getUID() . '&action=';
$SessionManager = new \User\Session\SessionManager();
$SessionUser = $SessionManager->getSessionUser();
?>

<!-- Page Navigation -->
<nav class="page-menu hide-on-print">
    <a href="<?php echo $action_url; ?>receipt" class="button current">Receipt <div class="submenu-icon submenu-icon-receipt"></div></a>
    <a href="javascript:window.print();" class="button">Print <div class="submenu-icon submenu-icon-print"></div></a>
    <a href="<?php echo $action_url; ?>download" class="button">Download PDF <div class="submenu-icon submenu-icon-download"></div></a>
    <a href="<?php echo $action_url; ?>email" class="button">Send as Email <div class="submenu-icon submenu-icon-email"></div></a>
    <a href="<?php echo $action_url; ?>bookmark" class="button">Bookmark URL <div class="submenu-icon submenu-icon-bookmark"></div></a>
    <?php if($SessionUser->hasAuthority('ROLE_ADMIN')) { ?>
        <a href="order?" class="button">Orders <div class="submenu-icon submenu-icon-list"></div></a>
        <a href="transaction/charge.php?" class="button">Charge  <div class="submenu-icon submenu-icon-charge"></div></a>
    <?php } ?>
</nav>

<!-- Bread Crumbs -->
<aside class="bread-crumbs">
    <a href="home" class="nav_home">Home</a>
    <?php if($SessionUser->hasAuthority('ROLE_ADMIN')) { ?>
    <a href="order" class="nav_order">Orders</a>
    <?php } ?>
    <a href="<?php echo $action_url; ?>view" class="nav_transaction_view">#<?php echo $Order->getUID(); ?></a>
</aside>

<section class="content">
    <?php if($this->hasException()) echo "<h5>", $this->getException()->getMessage(), "</h5>"; ?>

    <form class="form-view-transaction themed" onsubmit="return false;">
        <fieldset style="display: inline-block;">
            <legend>Order Information</legend>
            <table class="table-transaction-info themed striped-rows">
                <tbody>
                <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                    <td class="name">Amount</td>
                    <td>$<?php echo $Order->getAmount(); ?></td>
                </tr>

                <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                    <td class="name">Merchant</td>
                    <td><?php echo $Order->getMerchantShortName(); ?></td>
                </tr>
                <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                    <td class="name">Date</td>
                    <td><?php echo date("M jS Y G:i:s", strtotime($Order->getDate())); ?></td>
                </tr>
                <?php if($Order->getInvoiceNumber()) { ?>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td class="name">Invoice</td>
                        <td><?php echo $Order->getInvoiceNumber() ?: 'N/A'; ?></td>
                    </tr>
                <?php } ?>
                <?php if($Order->getCustomerID()) { ?>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td class="name">Customer</td>
                        <td><?php echo $Order->getCustomerID() ?: 'N/A' ?></td>
                    </tr>
                <?php } ?>
                <?php if($Order->getUsername()) { ?>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td class="name">Username</td>
                        <td><?php echo $Order->getUsername() ?: 'N/A' ?></td>
                    </tr>
                <?php } ?>

                <?php if ($Order->getPayeeEmail()) { ?>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td class="name">Email</td>
                        <td><a href="mailto:<?php echo $Order->getPayeeEmail() ?>"><?php echo $Order->getPayeeEmail() ?></a></td>
                    </tr>
                <?php }  ?>
                <?php if ($Order->getPayeeZipCode()) { ?>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td class="name">Zip Code</td>
                        <td><?php echo $Order->getPayeeZipCode(); ?></td>
                    </tr>
                <?php }  ?>

                <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                    <td class="name">Order Status</td>
                    <td><?php echo $Order->getStatus() ?: 'N/A' ?></td>
                </tr>
                </tbody>
            </table>
        </fieldset>

        <?php if ($Order->getCardNumber()) { ?>

            <fieldset style="display: inline-block;">
                <legend>Card Holder Information</legend>
                <table class="table-transaction-info themed striped-rows">
                    <tbody>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td class="name">Card Holder</td>
                        <td><?php echo $Order->getCardHolderFullName() ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td class="name">Card Number</td>
                        <td><?php echo $Order->getCardNumber(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td class="name">Exp</td>
                        <td><?php echo $Order->getCardExpMonth(), '/', $Order->getCardExpYear(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td class="name">Card Type</td>
                        <td><?php echo $Order->getCardType(); ?></td>
                    </tr>
                    </tbody>
                </table>
            </fieldset>

        <?php } else  { ?>

            <fieldset style="display: inline-block;">
                <legend>e-Check Information</legend>
                <table class="table-transaction-info themed striped-rows">
                    <tbody>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td class="name">Name on Account</td>
                        <td><?php echo $Order->getCheckAccountName(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td class="name">Check Account Number</td>
                        <td><?php echo $Order->getCheckAccountNumber() ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td class="name">Check Routing Number</td>
                        <td><?php echo $Order->getCheckRoutingNumber(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td class="name">Check Account Type</td>
                        <td><?php echo $Order->getCheckAccountType(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td class="name">Check Type</td>
                        <td><?php echo $Order->getCheckType(); ?></td>
                    </tr>
                    <?php if($Order->getCheckNumber()) { ?>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Check Number</td>
                            <td><?php echo $Order->getCheckNumber(); ?></td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </fieldset>

        <?php } ?>

        <fieldset>
            <legend>Transactions History</legend>
            <table class="table-results themed small">
                <tr>
                    <th>ID</th>
                    <th>Order</th>
                    <th>Card&nbsp;Holder</th>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Fee</th>
                    <th>Action</th>
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
                        <td><?php echo $Transaction->getHolderFullName(); ?></td>
                        <td><?php echo date("M jS Y G:i:s", strtotime($Transaction->getTransactionDate())); ?></td>
                        <td>$<?php echo $Transaction->getAmount(); ?></td>
                        <td>$<?php echo $Transaction->getServiceFee(); ?></td>
                        <td><?php echo $Transaction->getAction(); ?></td>
                        <td><a href='merchant?id=<?php echo $Transaction->getMerchantID(); ?>'><?php echo $Transaction->getMerchantShortName(); ?></a></td>
                    </tr>
                <?php } ?>
            </table>
        </fieldset>


    </form>
</section>