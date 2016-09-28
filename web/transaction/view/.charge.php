<?php
use User\Session\SessionManager;
use Merchant\Model\MerchantRow;
/**
 * @var \Transaction\View\TransactionView $this
 **/
$odd = false;
$SessionManager = new SessionManager();
$SessionUser = $SessionManager->getSessionUser();

?>
    <!-- Page Navigation -->
<!--    <nav class="page-menu">-->
<!--        <a href="transaction?" class="button">Transactions</a>-->
<!--        <a href="order?" class="button">Orders</a>-->
<!--        <a href="transaction/charge.php?" class="button current">Charge</a>-->
<!--    </nav>-->

    <!-- Bread Crumbs -->
    <aside class="bread-crumbs">
        <a href="home" class="nav_home">Home</a>
        <a href="transaction" class="nav_transaction">Transactions</a>
        <a href="transaction/charge.php" class="nav_transaction_charge">New Charge</a>
    </aside>

    <section class="content">
        <?php if($this->hasException()) echo "<h5>", $this->getException()->getMessage(), "</h5>"; ?>

        <form name="form-transaction-charge" class=" themed" method="POST">
            <input type="hidden" name="convenience_fee_flat" value="" />
            <input type="hidden" name="convenience_fee_limit" value="" />
            <input type="hidden" name="convenience_fee_variable_rate" value="" />

            <fieldset class="inline-on-merchant-selected">
                <legend>Choose a Merchant</legend>
                <select name="merchant_id" class="" autofocus>
                    <option value="">Choose a Merchant</option>
                    <?php
                    if($SessionUser->hasAuthority('ROLE_ADMIN')) {
                        $MerchantQuery = MerchantRow::queryAll();
                    } else {
                        $MerchantQuery = $SessionUser->queryUserMerchants();
                    }
                    foreach ($MerchantQuery as $Merchant)
                        /** @var \Merchant\Model\MerchantRow $Merchant */
                        echo "\n\t\t\t\t\t\t\t<option",
                        " data-form-class='", $Merchant->getChargeFormClasses(), "'",
                        " data-convenience-fee-flat='", $Merchant->getFeeFlat(), "'",
                        " data-convenience-fee-limit='", $Merchant->getFeeLimit(), "'",
                        " data-convenience-fee-variable-rate='", $Merchant->getFeeVariable(), "'",
                        " value='", $Merchant->getID(), "'>",
                        $Merchant->getShortName(), "</option>";
                    ?>
                </select>
            </fieldset>

            <fieldset style="display: inline-block;" class="show-on-merchant-selected">
                <legend>Choose a Payment Method</legend>
                <select name="payment_method" class="" autofocus>
                    <option value="keyed">Keyed Card</option>
                    <option value="swipe">Swipe Card</option>
                    <option value="check">e-Check</option>
                </select>
            </fieldset>

            <fieldset style="display: inline-block" class="show-on-payment-method-swipe">
                <legend class="alert reader-status">Card Swipe Ready</legend>
                <input type="text" name="swipe_input" size="30" />
            </fieldset>

            <hr/>

            <fieldset style="display: inline-block;" class="show-on-merchant-selected">
                <legend>Customer Fields</legend>
                <table class="table-transaction-charge themed" style="float: left;">
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?> required">
                        <td class="name">Payment Amount</td>
                        <td class="value"><input type="text" name="amount" value="" size="10" placeholder="0.00" required autofocus /></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td class="name">Customer Name</td>
                        <td class="value">
                            <input type="text" name="customer_first_name" value="" placeholder="First Name" size="12" />
                            <input type="text" name="customermi" value="" placeholder="MI" size="1" /> <br/>
                            <input type="text" name="customer_last_name" value="" placeholder="Last Name" size="12" />
                        </td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td class="name">Email</td>
                        <td class="value"><input type="text" name="payee_receipt_email" value="" placeholder="" /></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td class="name">Phone</td>
                        <td class="value"><input type="text" name="payee_phone_number" value="" placeholder="" /></td>
                    </tr>

                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td class="name">Customer ID#</td>
                        <td class="value"><input type="text" name="customer_id" value="" placeholder="" /></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td class="name">Invoice ID#</td>
                        <td class="value"><input type="text" name="invoice_number" value="" placeholder="" /></td>
                    </tr>
                </table>
            </fieldset>

            <fieldset style="display: inline-block;" class="show-on-payment-method-keyed show-on-payment-method-swipe">
                <legend>Cardholder Information</legend>
                <table class="table-transaction-charge themed">
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?> required">
                        <td class="name">First Name</td>
                        <td class="value"><input type="text" name="payee_first_name" value="" placeholder="" required /></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?> required">
                        <td class="name">Last Name</td>
                        <td class="value"><input type="text" name="payee_last_name" value="" placeholder="" required /></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td class="name">Billing Zipcode</td>
                        <td class="value"><input type="text" name="payee_zipcode" value="" placeholder="" /></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?> required">
                        <td class="name">Card Number</td>
                        <td class="value"><input type="text" name="card_number" value="" placeholder="" required /></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?> required">
                        <td class="name">Card Type</td>
                        <td class="value">
                            <select name="card_type" required>
                                <option value="">Choose an option</option>
                                <option>Visa</option>
                                <option>MasterCard</option>
                                <option>Amex</option>
                                <option>Discover</option>
                            </select>
                        </td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td class="name">CVV</td>
                        <td class="value"><input type="text" name="card_cvv2" value="" placeholder="" size="4" /></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?> required">
                        <td class="name">Expiration</td>
                        <td class="value">
                            <select name='card_exp_month' id='expireMM' required>
                                <option value=''>Month</option>
                                <option value='01'>Janaury</option>
                                <option value='02'>February</option>
                                <option value='03'>March</option>
                                <option value='04'>April</option>
                                <option value='05'>May</option>
                                <option value='06'>June</option>
                                <option value='07'>July</option>
                                <option value='08'>August</option>
                                <option value='09'>September</option>
                                <option value='10'>October</option>
                                <option value='11'>November</option>
                                <option value='12'>December</option>
                            </select>
                            <select name='card_exp_year' id='expireYY' required>
                                <option value=''>Year</option>
                                <option value='16'>2016</option>
                                <option value='17'>2017</option>
                                <option value='18'>2018</option>
                                <option value='19'>2019</option>
                                <option value='20'>2020</option>
                                <option value='21'>2021</option>
                                <option value='22'>2022</option>
                            </select>
                        </td>
                    </tr>

                </table>
            </fieldset>

            <fieldset style="display: inline-block;" class="show-on-payment-method-check">
                <legend>e-Check Information</legend>
                <table class="table-transaction-charge themed">
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?> required">
                        <td class="name">Account Name</td>
                        <td class="value"><input type="text" name="check_account_name" value="" placeholder="" required /></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?> required">
                        <td class="name">Account Type</td>
                        <td class="value">
                            <select name="check_type" required>
                                <option value="">Choose an option</option>
                                <option>Checking</option>
                                <option>Savings</option>
                            </select>
                        </td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?> required">
                        <td class="name">Account Number</td>
                        <td class="value"><input type="text" name="check_account_number" value="" placeholder="" required /></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?> required">
                        <td class="name">Routing Number</td>
                        <td class="value"><input type="text" name="check_routing_number" value="" placeholder="" required /></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td class="name">Check Type</td>
                        <td class="value">
                            <select name="check_type" required>
                                <option value="">Choose an option</option>
                                <option>Personal</option>
                                <option>Business</option>
                            </select>
                        </td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?> required">
                        <td class="name">Check Number</td>
                        <td class="value"><input type="text" name="check_number" value="" placeholder="" /></td>
                    </tr>

                </table>
            </fieldset>

            <hr />

            <fieldset style="display: inline-block;" class="show-on-merchant-selected">
                <legend>Submit Payment</legend>
                <table class="table-transaction-charge themed">
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td class="name">Convenience Fee</td>
                        <td class="value"><input type="text" name="convenience_fee_total" value="$0.00" disabled="disabled" /></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td class="name">Total Charge Amount</td>
                        <td class="value"><input type="text" name="total_amount" value="$0.00" disabled="disabled" /></td>
                    </tr>
<!--                    <tr class="row---><?php //echo ($odd=!$odd)?'odd':'even';?><!--">-->
<!--                        <td class="name">Method</td>-->
<!--                        <td class="value"><input type="text" name="entry_method" value="Keyed" disabled="disabled" /></td>-->
<!--                    </tr>-->
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td class="name">Submit</td>
                        <td class="value"><input type="submit" value="Pay Now" class="large" /></td>
                    </tr>
                </table>
            </fieldset>

        </form>
    </section>