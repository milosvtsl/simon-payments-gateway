<?php
use Integration\Model\IntegrationRow;
use User\Session\SessionManager;
use Merchant\Model\MerchantRow;
use Order\Model\OrderRow;
/**
 * @var \Transaction\View\ChargeView $this
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

    <article class="themed">

        <section class="content">
            <!-- Bread Crumbs -->
            <aside class="bread-crumbs">
                <a href="order" class="nav_transaction">Transactions</a>
                <a href="transaction/charge.php" class="nav_transaction_charge">New Charge</a>
            </aside>
            <?php if($this->hasMessage()) echo "<h5>", $this->getMessage(), "</h5>"; ?>

            <form name="form-transaction-charge" class=" themed" method="POST">
                <input type="hidden" name="integration_id" value="" />
                <input type="hidden" name="convenience_fee_flat" value="" />
                <input type="hidden" name="convenience_fee_limit" value="" />
                <input type="hidden" name="convenience_fee_variable_rate" value="" />

                <fieldset class="float-left-on-layout-horizontal">
                    <legend>Choose a Merchant</legend>
                    <table class="table-choose-merchant themed" style="float: left;">
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?> required">
                            <td>
                                <select name="merchant_id" class="" required autofocus>
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
                                            $testing = $Integration->getAPIType() === IntegrationRow::ENUM_API_TYPE_TESTING;

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
                                                    ( $testing || $SessionUser->hasAuthority('ROLE_ADMIN')
                                                    ? " (" . $Integration->getName() . ")" : ''),
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
                            </td>
                        </tr>
                    </table>
                </fieldset>

                <fieldset class="show-on-merchant-selected">
                    <legend>Choose a Payment Method</legend>
                    <table class="table-payment-method themed" style="float: left;">
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?> required">
                            <td>
                                <select name="entry_mode" class="" required autofocus>
            <!--                        <option value="">Choose a method</option>-->
                                    <option value="Keyed" <?php echo @$LASTPOST['entry_mode'] == 'Keyed' ? 'selected="selected"' : ''?>>Keyed Card</option>
                                    <option value="Swipe" <?php echo @$LASTPOST['entry_mode'] == 'Swipe' ? 'selected="selected"' : ''?>>Swipe Card</option>
                                    <option value="Check" <?php echo @$LASTPOST['entry_mode'] == 'Check' ? 'selected="selected"' : ''?>>e-Check</option>
                                </select>
                            </td>
                        </tr>
                    </table>
                </fieldset>

                <br />

                <fieldset class="show-on-merchant-selected float-left-on-layout-horizontal">
                    <legend>Customer Fields</legend>
                    <table class="table-transaction-charge themed" style="float: left;">
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?> required">
                            <td class="name">Payment Amount</td>
                            <td>
                                <input type="text" name="amount" value="<?php echo @$LASTPOST['amount']; ?>"  size="6" placeholder="x.xx" required autofocus />
                            </td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Customer Name</td>
                            <td>
                                <input type="text" name="customer_first_name" value="<?php echo @$LASTPOST['customer_first_name']; ?>" placeholder="First Name" size="12" />
                                <input type="text" name="customermi" value="<?php echo @$LASTPOST['customermi']; ?>" placeholder="MI" size="1" /> <br/>
                                <input type="text" name="customer_last_name" value="<?php echo @$LASTPOST['customer_last_name']; ?>" placeholder="Last Name" size="12" />
                            </td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Email</td>
                            <td><input type="text" name="payee_reciept_email" value="<?php echo @$LASTPOST['payee_reciept_email']; ?>" placeholder="xxx@xxx.xxx" /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Phone</td>
                            <td><input type="text" name="payee_phone_number" value="<?php echo @$LASTPOST['payee_phone_number']; ?>" placeholder="xxx-xxx-xxxx" /></td>
                        </tr>

                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Billing Address</td>
                            <td>
                                <input type="text" name="payee_address" value="<?php echo @$LASTPOST['payee_address']; ?>" placeholder="Address" />
                                <br/>
                                <input type="text" name="payee_address2" value="<?php echo @$LASTPOST['payee_address2']; ?>" placeholder="Address #2" />
                            </td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Billing Zip/State</td>
                            <td>
                                <input type="text" name="payee_zipcode" value="<?php echo @$LASTPOST['payee_zipcode']; ?>" placeholder="ZipCode" size="6" class="zip-lookup-field-zipcode" />
                                <select name="payee_state" style="width: 7em;" class='zip-lookup-field-state-short'>
                                    <option value="">State</option>
                                    <?php
                                    foreach(\System\Arrays\Locations::$STATES as $code => $name)
                                        echo "\n\t<option value='", $code, "' ",
                                        ($code === @$LASTPOST['payee_state'] ? ' selected="selected"' : ''),
                                        ">", $name, "</option>";
                                    ?>
                                </select>
                            </td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Billing City</td>
                            <td>
                                <input type="text" name="payee_city" size="10" value="<?php echo @$LASTPOST['payee_city']; ?>" placeholder="City" class='zip-lookup-field-city-title-case' />
                            </td>
                        </tr>

                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Customer&nbsp;ID#</td>
                            <td><input type="text" name="customer_id" value="<?php echo @$LASTPOST['customer_id']; ?>" placeholder="Optional Customer ID" /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Invoice&nbsp;ID#</td>
                            <td><input type="text" name="invoice_number" value="<?php echo @$LASTPOST['invoice_number']; ?>" placeholder="Optional Invoice Number" /></td>
                        </tr>
                    </table>
                </fieldset>

                <fieldset class="form-payment-method-credit show-on-merchant-selected show-on-payment-method-keyed show-on-payment-method-swipe">
                    <legend>Cardholder Information</legend>
                    <table class="table-transaction-charge themed">
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?> required">
                            <td class="name">First Name</td>
                            <td><input type="text" name="payee_first_name" value="<?php echo @$LASTPOST['payee_first_name']; ?>" placeholder="First Name" required /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?> required">
                            <td class="name">Last Name</td>
                            <td><input type="text" name="payee_last_name" value="<?php echo @$LASTPOST['payee_last_name']; ?>" placeholder="Last Name" required /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?> required">
                            <td class="name">Card Number</td>
                            <td><input type="text" name="card_number" value="<?php echo @$LASTPOST['card_number']; ?>" placeholder="xxxxxxxxxxxxxxxx" required /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?> required">
                            <td class="name">Card Type</td>
                            <td>
                                <select name="card_type" required>
                                    <option value="">Choose an option</option>
                                    <option <?php echo @$LASTPOST['card_type'] == 'Visa' ? 'selected="selected"' : ''?> title="Visa">Visa</option>
                                    <option <?php echo @$LASTPOST['card_type'] == 'MasterCard' ? 'selected="selected"' : ''?> title="MasterCard">MasterCard</option>
                                    <option <?php echo @$LASTPOST['card_type'] == 'Amex' ? 'selected="selected"' : ''?> title="Amex">Amex</option>
                                    <option <?php echo @$LASTPOST['card_type'] == 'Discover' ? 'selected="selected"' : ''?> title="Discover">Discover</option>
                                </select>
                            </td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">CVV</td>
                            <td><input type="number" name="card_cvv2" value="<?php echo @$LASTPOST['card_cvv2']; ?>" placeholder="xxxx" autocomplete="off" style="width: 4em;" /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?> required">
                            <td class="name">Expiration</td>
                            <td>
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
                                    <?php for($i=16; $i<64; $i++) { ?>
                                    <option <?php echo @$LASTPOST['card_exp_year'] == $i ? 'selected="selected"' : ''?> value='<?php echo $i; ?>'>20<?php echo $i; ?></option>
                                    <?php } ?>
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


                <fieldset class="show-on-payment-method-swipe show-on-payment-method-keyed show-on-merchant-selected" >
                    <legend class="alert reader-status">Card Swipe Ready</legend>
                    <table class="table-payment-method-swipe themed" style="float: left;">
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?> required">
                            <td>
                                <input type="password" name="card_track" size="103" value="<?php echo @$LASTPOST['card_track']; ?>" />
                            </td>
                        </tr>
                    </table>
                </fieldset>

                <fieldset class="form-payment-method-check show-on-payment-method-check">
                    <legend>e-Check Information</legend>
                    <table class="table-transaction-charge themed">
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?> required">
                            <td class="name">Account Name</td>
                            <td><input type="text" name="check_account_name" value="<?php echo @$LASTPOST['check_account_name']; ?>" placeholder="" required /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?> required">
                            <td class="name">Account Type</td>
                            <td>
                                <select name="check_account_type" required>
                                    <option value="">Choose an option</option>
                                    <option <?php echo @$LASTPOST['check_account_type'] == 'Checking' ? 'selected="selected"' : ''?>>Checking</option>
                                    <option <?php echo @$LASTPOST['check_account_type'] == 'Savings' ? 'selected="selected"' : ''?>>Savings</option>
                                </select>
                            </td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?> required">
                            <td class="name">Account Number</td>
                            <td><input type="text" name="check_account_number" value="<?php echo @$LASTPOST['check_account_number']; ?>" placeholder="" required /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?> required">
                            <td class="name">Routing Number</td>
                            <td><input type="text" name="check_routing_number" value="<?php echo @$LASTPOST['check_routing_number']; ?>" placeholder="" required /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Check Type</td>
                            <td>
                                <select name="check_type" required>
                                    <option value="">Choose an option</option>
                                    <option <?php echo @$LASTPOST['check_type'] == 'Personal' ? 'selected="selected"' : ''?>>Personal</option>
                                    <option <?php echo @$LASTPOST['check_type'] == 'Business' ? 'selected="selected"' : ''?>>Business</option>
                                </select>
                            </td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?> required">
                            <td class="name">Check Number</td>
                            <td><input type="text" name="check_number" value="<?php echo @$LASTPOST['check_number']; ?>" placeholder="" /></td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <div class="check-image"></div>
                            </td>
                        </tr>
                    </table>
                </fieldset>

                <br />


                <fieldset class="show-on-merchant-selected show-on-payment-method-selected">
                    <legend>Submit Payment</legend>


                    <table class="table-transaction-charge themed" style="float: left; width: 48%;">
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Recur Count</td>
                            <td>
                                <select name='recur_count'>
                                    <option value="0">Disabled</option>
                                    <?php
                                    for($i=1; $i<=99; $i++)
                                        echo "\n\t<option ",
                                        @$LASTPOST['recur_count'] == $i ? 'selected="selected"' : '',
                                        ">", $i, "</option>";
                                    ?>
                                </select>
                            </td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Recur Amount
                                <br />
                                <span style="font-size: x-small; color: grey">(If different from Payment Amount)</span>
                            </td>
                            <td class="value"><input type="text" name="recur_amount" placeholder="x.xx" size="6" value="<?php echo @$LASTPOST['recur_amount']; ?>" required="required"/></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Recur Frequency</td>
                            <td>
                                <select name='recur_frequency'>
                                    <?php
                                    if(empty($LASTPOST['recur_frequency']))
                                        $LASTPOST['recur_frequency'] = 'Monthly';
                                    foreach(OrderRow::$ENUM_RUN_FREQUENCY as $type => $name)
                                        echo "\n\t<option value='", $type, "'",
                                        @$LASTPOST['recur_frequency'] === $type ? ' selected="selected"' : '',
                                        ">", $name, "</option>";
                                    ?>
                                </select>
                            </td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">First Recur Date</td>
                            <td><input type="date" name="recur_next_date" value="<?php echo @$LASTPOST['recur_next_date']; ?>" required="required"/></td>
                        </tr>
                    </table>

                    <table class="table-transaction-charge themed" style="width: 48%;">
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Convenience Fee</td>
                            <td><input type="text" size="9" name="convenience_fee_total" value="$0.00" disabled="disabled" /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Total Charge Amount</td>
                            <td><input type="text" size="9" name="total_amount" value="$0.00" disabled="disabled" /></td>
                        </tr>
                        <!--                    <tr class="row---><?php //echo ($odd=!$odd)?'odd':'even';?><!--">-->
                        <!--                        <td class="name">Method</td>-->
                        <!--                        <td><input type="text" name="entry_method" value="Keyed" disabled="disabled" /></td>-->
                        <!--                    </tr>-->
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Submit</td>
                            <td>
                                <input type="submit" value="Pay Now" class="themed" />
                            </td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Reset</td>
                            <td>
                                <input type="reset" value="Reset" class="themed" todo="clear all" />
                            </td>
                        </tr>
                    </table>
                </fieldset>

            </form>
        </section>
    </article>