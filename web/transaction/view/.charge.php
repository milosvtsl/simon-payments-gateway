<?php
/**
 * @var \Transaction\View\TransactionView $this
 **/
$odd = false;
?>
    <section class="message">
        <h1>Charge a card</h1>

        <?php if($this->hasException()) { ?>
            <h5><?php echo $this->hasException(); ?></h5>

        <?php } else if ($this->hasSessionMessage()) { ?>
            <h5><?php echo $this->popSessionMessage(); ?></h5>

        <?php } ?>
    </section>

    <section class="content">
        <script src="transaction/view/assets/charge.js"></script>
        <form name="form-transaction-charge" class=" themed" method="POST">
            <fieldset class="action-fields">
                <legend>Actions</legend>
                <a href="transaction?" class="button">Transaction List</a>
                <a href="transaction/charge.php?" class="button">Charge</a>
            </fieldset>
            <fieldset style="float:left;">
                <legend>Charge Fields</legend>
                <table class="table-transaction-charge themed">
                    <tr>
                        <th>Field</th>
                        <th>Value</th>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?> required">
                        <td class="name">Payment Amount</td>
                        <td class="value"><input type="text" name="amount" value="" size="10" placeholder="0.00" required /></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?> required">
                        <td class="name">Merchant</td>
                        <td class="value">
                            <select name="merchant_id" class="">
                                <option value="">Choose a Merchant</option>
                                <?php
                                $MerchantQuery = \Merchant\Model\MerchantRow::queryAll();
                                foreach($MerchantQuery as $Merchant)
                                    /** @var \Merchant\Model\MerchantRow $Merchant */
                                    echo "\n\t\t\t\t\t\t\t<option value='", $Merchant->getID(), "'>", $Merchant->getShortName(), "</option>";
                                ?>
                            </select>
                        </td>
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
                        <td class="name">Customer ID#</td>
                        <td class="value"><input type="text" name="customer_id" value="" placeholder="" /></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td class="name">Invoice ID#</td>
                        <td class="value"><input type="text" name="invoice_number" value="" placeholder="" /></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?> required">
                        <td class="name">Cardholder First Name</td>
                        <td class="value"><input type="text" name="payee_first_name" value="" placeholder="" required /></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?> required">
                        <td class="name">Cardholder Last Name</td>
                        <td class="value"><input type="text" name="payee_last_name" value="" placeholder="" required /></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td class="name">Billing Zipcode</td>
                        <td class="value"><input type="text" name="payee_zipcode" value="" placeholder="" /></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td class="name">Email</td>
                        <td class="value"><input type="text" name="payee_receipt_email" value="" placeholder="" /></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td class="name">Phone</td>
                        <td class="value"><input type="text" name="payee_phone_number" value="" placeholder="" /></td>
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
            <fieldset style="display: inline-block;">
                <legend>Submit Payment</legend>
                <table class="table-transaction-charge themed">
                    <caption class="alert reader-status">Card Swipe Ready!</caption>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td class="name">Total Charge Amount</td>
                        <td class="value"><input type="text" name="total_amount" value="$0.00" disabled="disabled" /></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td class="name">Method</td>
                        <td class="value"><input type="text" name="entry_method" value="Keyed" disabled="disabled" /></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td class="name">Submit</td>
                        <td class="value"><input type="submit" value="Pay Now" class="large" /></td>
                    </tr>
                </table>
            </fieldset>
        </form>
    </section>