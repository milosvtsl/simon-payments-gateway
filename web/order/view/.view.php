<?php
use \Merchant\Model\MerchantRow;
/** @var \Order\View\OrderView $this*/

$Order = $this->getOrder();
$Transaction = $Order->fetchAuthorizedTransaction();
$Merchant = MerchantRow::fetchByID($Order->getMerchantID());
$odd = true;
$action_url = 'order/receipt.php?uid=' . $Order->getUID(false) . '&action=';
$action_url_pdf = 'order/pdf.php?uid=' . $Order->getUID(false);
$SessionManager = new \User\Session\SessionManager();
$SessionUser = $SessionManager->getSessionUser();

// Get Timezone diff
$offset = $SessionUser->getTimeZoneOffset('now');


$Theme = $this->getTheme();
$Theme->addPathURL('order',        'Transactions');
$Theme->addPathURL($action_url,    $Order->getUID(true));
$Theme->renderHTMLBodyHeader();
$Theme->printHTMLMenu('order-view', $action_url);
?>

    <article class="themed">

        <section class="content">

            <?php if($this->hasMessage()) echo "<h5>", $this->getMessage(), "</h5>"; ?>

            <form name="form-order-view" id="form-order-view" class="themed" method="POST">
                <fieldset style="display: inline-block;">
                    <div class="legend"><?php echo $Merchant->getShortName(); ?></div>
                    <table class="table-transaction-info themed striped-rows">
                        <tbody>
                        <?php $odd = true; ?>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">City, State</td>
                            <td class="value"><?php echo $Merchant->getCity(), ',', $Merchant->getState(); ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Zip Code</td>
                            <td class="value"><?php echo $Merchant->getZipCode(); ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Phone</td>
                            <td class="value"><?php echo $Merchant->getTelephone(); ?></td>
                        </tr>

<!--                        <tr class="row---><?php //echo ($odd=!$odd)?'odd':'even';?><!--">-->
<!--                            <td class="name">Customer</td>-->
<!--                            <td class="value">--><?php //echo $Order->getCustomerFullName() ?><!--</td>-->
<!--                        </tr>-->


                        </tbody>
                    </table>
                </fieldset>

                <fieldset style="display: inline-block;">
                    <div class="legend">Receipt</div>
                    <table class="table-transaction-info themed striped-rows">
                        <tbody>
                            <?php $odd = true; ?>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">Date</td>
                                <td class="value"><?php echo date("F jS Y", strtotime($Order->getDate()) + $offset); ?></td>
                            </tr>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">Time</td>
                                <td class="value"><?php echo date("g:i:s A", strtotime($Order->getDate()) + $offset); ?></td>
                            </tr>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">Time Zone</td>
                                <td class="value"><?php echo str_replace('_', '', $SessionUser->getTimeZone()); ?></td>
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
                            <?php if ($Order->getPayeeEmail()) { ?>
                                <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                    <td class="name">Email</td>
                                    <td class="value"><a href="mailto:<?php echo $Order->getPayeeEmail() ?>"><?php echo $Order->getPayeeEmail() ?></a></td>
                                </tr>
                            <?php }  ?>
                        </tbody>
                    </table>
                </fieldset>


                <fieldset style="display:inline-block; min-width: 5em;">
                    <div class="legend">Totals</div>
                    <table class="table-transaction-info-totals themed striped-rows ">
                        <tbody>
                        <?php $odd = true; ?>
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

                    <fieldset style="max-width: 44em;">
                        <div class="legend">Card Holder: <?php echo $Order->getCardHolderFullName(); ?></div>
                        <table class="table-transaction-info themed cell-borders small" style="width: 94%">
                            <tbody>
                                <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                    <?php if($Order->getUsername()) { ?>
                                    <th>User ID</th>
                                    <?php }  ?>
                                    <th>Credit Card</th>
                                    <th>Card Type</th>
                                    <th>Status</th>
                                    <th>Code</th>
                                    <th>Order ID</th>
                                </tr>
                                <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                    <?php if($Order->getUsername()) { ?>
                                        <td class="value"><?php echo $Order->getUsername(); ?></td>
                                    <?php }  ?>
                                    <td class="value"><?php echo $Order->getCardNumber() ? substr($Order->getCardNumber(), -4) : 'N/A'; ?></td>
                                    <td class="value"><?php echo $Order->getCardType(); ?></td>
                                    <td class="value"><?php echo $Order->getStatus(); ?></td>
                                    <td class="value"><?php echo $Transaction ? $Transaction->getTransactionID() : "N/A"; ?></td>
                                    <td class="value"><?php echo $Order->getID(); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </fieldset>

                <?php } else  { ?>

                    <fieldset style="max-width: 44em;">
                        <div class="legend">e-Check : <?php echo $Order->getCheckAccountName(); ?></div>
                        <table class="table-transaction-card-info themed cell-borders small"  style="width: 94%">
                            <tbody>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <?php if($Order->getUsername()) { ?>
                                    <th>User ID</th>
                                <?php }  ?>
                                <th>Type</th>
                                <th>Account</th>
                                <th>Routing</th>
                                <th>Usage</th>
                                <th>Num</th>
                                <th>Status</th>
                                <th>Code</th>
                                <th>Order ID</th>
                            </tr>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <?php if($Order->getUsername()) { ?>
                                    <td class="value"><?php echo $Order->getUsername(); ?></td>
                                <?php }  ?>
                                <td class="value"><?php echo $Order->getCheckType(); ?></td>
                                <td class="value"><?php echo $Order->getCheckAccountNumber(); ?></td>
                                <td class="value"><?php echo $Order->getCheckRoutingNumber(); ?></td>
                                <td class="value"><?php echo $Order->getCheckAccountType(); ?></td>
                                <td class="value"><?php echo $Order->getCheckNumber(); ?></td>
                                <td class="value"><?php echo $Order->getStatus(); ?></td>
                                <td class="value"><?php echo $Transaction ? $Transaction->getTransactionID() : 'N/A'; ?></td>
                                <td class="value"><?php echo $Order->getID(); ?></td>
                            </tr>
                            </tbody>
                        </table>
                    </fieldset>


                <?php } ?>


                <fieldset class="show-on-print" style="clear: both;">
                    <br/>
                    <br/>
                    <br/>
                    <hr style="height: 2px;">
                    Customer Signature
                </fieldset>

                <?php if ($Order->getSubscriptionCount() > 0) { ?>
                <fieldset style="max-width: 44em;" class="hide-on-print">
                    <div class="legend">Subscription Status</div>
                    <table class="table-results themed small cell-borders" style="width: 94%">
                        <tr>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Frequency</th>
                            <th>Next Recurrence</th>
                            <th>Perform</th>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td>$<?php echo $Order->getSubscriptionAmount(), ' (', $Order->getSubscriptionCount(),')'; ?></td>
                            <td><?php echo $Order->getSubscriptionStatus(), $Order->getSubscriptionMessage() ? ': ' : '', $Order->getSubscriptionMessage(); ?></td>
                            <td><?php echo $Order->getSubscriptionFrequency(); ?></td>
                            <td><?php echo date("Y M j g:i A", strtotime($Order->getSubscriptionNextDate()) + $offset); ?></td>
                            <td>
                                <?php
                                $disabled = $Order->getSubscriptionStatus() == 'Active' ? '' : " disabled='disabled'";
                                echo "<input name='action' type='submit' value='Cancel'{$disabled}/>";
                                ?>
                            </td>
                        </tr>
                    </table>
                </fieldset>
                <?php } ?>

                <fieldset style="max-width: 44em;" class="hide-on-print">
                    <div class="legend">Transaction History</div>
                    <table class="table-results themed small cell-borders" style="width: 94%">
                        <tr>
                            <th class="hide-on-layout-narrow">TID</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Fee</th>
                            <th>Action</th>
                            <th>Perform</th>
                        </tr>
                        <?php
                        /** @var \Order\Model\TransactionRow $Transaction */
                        $DB = \System\Config\DBConfig::getInstance();
                        $TransactionQuery = $DB->prepare(\Order\Model\TransactionRow::SQL_SELECT . "WHERE t.order_item_id = ? LIMIT 100");
                        /** @noinspection PhpMethodParametersCountMismatchInspection */
                        $TransactionQuery->setFetchMode(\PDO::FETCH_CLASS, \Order\Model\TransactionRow::_CLASS);
                        $TransactionQuery->execute(array($this->getOrder()->getID()));
                        $odd = false;
                        foreach($TransactionQuery as $Transaction) { ?>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="hide-on-layout-narrow"><a href='/order/receipt.php?uid=<?php echo $Order->getUID(false); ?>'><?php echo $Transaction->getTransactionID(); ?></a></td>
                                <td><?php echo date("M j g:i A", strtotime($Transaction->getTransactionDate()) + $offset); ?></td>
                                <td>$<?php echo $Transaction->getAmount(); ?></td>
                                <td>$<?php echo $Transaction->getServiceFee(); ?></td>
                                <td>
                                    <a href="integration/request?id=<?php echo $Transaction->getIntegrationRequestID(); ?>">
                                        <?php echo $Transaction->getAction(); ?>
                                    </a>
                                </td>
                                <td>
                                    <?php
                                    switch($Transaction->getAction()) {
                                        case 'Authorized':
                                            if($Order->getStatus() === 'Authorized') {
                                                $disabled = $SessionUser->hasAuthority('ROLE_VOID_CHARGE', 'ROLE_ADMIN') ? '' : " disabled='disabled'";
                                                echo "<input name='action' type='submit' value='Void'{$disabled}/>";
                                            }
                                            break;

                                        case 'Settled':
                                            if($Order->getStatus() === 'Settled') {
                                                $disabled = $SessionUser->hasAuthority('ROLE_RETURN_CHARGE', 'ROLE_ADMIN') ? '' : " disabled='disabled'";
                                                echo "<input name='action' type='submit' value='Return'{$disabled}/>";
                                            }
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

<?php
// Render Footer
$this->getTheme()->renderHTMLBodyFooter();
?>