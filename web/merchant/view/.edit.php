<?php
use Merchant\Model\MerchantRow;

/**
 * @var \Merchant\View\MerchantView $this
 * @var PDOStatement $MerchantQuery
 **/
$Merchant = $this->getMerchant();
$odd = false;
$action_url = '/merchant/index.php?id=' . $Merchant->getID() . '&action=';


$Theme = $this->getTheme();
$Theme->addPathURL('merchant',      'Merchants');
$Theme->addPathURL($action_url,     $Merchant->getShortName());
$Theme->addPathURL($action_url.'edit',     'Edit');
$Theme->renderHTMLBodyHeader();
$Theme->printHTMLMenu('merchant-edit', $action_url);
?>

    <article class="themed">

        <section class="content">


            <?php if($SessionManager->hasMessage()) echo "<h5>", $SessionManager->popMessage(), "</h5>"; ?>

            <form name="form-merchant-edit" class="themed" method="POST" action="<?php echo $action_url; ?>edit">
                <input type="hidden" name="id" value="<?php echo $Merchant->getID(); ?>" />
                <input type="hidden" name="action" value="edit" />
                <fieldset>
                    <div class="legend">Edit Merchant #<?php echo $Merchant->getID(); ?></div>

                    <div class="page-buttons order-page-buttons hide-on-print">
                        <a href="<?php echo $action_url; ?>view" class="page-button page-button-view">
                            <div class="app-button large app-button-view" ></div>
                            View
                        </a>
                        <a href="<?php echo $action_url; ?>provision" class="page-button page-button-provision">
                            <div class="app-button large app-button-provision" ></div>
                            Provision
                        </a>
                    </div>

                    <hr/>


                    <table class="table-merchant-info themed small striped-rows" style="float: left; width: 49%;">
                        <tr>
                            <th colspan="2">Information</th>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Name</td>
                            <td><input type="text" name="name" size="24" value="<?php echo $Merchant->getName(); ?>" /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Short Name</td>
                            <td><input type="text" name="short_name" size="24" value="<?php echo $Merchant->getShortName(); ?>" /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Email</td>
                            <td><input type="text" name="main_email_id" size="24" value="<?php echo $Merchant->getMainEmailID(); ?>" /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">URL</td>
                            <td><input type="text" name="url" size="24" value="<?php echo $Merchant->getURL(); ?>" /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Merchant MCC Code</td>
                            <td>
                                <select name="mcc" style="width: 16em;" title="Select Merchant MCC Code">
                                    <?php
                                    foreach(\System\Arrays\Merchants::$MCC as $code=>$title)
                                        echo "<option value='", $code, "'",
                                        ($Merchant->getMerchantMCC() == $code ? ' selected="selected"' : ''),
                                        ">$code - $title</option>\n";
                                    ?>
                                </select>
                            </td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Status</td>
                            <td>
                                <select name="status_id">
                                    <?php
                                    foreach(MerchantRow::$ENUM_STATUS as $code=>$title)
                                        echo "<option value='", $code, "'",
                                        ($Merchant->getStatusID() === $code ? ' selected="selected"' : ''),
                                        ">", $title, "</option>\n";
                                    ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th colspan="2">Convenience Fee</th>
                        </tr>
                        <?php $odd = false; ?>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Enable</td>
                            <td><input type="checkbox" name="convenience_fee_enabled" <?php echo $Merchant->isConvenienceFeeEnabled() ? "checked='checked'" : ''; ?> /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Variable</td>
                            <td><input type="text" name="convenience_fee_variable" size="12" value="<?php echo $Merchant->getConvenienceFeeVariable(); ?>" /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Flat</td>
                            <td><input type="text" name="convenience_fee_flat" size="12" value="<?php echo $Merchant->getConvenienceFeeFlat(); ?>" /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Limit</td>
                            <td><input type="text" name="convenience_fee_limit" size="12" value="<?php echo $Merchant->getConvenienceFeeLimit(); ?>" /></td>
                        </tr>
                        <tr>
                            <th colspan="2">Batch</th>
                        </tr>
                        <?php $odd = false; ?>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Batch Close</td>
                            <td><?php echo $Merchant->getBatchTime(), ' ', $Merchant->getBatchTimeZone(); ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Open Date</td>
                            <td><input type="datetime-local" name="open_date" value="<?php echo date("Y-m-d\TH:i:s", strtotime($Merchant->getOpenDate())); ?>" /></td>
                        </tr>
                        <tr>
                            <th colspan="2">Accounts & IDs</th>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Store ID</td>
                            <td><input type="text" name="store_id" size="12" value="<?php echo $Merchant->getStoreID(); ?>" /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Discover Ext</td>
                            <td><input type="text" name="discover_external" size="24" value="<?php echo $Merchant->getDiscoverExt(); ?>" /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Amex Ext</td>
                            <td><input type="text" name="amex_external" size="24" value="<?php echo $Merchant->getAmexExt(); ?>" /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Agent Chain</td>
                            <td><input type="text" name="agent_chain" size="24" value="<?php echo $Merchant->getAgentChain(); ?>" /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Tax ID</td>
                            <td><input type="text" name="tax_id" size="24" value="<?php echo $Merchant->getTaxID(); ?>" /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Business Tax ID</td>
                            <td><input type="text" name="business_tax_id" size="24" value="<?php echo $Merchant->getBusinessTaxID(); ?>" /></td>
                        </tr>
                        <tr>
                            <th colspan="2">Business</th>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Business Type</td>
                            <td>
                                <select name="business_type">
                                    <?php
                                    foreach(MerchantRow::$ENUM_BUSINESS_TYPE as $code=>$title)
                                        echo "<option value='", $code, "'",
                                            ($Merchant->getBusinessType() === $code ? ' selected="selected"' : ''),
                                            ">", $title, "</option>\n";
                                    ?>
                                </select>
                            </td>
                        </tr>

                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Phone Number</td>
                            <td><input type="text" name="telephone" size="24" value="<?php echo $Merchant->getTelephone(); ?>" /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Address</td>
                            <td><input type="text" name="address1" size="24" value="<?php echo $Merchant->getAddress(); ?>" /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Address 2</td>
                            <td><input type="text" name="address2" size="24" value="<?php echo $Merchant->getAddress2(); ?>" /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">City</td>
                            <td><input type="text" name="city" size="24" value="<?php echo $Merchant->getCity(); ?>" /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">State</td>
                            <td>
                                <select name="state_id">
                                    <?php
                                    $StateQuery = \System\Model\StateRow::queryAll();
                                    foreach($StateQuery as $State)
                                        /** @var \System\Model\StateRow $State */
                                        echo "<option value='", $State->getID(), "'",
                                        ($State->getShortCode() === $Merchant->getRegionCode() ? ' selected="selected"' : ''),
                                        ">", $State->getName(), "</option>\n";
                                    ?>
                                </select>
                            </td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Zip</td>
                            <td><input type="text" name="zipcode" size="12" value="<?php echo $Merchant->getZipCode(); ?>" /></td>
                        </tr>

                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Country</td>
                            <td>
                                <select name="country" style="max-width: 16em;">
                                    <?php
                                    foreach(\System\Arrays\Locations::$COUNTRIES as $code => $description)
                                        if(strlen($code) === 3)
                                            echo "<option value='", $code, "'",
                                            ($code === $Merchant->getCountryCode() ? ' selected="selected"' : ''),
                                            ">", $description, "</option>\n";
                                    ?>
                                </select>
                            </td>
                        </tr>
                    </table>
                    <?php $odd = false; ?>
                    <table class="table-merchant-info themed small striped-rows" style="width: 49%">
                        <tr>
                            <th colspan="2">Contact Information</th>
                        </tr>
                        <?php $odd = false; ?>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Main Contact</td>
                            <td><input type="text" name="main_contact" value="<?php echo $Merchant->getMainContact(); ?>" /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Sale Rep</td>
                            <td><input type="text" name="sale_rep" value="<?php echo $Merchant->getSaleRep(); ?>" /></td>
                        </tr>

                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Title</td>
                            <td><input type="text" name="main_contact" value="<?php echo $Merchant->getTitle(); ?>" /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">DOB</td>
                            <td><input type="date" name="main_contact" value="<?php echo $Merchant->getDOB(); ?>" /></td>
                        </tr>

                        <tr>
                            <th colspan="2">Fraud Scrubbing</th>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Transaction High Limit</td>
                            <td><input type="text" name="fraud_high_limit" value="<?php echo $Merchant->getFraudHighLimit() ?: ''; ?>" placeholder="Maximum Transaction Amount"  /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Transaction Low Limit</td>
                            <td><input type="text" name="fraud_low_limit" value="<?php echo $Merchant->getFraudLowLimit() ?: ''; ?>" placeholder="Minimum Transaction Amount" /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Transaction High Monthly Limit</td>
                            <td><input type="text" name="fraud_high_monthly_limit" value="<?php echo $Merchant->getFraudHighMonthlyLimit() ?: ''; ?>" placeholder="Monthly Transaction Limit" /></td>
                        </tr>
                        <?php
                        foreach(MerchantRow::$FRAUD_FLAG_DESCRIPTIONS as $type => $description) {
                        ?>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name"><?php echo $description; ?></td>
                            <td>
                                <label style="display: block;">
                                <input type='checkbox' name="fraud_flags[<?php echo $type; ?>]" style="transform: scale(1.8); margin: 0.44em;"
                                       <?php echo $Merchant->hasFlag($type) ? ' checked' : ''; ?>
                                 title="<?php echo $description; ?>"/>
                                </label>
                            </td>
                        </tr>
                        <?php } ?>
                        <tr>
                            <th colspan="2">Payment Instrument</th>
                        </tr>
                        <?php $odd = false; ?>

                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Payment Type</td>
                            <td>
                                <select name="payout_type">
                                    <option value="">Choose a payout type</option>
                                    <?php
                                    foreach(MerchantRow::$ENUM_PAYOUT_TYPES as $type => $description)
                                        echo "<option value='", $type, "'",
                                        ($type === $Merchant->getPayoutType() ? ' selected="selected"' : ''),
                                        ">", $description, "</option>\n";
                                    ?>
                                </select>
                            </td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Account Name</td>
                            <td><input type="text" name="payout_account_name" value="<?php echo $Merchant->getPayoutAccountName();  ?>" placeholder="Name on Account" /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Account Type</td>
                            <td>
                                <select name="payout_account_type">
                                    <option value="">Choose an Account Type</option>
                                    <?php
                                    foreach(MerchantRow::$ENUM_PAYOUT_ACCOUNT_TYPES as $type => $description)
                                        echo "<option value='", $type, "'",
                                        ($type === $Merchant->getPayoutType() ? ' selected="selected"' : ''),
                                        ">", $description, "</option>\n";
                                    ?>
                                </select>
                            </td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Account Number</td>
                            <td><input type="text" name="payout_account_number" value="<?php echo $Merchant->getPayoutAccountNumber(); ?>" placeholder="Account Number" /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Routing Number</td>
                            <td><input type="text" name="payout_bank_code" value="<?php echo $Merchant->getPayoutRoutingNumber(); ?>" placeholder="Routing Number" /></td>
                        </tr>

                        <tr>
                            <th colspan="2">Notes: <?php echo $Merchant->getShortName(); ?></th>
                        </tr>
                        <?php $odd = false; ?>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td colspan="2"><textarea type="text" name="notes" rows="45" cols="38" placeholder="Merchant-specific notes" style="width: 96%;" ><?php echo $Merchant->getNotes(); ?></textarea></td>
                        </tr>

                        <tr >
                            <td colspan="2">
                                <input type="submit" value="Update" class="themed"/>
                            </td>
                        </tr>
                    </table>
                </fieldset>
            </form>
        </section>
    </article>

<?php $this->getTheme()->renderHTMLBodyFooter(); ?>