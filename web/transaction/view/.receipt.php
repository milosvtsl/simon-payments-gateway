<?php
use Order\Model\OrderRow;
/**
 * @var \Transaction\View\TransactionView $this
 **/
$Transaction = $this->getTransaction();
$Order = OrderRow::fetchByID($Transaction->getOrderID());
if(!$Order)
    throw new Exception("Order ID was not set");
$odd = true;
$action_url = 'receipt?uid=' . $Transaction->getUID() . '&action=';
?>

    <!-- Page Navigation -->
    <nav class="page-menu">
        <a href="<?php echo $action_url; ?>view" class="button current">Receipt</a>
        <a href="<?php echo $action_url; ?>download" class="button">Download PDF</a>
        <a href="<?php echo $action_url; ?>email" class="button">Send as Email</a>
        <a href="<?php echo $action_url; ?>bookmark" class="button">Bookmark URL</a>
    </nav>

    <!-- Bread Crumbs -->
    <aside class="bread-crumbs">
        <a href="home" class="nav_home">Home</a>
        <a href="<?php echo $action_url; ?>view" class="nav_transaction_view">Receipt #<?php echo $Transaction->getUID(); ?></a>
    </aside>

    <section class="content">
        <?php if($this->hasException()) echo "<h5>", $this->getException()->getMessage(), "</h5>"; ?>

        <form class="form-view-transaction themed" onsubmit="return false;">
            <fieldset style="display: inline-block;">
                <legend>Transaction Information</legend>
                <table class="table-transaction-info themed striped-rows">
                    <tbody>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Amount</td>
                            <td>$<?php echo $Transaction->getAmount(); ?></td>
                        </tr>

                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Service Fee</td>
                            <td>$<?php echo $Transaction->getServiceFee(); ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Merchant</td>
                            <td><?php echo $Transaction->getMerchantShortName(); ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Date</td>
                            <td><?php echo date("M jS Y G:i:s", strtotime($Transaction->getDate())); ?></td>
                        </tr>
                        <?php if($Transaction->getInvoiceNumber()) { ?>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Invoice</td>
                            <td><?php echo $Transaction->getInvoiceNumber() ?: 'N/A'; ?></td>
                        </tr>
                        <?php } ?>
                        <?php if($Transaction->getCustomerID()) { ?>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Customer</td>
                            <td><?php echo $Transaction->getCustomerID() ?: 'N/A' ?></td>
                        </tr>
                        <?php } ?>
                        <?php if($Transaction->getUsername()) { ?>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">Username</td>
                                <td><?php echo $Transaction->getUsername() ?: 'N/A' ?></td>
                            </tr>
                        <?php } ?>

                        <?php if ($Order->getPayeeEmail()) { ?>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">Email</td>
                                <td><?php echo $Order->getPayeeEmail() ?></td>
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


        </form>
    </section>