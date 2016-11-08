<?php
use Merchant\Model\MerchantRow;
/**
 * @var \Merchant\View\MerchantView $this
 * @var PDOStatement $MerchantQuery
 **/
$Merchant = $this->getMerchant();
$odd = false;
$action_url = '/merchant/index.php?id=' . $Merchant->getID() . '&action=';
?>

    <!-- Page Navigation -->
    <nav class="page-menu hide-on-print">
        <a href="/" class="button">Dashboard <div class="submenu-icon submenu-icon-dashboard"></div></a>
        <?php if($SessionUser->hasAuthority('ROLE_ADMIN', 'ROLE_POST_CHARGE')) { ?>
            <a href="transaction/charge.php" class="button<?php echo @$ca['charge']; ?>">Charge<div class="submenu-icon submenu-icon-charge"></div></a>
        <?php } ?>
        <?php if($SessionUser->hasAuthority('ROLE_ADMIN', 'ROLE_SUB_ADMIN')) { ?>
            <a href="merchant#content" class="button">Merchants <div class="submenu-icon submenu-icon-merchant"></div></a>
        <?php } ?>
        <a href="<?php echo $action_url; ?>view" class="button">View <div class="submenu-icon submenu-icon-view"></div></a>
        <a href="<?php echo $action_url; ?>edit" class="button current">Edit <div class="submenu-icon submenu-icon-edit"></div></a>
        <?php if($SessionUser->hasAuthority('ROLE_ADMIN')) { ?>
            <a href="<?php echo $action_url; ?>provision" class="button">Provision <div class="submenu-icon submenu-icon-provision"></div></a>
            <a href="<?php echo $action_url; ?>settle" class="button">Settle <div class="submenu-icon submenu-icon-settle"></div></a>
        <?php } ?>
    </nav>

    <article id="article" class="themed">
        <section id="content" class="content">
            <a name='content'></a>

            <!-- Bread Crumbs -->
            <aside class="bread-crumbs">
                <a href="merchant" class="nav_merchant">Merchants</a>
                <a href="<?php echo $action_url; ?>view" class="nav_merchant_view"><?php echo $Merchant->getShortName(); ?></a>
                <a href="<?php echo $action_url; ?>edit" class="nav_merchant_view">Edit</a>
            </aside>

            <?php if($this->hasMessage()) echo "<h5>", $this->getMessage(), "</h5>"; ?>

            <form name="form-merchant-edit" class="themed" method="POST" action="<?php echo $action_url; ?>edit">
                <input type="hidden" name="id" value="<?php echo $Merchant->getID(); ?>" />
                <input type="hidden" name="action" value="edit" />
                <fieldset>
                    <legend>Edit Merchant Fields</legend>
                    <table class="table-merchant-info themed small striped-rows" style="float: left;">
                        <tr>
                            <th colspan="2">Information</th>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">ID</td>
                            <td><?php echo $Merchant->getID(); ?></td>
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
    <!--                    <tr class="row---><?php //echo ($odd=!$odd)?'odd':'even';?><!--">-->
    <!--                        <td>UID</td>-->
    <!--                        <td>--><?php //echo $Merchant->getUID(); ?><!--</td>-->
    <!--                    </tr>-->
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Name</td>
                            <td><input type="text" name="name" size="32" value="<?php echo $Merchant->getName(); ?>" /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Short Name</td>
                            <td><input type="text" name="short_name" size="32" value="<?php echo $Merchant->getShortName(); ?>" /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Email</td>
                            <td><input type="text" name="email" size="32" value="<?php echo $Merchant->getMainEmailID(); ?>" /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">URL</td>
                            <td><input type="text" name="url" size="32" value="<?php echo $Merchant->getURL(); ?>" /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Merchant ID</td>
                            <td><input type="text" name="merchant_id" size="12" value="<?php echo $Merchant->getID(); ?>" /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Merchant SIC</td>
                            <td><input type="text" name="sic" size="12" value="<?php echo $Merchant->getMerchantSIC(); ?>" /></td>
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
                            <td><input type="text" name="convenience_fee_variable" size="12" value="<?php echo $Merchant->getFeeVariable(); ?>" /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Flat</td>
                            <td><input type="text" name="convenience_fee_flat" size="12" value="<?php echo $Merchant->getFeeFlat(); ?>" /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Limit</td>
                            <td><input type="text" name="convenience_fee_limit" size="12" value="<?php echo $Merchant->getFeeLimit(); ?>" /></td>
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
                            <td><input type="text" name="discover_external" size="32" value="<?php echo $Merchant->getDiscoverExt(); ?>" /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Amex Ext</td>
                            <td><input type="text" name="amex_external" size="32" value="<?php echo $Merchant->getAmexExt(); ?>" /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Agent Chain</td>
                            <td><input type="text" name="agent_chain" size="32" value="<?php echo $Merchant->getAgentChain(); ?>" /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Tax ID</td>
                            <td><input type="text" name="tax_id" size="32" value="<?php echo $Merchant->getTaxID(); ?>" /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Business Tax ID</td>
                            <td><input type="text" name="business_tax_id" size="32" value="<?php echo $Merchant->getBusinessTaxID(); ?>" /></td>
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
                            <td><input type="text" name="telephone" size="32" value="<?php echo $Merchant->getTelephone(); ?>" /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Address</td>
                            <td><input type="text" name="address" size="32" value="<?php echo $Merchant->getAddress(); ?>" /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Address 2</td>
                            <td><input type="text" name="address2" size="32" value="<?php echo $Merchant->getAddress2(); ?>" /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">City</td>
                            <td><input type="text" name="city" size="32" value="<?php echo $Merchant->getCity(); ?>" /></td>
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
                                <select name="country">
                                    <?php
                                    foreach(\System\Arrays\Locations::$COUNTRIES as $code => $name)
                                        if(strlen($code) === 3)
                                            echo "<option value='", $code, "'",
                                            ($code === $Merchant->getCountryCode() ? ' selected="selected"' : ''),
                                            ">", $name, "</option>\n";
                                    ?>
                                </select>
                            </td>
                        </tr>
                    </table>
                    <?php $odd = false; ?>
                    <table class="table-merchant-info themed small striped-rows">
                        <tr>
                            <th colspan="2">Contact Information</th>
                        </tr>
                        <?php $odd = false; ?>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Main Contact</td>
                            <td><input type="text" name="main_contact" size="32" value="<?php echo $Merchant->getMainContact(); ?>" /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Sale Rep</td>
                            <td><input type="text" name="sale_rep" size="32" value="<?php echo $Merchant->getSaleRep(); ?>" /></td>
                        </tr>

                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Title</td>
                            <td><input type="text" name="main_contact" size="32" value="<?php echo $Merchant->getTitle(); ?>" /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">DOB</td>
                            <td><input type="date" name="main_contact" value="<?php echo $Merchant->getDOB(); ?>" /></td>
                        </tr>


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
                                    foreach(MerchantRow::$ENUM_PAYOUT_TYPES as $type => $name)
                                        echo "<option value='", $type, "'",
                                        ($type === $Merchant->getPayoutType() ? ' selected="selected"' : ''),
                                        ">", $name, "</option>\n";
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
                                    foreach(MerchantRow::$ENUM_PAYOUT_ACCOUNT_TYPES as $type => $name)
                                        echo "<option value='", $type, "'",
                                        ($type === $Merchant->getPayoutType() ? ' selected="selected"' : ''),
                                        ">", $name, "</option>\n";
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
                            <td><input type="text" name="payout_bank_code" value="<?php echo $Merchant->getPayoutBankCode(); ?>" placeholder="Routing Number" /></td>
                        </tr>

                        <tr>
                            <th colspan="2">Notes: <?php echo $Merchant->getShortName(); ?></th>
                        </tr>
                        <?php $odd = false; ?>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td colspan="2"><textarea type="text" name="notes" rows="45" cols="44" placeholder="Merchant-specific notes" ><?php echo $Merchant->getNotes(); ?></textarea></td>
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