<?php
use Integration\Model\IntegrationRow;
use User\Session\SessionManager;
use Merchant\Model\MerchantRow;
use Order\Model\OrderRow;
/**
 * @var \Transaction\View\TransactionView $this
 **/
$odd = false;
$SessionManager = new SessionManager();
$SessionUser = $SessionManager->getSessionUser();

$LASTPOST = array();
if(isset($_SESSION['transaction/charge.php']))
    $LASTPOST = $_SESSION['transaction/charge.php'];

$button_current = 'charge';
include dirname(dirname(__DIR__)) . '/user/view/.dashboard.nav.php';
?>

<!-- Bread Crumbs -->
    <aside class="bread-crumbs">
        <a href="/" class="nav_home">Home</a>
        <a href="order" class="nav_transaction">Orders</a>
        <a href="transaction/charge.php" class="nav_transaction_charge">New Charge</a>
    </aside>

    <section class="content">
        <?php if($this->hasException()) echo "<h5>", $this->getException()->getMessage(), "</h5>"; ?>

        <form name="form-transaction-charge" class=" themed" method="POST">
            <input type="hidden" name="integration_id" value="" />
            <input type="hidden" name="convenience_fee_flat" value="" />
            <input type="hidden" name="convenience_fee_limit" value="" />
            <input type="hidden" name="convenience_fee_variable_rate" value="" />

            <fieldset class="inline-on-merchant-selected">
                <legend>Choose a Merchant</legend>
                <select name="merchant_id" class="" autofocus>
                    <?php
                    if($SessionUser->hasAuthority('ROLE_ADMIN')) {
                        echo '<option value="">Choose a Merchant (as Admin ', $SessionUser->getUsername(), ')</option>';
                        $MerchantQuery = MerchantRow::queryAll();
                    } else {
                        $MerchantQuery = $SessionUser->queryUserMerchants();
                    }
                    foreach ($MerchantQuery as $Merchant) {
                        /** @var MerchantRow $Merchant */
                        foreach ($Merchant->getMerchantIdentities() as $MerchantIdentity) {
                            $reason = null;
                            $Integration = $MerchantIdentity->getIntegrationRow();
                            if($Integration->getAPIType() === IntegrationRow::ENUM_API_TYPE_DISABLED)
                                continue;
                            
                            if($MerchantIdentity->isProvisioned($reason)) {
                                echo "\n\t\t\t\t\t\t\t<option",
                                " data-integration-id='", $Integration->getID(), "'",
                                " data-form-class='", $Merchant->getChargeFormClasses(), "'",
                                " data-convenience-fee-flat='", $Merchant->getFeeFlat(), "'",
                                " data-convenience-fee-limit='", $Merchant->getFeeLimit(), "'",
                                " data-convenience-fee-variable-rate='", $Merchant->getFeeVariable(), "'",
                                (@$LASTPOST['merchant_id'] == $Merchant->getID() ? 'selected="selected" ' : ''),
                                " value='", $Merchant->getID(), "'>",
                                    $Merchant->getShortName(),
                                    " (", $Integration->getName(), ")",
                                "</option>";
                            } else {
                                echo "\n\t\t\t\t\t\t\t<!--option disabled='disabled'>",
                                    $Merchant->getShortName(),
                                    " (", $Integration->getName(), ")",
                                '</option-->';
                            }
                        }
                    }
                    ?>
                </select>
            </fieldset>

            <fieldset style="display: inline-block;" class="show-on-merchant-selected">
                <legend>Choose a Payment Method</legend>
                <select name="entry_mode" class="" autofocus>
                    <option value="">Choose a method</option>
                    <option value="keyed" <?php echo @$LASTPOST['entry_mode'] == 'keyed' ? 'selected="selected"' : ''?>>Keyed Card</option>
                    <option value="swipe" <?php echo @$LASTPOST['entry_mode'] == 'swipe' ? 'selected="selected"' : ''?>>Swipe Card</option>
                    <option value="check" <?php echo @$LASTPOST['entry_mode'] == 'check' ? 'selected="selected"' : ''?>>e-Check</option>
                </select>
            </fieldset>

            <fieldset style="display: inline-block" class="show-on-payment-method-swipe">
                <legend class="alert reader-status">Card Swipe Ready</legend>
                <input type="text" name="card_track" size="30" value="<?php echo @$LASTPOST['card_track']; ?>" />
            </fieldset>

            <hr/>

            <fieldset style="display: inline-block;" class="show-on-merchant-selected">
                <legend>Customer Fields</legend>
                <table class="table-transaction-charge themed" style="float: left;">
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?> required">
                        <td class="name">Payment Amount</td>
                        <td class="value">
                            <input type="text" name="amount" value="<?php echo @$LASTPOST['amount']; ?>"  size="10" placeholder="x.xx" required autofocus />
                        </td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td class="name">Customer Name</td>
                        <td class="value">
                            <input type="text" name="customer_first_name" value="<?php echo @$LASTPOST['customer_first_name']; ?>" placeholder="First Name" size="12" />
                            <input type="text" name="customermi" value="<?php echo @$LASTPOST['customermi']; ?>" placeholder="MI" size="1" /> <br/>
                            <input type="text" name="customer_last_name" value="<?php echo @$LASTPOST['customer_last_name']; ?>" placeholder="Last Name" size="12" />
                        </td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td class="name">Email</td>
                        <td class="value"><input type="text" name="payee_reciept_email" value="<?php echo @$LASTPOST['payee_reciept_email']; ?>" placeholder="xxx@xxx.xxx" /></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td class="name">Phone</td>
                        <td class="value"><input type="text" name="payee_phone_number" value="<?php echo @$LASTPOST['payee_phone_number']; ?>" placeholder="xxx-xxx-xxxx" /></td>
                    </tr>

                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td class="name">Customer&nbsp;ID#</td>
                        <td class="value"><input type="text" name="customer_id" value="<?php echo @$LASTPOST['customer_id']; ?>" placeholder="Optional Customer ID" /></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td class="name">Invoice&nbsp;ID#</td>
                        <td class="value"><input type="text" name="invoice_number" value="<?php echo @$LASTPOST['invoice_number']; ?>" placeholder="Optional Invoice Number" /></td>
                    </tr>
                </table>
            </fieldset>

            <fieldset style="display: inline-block;" class="form-payment-method-credit show-on-payment-method-keyed show-on-payment-method-swipe">
                <legend>Cardholder Information</legend>
                <table class="table-transaction-charge themed">
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?> required">
                        <td class="name">First Name</td>
                        <td class="value"><input type="text" name="payee_first_name" value="<?php echo @$LASTPOST['payee_first_name']; ?>" placeholder="First Name" required /></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?> required">
                        <td class="name">Last Name</td>
                        <td class="value"><input type="text" name="payee_last_name" value="<?php echo @$LASTPOST['payee_last_name']; ?>" placeholder="Last Name" required /></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td class="name">Address</td>
                        <td class="value">
                            <input type="text" name="payee_address" value="<?php echo @$LASTPOST['payee_address']; ?>" placeholder="Address" />
                            <br/>
                            <input type="text" name="payee_address2" value="<?php echo @$LASTPOST['payee_address2']; ?>" placeholder="Address #2" />
                        </td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td class="name">Billing Zipcode</td>
                        <td class="value"><input type="text" name="payee_zipcode" value="<?php echo @$LASTPOST['payee_zipcode']; ?>" placeholder="ZipCode" size="7" /></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?> required">
                        <td class="name">Card Number</td>
                        <td class="value"><input type="text" name="card_number" value="<?php echo @$LASTPOST['card_number']; ?>" placeholder="xxxxxxxxxxxxxxxx" required /></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?> required">
                        <td class="name">Card Type</td>
                        <td class="value">
                            <select name="card_type" required>
                                <option value="">Choose an option</option>
                                <option <?php echo @$LASTPOST['card_type'] == 'Visa' ? 'selected="selected"' : ''?>>Visa</option>
                                <option <?php echo @$LASTPOST['card_type'] == 'MasterCard' ? 'selected="selected"' : ''?>>MasterCard</option>
                                <option <?php echo @$LASTPOST['card_type'] == 'Amex' ? 'selected="selected"' : ''?>>Amex</option>
                                <option <?php echo @$LASTPOST['card_type'] == 'Discover' ? 'selected="selected"' : ''?>>Discover</option>
                            </select>
                        </td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td class="name">CVV</td>
                        <td class="value"><input type="text" name="card_cvv2" value="<?php echo @$LASTPOST['card_cvv2']; ?>" placeholder="xxxx" size="4" /></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?> required">
                        <td class="name">Expiration</td>
                        <td class="value">
                            <select name='card_exp_month' id='expireMM' required>
                                <option value=''>Month</option>
                                <option <?php echo @$LASTPOST['card_exp_month'] == '01' ? 'selected="selected"' : ''?> value='01'>January</option>
                                <option <?php echo @$LASTPOST['card_exp_month'] == '02' ? 'selected="selected"' : ''?> value='02'>February</option>
                                <option <?php echo @$LASTPOST['card_exp_month'] == '03' ? 'selected="selected"' : ''?> value='03'>March</option>
                                <option <?php echo @$LASTPOST['card_exp_month'] == '04' ? 'selected="selected"' : ''?> value='04'>April</option>
                                <option <?php echo @$LASTPOST['card_exp_month'] == '05' ? 'selected="selected"' : ''?> value='05'>May</option>
                                <option <?php echo @$LASTPOST['card_exp_month'] == '06' ? 'selected="selected"' : ''?> value='06'>June</option>
                                <option <?php echo @$LASTPOST['card_exp_month'] == '07' ? 'selected="selected"' : ''?> value='07'>July</option>
                                <option <?php echo @$LASTPOST['card_exp_month'] == '08' ? 'selected="selected"' : ''?> value='08'>August</option>
                                <option <?php echo @$LASTPOST['card_exp_month'] == '09' ? 'selected="selected"' : ''?> value='09'>September</option>
                                <option <?php echo @$LASTPOST['card_exp_month'] == '10' ? 'selected="selected"' : ''?> value='10'>October</option>
                                <option <?php echo @$LASTPOST['card_exp_month'] == '11' ? 'selected="selected"' : ''?> value='11'>November</option>
                                <option <?php echo @$LASTPOST['card_exp_month'] == '12' ? 'selected="selected"' : ''?> value='12'>December</option>
                            </select>
                            <select name='card_exp_year' id='expireYY' required>
                                <option value=''>Year</option>
                                <option <?php echo @$LASTPOST['card_exp_year'] == '16' ? 'selected="selected"' : ''?> value='16'>2016</option>
                                <option <?php echo @$LASTPOST['card_exp_year'] == '17' ? 'selected="selected"' : ''?> value='17'>2017</option>
                                <option <?php echo @$LASTPOST['card_exp_year'] == '18' ? 'selected="selected"' : ''?> value='18'>2018</option>
                                <option <?php echo @$LASTPOST['card_exp_year'] == '19' ? 'selected="selected"' : ''?> value='19'>2019</option>
                                <option <?php echo @$LASTPOST['card_exp_year'] == '20' ? 'selected="selected"' : ''?> value='20'>2020</option>
                                <option <?php echo @$LASTPOST['card_exp_year'] == '21' ? 'selected="selected"' : ''?> value='21'>2021</option>
                                <option <?php echo @$LASTPOST['card_exp_year'] == '22' ? 'selected="selected"' : ''?> value='22'>2022</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <div class="credit-image"></div>
                        </td>
                    </tr>
                </table>
            </fieldset>

            <fieldset style="display: inline-block;" class="form-payment-method-check show-on-payment-method-check">
                <legend>e-Check Information</legend>
                <table class="table-transaction-charge themed">
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?> required">
                        <td class="name">Account Name</td>
                        <td class="value"><input type="text" name="check_account_name" value="<?php echo @$LASTPOST['check_account_name']; ?>" placeholder="" required /></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?> required">
                        <td class="name">Account Type</td>
                        <td class="value">
                            <select name="check_account_type" required>
                                <option value="">Choose an option</option>
                                <option <?php echo @$LASTPOST['check_account_type'] == 'Checking' ? 'selected="selected"' : ''?>>Checking</option>
                                <option <?php echo @$LASTPOST['check_account_type'] == 'Savings' ? 'selected="selected"' : ''?>>Savings</option>
                            </select>
                        </td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?> required">
                        <td class="name">Account Number</td>
                        <td class="value"><input type="text" name="check_account_number" value="<?php echo @$LASTPOST['check_account_number']; ?>" placeholder="" required /></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?> required">
                        <td class="name">Routing Number</td>
                        <td class="value"><input type="text" name="check_routing_number" value="<?php echo @$LASTPOST['check_routing_number']; ?>" placeholder="" required /></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td class="name">Check Type</td>
                        <td class="value">
                            <select name="check_type" required>
                                <option value="">Choose an option</option>
                                <option <?php echo @$LASTPOST['check_type'] == 'Personal' ? 'selected="selected"' : ''?>>Personal</option>
                                <option <?php echo @$LASTPOST['check_type'] == 'Business' ? 'selected="selected"' : ''?>>Business</option>
                            </select>
                        </td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?> required">
                        <td class="name">Check Number</td>
                        <td class="value"><input type="text" name="check_number" value="<?php echo @$LASTPOST['check_number']; ?>" placeholder="" /></td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <div class="check-image"></div>
                        </td>
                    </tr>
                </table>
            </fieldset>

            <hr />

            <fieldset style="display: inline-block;" class="form-payment-recurring show-on-merchant-selected">
                <legend>Recurring Information</legend>
                <table class="table-transaction-charge themed">
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td class="name">Enable Recurring</td>
                        <td class="value"><input type="checkbox" name="recur_enable" value="1"
                                <?php if(@$LASTPOST['recur_enable']) echo 'checked="checked"'; ?>
                                /></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td class="name">Run until canceled</td>
                        <td class="value"><input type="checkbox" name="recur_until_cancel" value="1" /></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td class="name">First Recur Date</td>
                        <td class="value"><input type="date" name="recur_start_date" value="<?php echo date('Y-m-d', time()+24*60*60*30); ?>"/></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td class="name">Recur Count</td>
                        <td class="value">
                            <select name='recur_count'>
                                <?php
                                if(empty($LASTPOST['recur_count']))
                                    $LASTPOST['recur_count'] = '3';
                                for($i=1; $i<=24; $i++)
                                    echo "\n\t<option ",
                                    $LASTPOST['recur_count'] == $i ? 'selected="selected"' : '',
                                    ">", $i, "</option>";
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td class="name">Recur Frequency</td>
                        <td class="value">
                            <select name='recur_frequency'>
                                <?php
                                if(empty($LASTPOST['recur_frequency']))
                                    $LASTPOST['recur_frequency'] = 'Monthly';
                                foreach(OrderRow::$ENUM_RUN_FREQUENCY as $type => $name)
                                    echo "\n\t<option ",
                                        @$LASTPOST['recur_frequency'] === $type ? 'selected="selected"' : '',
                                        ">", $name, "</option>";
                                ?>
                            </select>
                        </td>
                    </tr>
                </table>
            </fieldset>

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
                        <td class="value">
                            <input type="submit" value="Pay Now" class="themed" />
                        </td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td class="name">Reset</td>
                        <td class="value">
                            <input type="reset" value="Reset" class="themed" />
                        </td>
                    </tr>
                </table>
            </fieldset>

        </form>
    </section>