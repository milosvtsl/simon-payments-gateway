<?php
use Merchant\Model\MerchantRow;

/** @var \Subscription\View\SubscriptionView $this*/
// Render Header
$this->getTheme()->renderHTMLBodyHeader();

$Subscription = $this->getSubscription();
$Merchant = MerchantRow::fetchByID($Subscription->getMerchantID());
$odd = true;
$action_url = 'subscription/receipt.php?uid=' . $Subscription->getUID() . '&action=';
$action_url_pdf = 'subscription/pdf.php?uid=' . $Subscription->getUID();
$SessionManager = new \User\Session\SessionManager();
$SessionUser = $SessionManager->getSessionUser();

// Get Timezone diff
$offset = $SessionUser->getTimeZoneOffset('now');

$this->getTheme()->printHTMLMenu('subscription-view', $action_url, array(
        '<a href="' . $action_url . 'receipt" class="button">Receipt <div class="submenu-icon submenu-icon-receipt"></div></a>',
        '<a href="javascript:window.print();" class="button">Print <div class="submenu-icon submenu-icon-print"></div></a>',
        '<a href="' . $action_url . 'download" class="button">Download <div class="submenu-icon submenu-icon-download"></div></a>',
));
?>

    <article class="themed">
        <!-- Bread Crumbs -->
        <aside class="bread-crumbs hide-on-print">
            <?php if($SessionUser->hasAuthority('ROLE_ADMIN')) { ?>
                <a href="subscription" class="nav_subscription">Subscriptions</a>
            <?php } ?>
            <a href="<?php echo $action_url; ?>view" class="nav_transaction_view">#<?php echo $Subscription->getUID(); ?></a>
        </aside>


        <section class="content">

            <?php if($SessionManager->hasMessage()) echo "<h5>", $SessionManager->popMessage(), "</h5>"; ?>

            <form name="form-subscription-view" id="form-subscription-view" class="themed" method="POST">
                <fieldset style="">
                    <div class="legend"><?php echo $Merchant->getShortName(); ?></div>
                    <table class="table-transaction-info themed striped-rows">
                        <tbody>
                        <?php $odd = true; ?>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Location</td>
                            <td class="value"><?php echo $Merchant->getCity(), ',', $Merchant->getState(), ' ', $Merchant->getZipCode() ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Phone</td>
                            <td class="value"><?php echo $Merchant->getTelephone(); ?></td>
                        </tr>

                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Customer</td>
                            <td class="value"><?php echo $Subscription->getCustomerFullName() ?></td>
                        </tr>


                        </tbody>
                    </table>
                </fieldset>

                <fieldset style="">
                    <div class="legend">Subscription</div>
                    <table class="table-transaction-info themed striped-rows">
                        <tbody>
                            <?php $odd = true; ?>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">Next Recur Date</td>
                                <td class="value"><?php echo date("F jS Y", strtotime($Subscription->getRecurNextDate()) + $offset); ?></td>
                            </tr>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">Recur Count</td>
                                <td class="value"><?php echo $Subscription->getRecurCount(); ?></td>
                            </tr>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">Recur Frequency</td>
                                <td class="value"><?php echo $Subscription->getRecurFrequency(); ?></td>
                            </tr>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">Time Zone</td>
                                <td class="value"><?php echo str_replace('_', '', $SessionUser->getTimeZone()); ?></td>
                            </tr>
                            <?php if($Subscription->getInvoiceNumber()) { ?>
                                <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                    <td class="name">Invoice</td>
                                    <td class="value"><?php echo $Subscription->getInvoiceNumber() ?: 'N/A'; ?></td>
                                </tr>
                            <?php } ?>
                            <?php if($Subscription->getCustomerID()) { ?>
                                <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                    <td class="name">Customer</td>
                                    <td class="value"><?php echo $Subscription->getCustomerID() ?: 'N/A' ?></td>
                                </tr>
                            <?php } ?>
                            <?php if ($Subscription->getPayeeEmail()) { ?>
                                <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                    <td class="name">Email</td>
                                    <td class="value"><a href="mailto:<?php echo $Subscription->getPayeeEmail() ?>"><?php echo $Subscription->getPayeeEmail() ?></a></td>
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
                        <?php if ($Subscription->getConvenienceFee()) { ?>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">Subtotal</td>
                                <td class="value">$<?php echo $Subscription->getAmount(); ?></td>
                            </tr>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">Fee</td>
                                <td class="value">$<?php echo $Subscription->getConvenienceFee(); ?></td>
                            </tr>
                        <?php } ?>

                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Total</td>
                            <td class="value">$<?php echo number_format($Subscription->getAmount()+$Subscription->getConvenienceFee(), 2); ?></td>
                        </tr>
                        </tbody>
                    </table>
                </fieldset>

                <?php if ($Subscription->getCardNumber()) { ?>

                    <fieldset style="max-width: 44em;">
                        <div class="legend">Card Holder: <?php echo $Subscription->getPayeeFullName(); ?></div>
                        <table class="table-transaction-info themed cell-bsubscriptions small" style="width: 94%">
                            <tbody>
                                <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                    <?php if($Subscription->getUsername()) { ?>
                                    <th>User ID</th>
                                    <?php }  ?>
                                    <th>Credit Card</th>
                                    <th>Card Type</th>
                                    <th>Status</th>
                                    <th>Code</th>
                                    <th>Subscription ID</th>
                                </tr>
                                <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                    <?php if($Subscription->getUsername()) { ?>
                                        <td class="value"><?php echo $Subscription->getUsername(); ?></td>
                                    <?php }  ?>
                                    <td class="value"><?php echo $Subscription->getCardNumber(); ?></td>
                                    <td class="value"><?php echo $Subscription->getCardType(); ?></td>
                                    <td class="value"><?php echo $Subscription->getStatus(); ?></td>
                                    <td class="value"><?php echo $Transaction ? $Transaction->getTransactionID() : "N/A"; ?></td>
                                    <td class="value"><?php echo $Subscription->getID(); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </fieldset>

                <?php } else  { ?>

                    <fieldset style="max-width: 44em;">
                        <div class="legend">e-Check : <?php echo $Subscription->getCheckAccountName(); ?></div>
                        <table class="table-transaction-card-info themed cell-bsubscriptions small"  style="width: 94%">
                            <tbody>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <?php if($Subscription->getUsername()) { ?>
                                    <th>User ID</th>
                                <?php }  ?>
                                <th>Type</th>
                                <th>Account</th>
                                <th>Routing</th>
                                <th>Usage</th>
                                <th>Num</th>
                                <th>Status</th>
                                <th>Code</th>
                                <th>Subscription ID</th>
                            </tr>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <?php if($Subscription->getUsername()) { ?>
                                    <td class="value"><?php echo $Subscription->getUsername(); ?></td>
                                <?php }  ?>
                                <td class="value"><?php echo $Subscription->getCheckType(); ?></td>
                                <td class="value"><?php echo $Subscription->getCheckAccountNumber(); ?></td>
                                <td class="value"><?php echo $Subscription->getCheckRoutingNumber(); ?></td>
                                <td class="value"><?php echo $Subscription->getCheckAccountType(); ?></td>
                                <td class="value"><?php echo $Subscription->getCheckNumber(); ?></td>
                                <td class="value"><?php echo $Subscription->getStatus(); ?></td>
                                <td class="value"><?php echo $Transaction ? $Transaction->getTransactionID() : 'N/A'; ?></td>
                                <td class="value"><?php echo $Subscription->getID(); ?></td>
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

                <?php if ($Subscription->getSubscriptionCount() > 0) { ?>
                <fieldset style="max-width: 44em;" class="hide-on-print">
                    <div class="legend">Subscription Status</div>
                    <table class="table-results themed small cell-bsubscriptions" style="width: 94%">
                        <tr>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Frequency</th>
                            <th>Next Recurrence</th>
                            <th>Perform</th>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td>$<?php echo $Subscription->getSubscriptionAmount(), ' (', $Subscription->getSubscriptionCount(),')'; ?></td>
                            <td><?php echo $Subscription->getSubscriptionStatus(), $Subscription->getSubscriptionMessage() ? ': ' : '', $Subscription->getSubscriptionMessage(); ?></td>
                            <td><?php echo $Subscription->getSubscriptionFrequency(); ?></td>
                            <td><?php echo date("Y M j g:i A", strtotime($Subscription->getSubscriptionNextDate()) + $offset); ?></td>
                            <td>
                                <?php
                                $disabled = $Subscription->getSubscriptionStatus() == 'Active' ? '' : " disabled='disabled'";
                                echo "<input name='action' type='submit' value='Cancel'{$disabled}/>";
                                ?>
                            </td>
                        </tr>
                    </table>
                </fieldset>
                <?php } ?>

                <fieldset style="max-width: 44em;" class="hide-on-print">
                    <div class="legend">Transaction History</div>
                    <table class="table-results themed small cell-bsubscriptions" style="width: 94%">
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
                        $TransactionQuery = $DB->prepare(\Order\Model\TransactionRow::SQL_SELECT . "WHERE t.subscription_item_id = ? LIMIT 100");
                        /** @noinspection PhpMethodParametersCountMismatchInspection */
                        $TransactionQuery->setFetchMode(\PDO::FETCH_CLASS, \Order\Model\TransactionRow::_CLASS);
                        $TransactionQuery->execute(array($this->getSubscription()->getID()));
                        $odd = false;
                        foreach($TransactionQuery as $Transaction) { ?>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="hide-on-layout-narrow"><a href='/subscription/receipt.php?uid=<?php echo $Subscription->getUID(); ?>'><?php echo $Transaction->getTransactionID(); ?></a></td>
                                <td><?php echo date("M j g:i A", strtotime($Transaction->getTransactionDate()) + $offset); ?></td>
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
                                            if($Subscription->getStatus() === 'Authorized') {
                                                $disabled = $SessionUser->hasAuthority('ROLE_VOID_CHARGE', 'ROLE_ADMIN') ? '' : " disabled='disabled'";
                                                echo "<input name='action' type='submit' value='Void'{$disabled}/>";
                                            }
                                            break;

                                        case 'Settled':
                                            if($Subscription->getStatus() === 'Settled') {
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