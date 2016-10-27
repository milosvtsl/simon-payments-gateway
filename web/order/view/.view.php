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
    <?php if($SessionUser->hasAuthority('ROLE_ADMIN')) { ?>
        <a href="order?" class="button">Transactions <div class="submenu-icon submenu-icon-list"></div></a>
        <a href="transaction/charge.php?" class="button">Charge  <div class="submenu-icon submenu-icon-charge"></div></a>
    <?php } ?>
    <a href="<?php echo $action_url; ?>receipt" class="button current">Receipt <div class="submenu-icon submenu-icon-receipt"></div></a>
    <a href="javascript:window.print();" class="button">Print <div class="submenu-icon submenu-icon-print"></div></a>
    <a href="<?php echo $action_url; ?>download" class="button">Download <div class="submenu-icon submenu-icon-download"></div></a>
    <a href="<?php echo $action_url; ?>email" class="button">Email <div class="submenu-icon submenu-icon-email"></div></a>
<!--    <a href="--><?php //echo $action_url; ?><!--bookmark" class="button">Bookmark URL <div class="submenu-icon submenu-icon-bookmark"></div></a>-->
</nav>

<article class="themed">

    <section class="content">
        <!-- Bread Crumbs -->
        <aside class="bread-crumbs">
            <?php if($SessionUser->hasAuthority('ROLE_ADMIN')) { ?>
                <a href="order" class="nav_order hide-on-print">Transactions</a>
            <?php } ?>
            <a href="<?php echo $action_url; ?>view" class="nav_transaction_view">#<?php echo $Order->getUID(); ?></a>
        </aside>
        <?php if($this->hasMessage()) echo "<h5>", $this->getMessage(), "</h5>"; ?>

        <form name="form-order-view" id="form-order-view" class="themed" method="POST">
            <fieldset style="display: inline-block;">
                <legend>Order Information</legend>
                <table class="table-transaction-info themed striped-rows">
                    <tbody>

                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td class="name">Merchant</td>
                        <td class="value"><?php echo $Order->getMerchantShortName(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td class="name">Date</td>
                        <td class="value"><?php echo date("M jS Y G:i", strtotime($Order->getDate())); ?></td>
                    </tr>
                    <?php if($Order->getInvoiceNumber()) { ?>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Invoice</td>
                            <td class="value"><?php echo $Order->getInvoiceNumber() ?: 'N/A'; ?></td>
                        </tr>
                    <?php } ?>
                    <?php if($Order->getCustomerID()) { ?>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Customer</td>
                            <td class="value"><?php echo $Order->getCustomerID() ?: 'N/A' ?></td>
                        </tr>
                    <?php } ?>
                    <?php if($Order->getUsername()) { ?>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Username</td>
                            <td class="value"><?php echo $Order->getUsername() ?: 'N/A' ?></td>
                        </tr>
                    <?php } ?>

                    <?php if ($Order->getPayeeEmail()) { ?>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Email</td>
                            <td class="value"><a href="mailto:<?php echo $Order->getPayeeEmail() ?>"><?php echo $Order->getPayeeEmail() ?></a></td>
                        </tr>
                    <?php }  ?>

                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td class="name">Order Status</td>
                        <td class="value"><?php echo $Order->getStatus() ?: 'N/A' ?></td>
                    </tr>

                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td class="name">Entry Method</td>
                        <td class="value"><?php echo ucfirst($Order->getEntryMode()) ?: 'N/A' ?></td>
                    </tr>
                    <?php if($SessionUser->hasAuthority('ROLE_ADMIN')) { ?>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?> hide-on-print">
                            <td class="name">Integration</td>
                            <td class="value">
                                <?php echo ucfirst($Order->getIntegrationName()) ?: 'N/A' ?>
                            </td>
                        </tr>
                    <?php } ?>

                    <?php if ($Order->getConvenienceFee()) { ?>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Subtotal</td>
                            <td class="value">$<?php echo $Order->getAmount(); ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Fee</td>
                            <td class="value">$<?php echo $Order->getConvenienceFee(); ?></td>
                        </tr>
                    <?php } ?>

                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td class="name">Total</td>
                        <td class="value">$<?php echo number_format($Order->getAmount()+$Order->getConvenienceFee(), 2); ?></td>
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
                                <td class="value"><?php echo $Order->getCardHolderFullName() ?></td>
                            </tr>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">Card Number</td>
                                <td class="value"><?php echo $Order->getCardNumber(); ?></td>
                            </tr>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">Exp</td>
                                <td class="value"><?php echo $Order->getCardExpMonth(), '/', $Order->getCardExpYear(); ?></td>
                            </tr>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">Card Type</td>
                                <td class="value"><?php echo $Order->getCardType(); ?></td>
                            </tr>
                        <?php if ($Order->getPayeeAddress()) { ?>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">Address</td>
                                <td class="value"><?php echo $Order->getPayeeAddress(); ?><br/><?php echo $Order->getPayeeAddress2(); ?></td>
                            </tr>
                        <?php }  ?>
                        <?php if ($Order->getPayeeZipCode()) { ?>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">Zip Code</td>
                                <td class="value"><?php echo $Order->getPayeeZipCode(); ?></td>
                            </tr>
                        <?php }  ?>
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
                            <td class="value"><?php echo $Order->getCheckAccountName(); ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Check Account Number</td>
                            <td class="value"><?php echo $Order->getCheckAccountNumber() ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Check Routing Number</td>
                            <td class="value"><?php echo $Order->getCheckRoutingNumber(); ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Check Account Type</td>
                            <td class="value"><?php echo $Order->getCheckAccountType(); ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Check Type</td>
                            <td class="value"><?php echo $Order->getCheckType(); ?></td>
                        </tr>
                        <?php if($Order->getCheckNumber()) { ?>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">Check Number</td>
                                <td class="value"><?php echo $Order->getCheckNumber(); ?></td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </fieldset>

            <?php } ?>

            <fieldset class="show-on-print">
                <br/>
                <br/>
                <br/>
                <hr>
                Customer Signature
            </fieldset>

            <fieldset style="display: inline-block;" class="hide-on-print">
                <legend>Transaction History</legend>
                <table class="table-results themed small">
                    <tr>
                        <th>ID</th>
                        <th class="hide-on-layout-vertical">TID</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Fee</th>
                        <th>Action</th>
                        <th>Perform</th>
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
                            <td><a href='transaction?id=<?php echo $Transaction->getID(); ?>#form-order-view'><?php echo $Transaction->getID(); ?></a></td>
                            <td class="hide-on-layout-vertical"><?php echo $Transaction->getTransactionID(); ?></td>
                            <td><?php echo date("m/d H:i", strtotime($Transaction->getTransactionDate())); ?></td>
                            <td>$<?php echo $Transaction->getAmount(); ?></td>
                            <td>$<?php echo $Transaction->getServiceFee(); ?></td>
                            <td>
                                <a href="integration/request?type=transaction&type_id=<?php echo $Transaction->getID(); ?>">
                                    <?php echo $Transaction->getAction(); ?>
                                </a>
                            </td>
                            <td>
                                <?php
                                    switch($Transaction->getAction()) {
                                        case 'Authorized':
                                            $disabled = $Order->getStatus() !== 'Authorized' ? " disabled='disabled'" : '';
                                            echo "<input name='action' type='submit' value='Void'{$disabled}/>";
                                            break;

                                        case 'Settled':
                                            $disabled = $Order->getStatus() !== 'Settled' ? " disabled='disabled'" : '';
                                            echo "<input name='action' type='submit' value='Return'{$disabled}/>";
                                            break;
                                    }
                                ?>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
            </fieldset>


        </form>
    </section>
</article>