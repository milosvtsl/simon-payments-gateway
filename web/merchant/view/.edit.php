<?php
/**
 * @var \Merchant\View\MerchantView $this
 * @var PDOStatement $MerchantQuery
 **/
$Merchant = $this->getMerchant();
$odd = false;
$action_url = 'merchant?id=' . $Merchant->getID() . '&action=';
?>
    <section class="message">
        <h1>Edit <?php echo $Merchant->getShortName(); ?></h1>

        <?php if($this->hasException()) { ?>
            <h5><?php echo $this->hasException(); ?></h5>

        <?php } else if ($this->hasSessionMessage()) { ?>
            <h5><?php echo $this->popSessionMessage(); ?></h5>

        <?php } else { ?>
            <h5>Edit this Merchant Account...</h5>

        <?php } ?>
    </section>

    <section class="content">
        <form class="form-view-merchant themed" method="POST">
            <fieldset class="action-fields">
                <legend>Actions</legend>
                <a href="merchant?" class="button">Merchant List</a>
                <a href="<?php echo $action_url; ?>view" class="button">View</a>
            </fieldset>
            <fieldset>
                <legend>Edit Merchant Fields</legend>
                <table class="table-merchant-info themed">
                    <tr>
                        <th>Field</th>
                        <th>Value</th>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>ID</td>
                        <td><?php echo $Merchant->getID(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>UID</td>
                        <td><?php echo $Merchant->getUID(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Name</td>
                        <td><input type="text" name="name" size="32" value="<?php echo $Merchant->getName(); ?>" /></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Short Name</td>
                        <td><input type="text" name="short_name" size="32" value="<?php echo $Merchant->getShortName(); ?>" /></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Email</td>
                        <td><input type="text" name="email" size="32" value="<?php echo $Merchant->getMainEmailID(); ?>" /></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Merchant ID</td>
                        <td><input type="text" name="merchant_id" size="12" value="<?php echo $Merchant->getID(); ?>" /></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Merchant SIC</td>
                        <td><input type="text" name="sic" size="12" value="<?php echo $Merchant->getMerchantSIC(); ?>" /></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Fee: Limit</td>
                        <td><input type="text" name="convenience_fee_limit" size="12" value="<?php echo $Merchant->getFeeLimit(); ?>" /></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Fee: Flat</td>
                        <td><input type="text" name="convenience_fee_flat" size="12" value="<?php echo $Merchant->getFeeFlat(); ?>" /></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Fee: Variable</td>
                        <td><input type="text" name="convenience_fee_variable" size="12" value="<?php echo $Merchant->getFeeVariable(); ?>" /></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Batch Close</td>
                        <td><?php echo $Merchant->getBatchTime(), ' ', $Merchant->getBatchTimeZone(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Open Date</td>
                        <td><input type="datetime-local" name="open_date" value="<?php echo date("Y-m-d\TH:i:s", strtotime($Merchant->getOpenDate())); ?>" /></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Status</td>
                        <td>
                            <select name="status_id">
                            <?php
                            $StatusQuery = \Merchant\Model\MerchantStatusRow::queryAll();
                            foreach($StatusQuery as $State)
                                /** @var \Merchant\Model\MerchantStatusRow $State */
                                echo "<option value='", $State->getID(), "'",
                                ($State->getID() === $Merchant->getStatusID() ? ' selected="selected"' : ''),
                                ">", $State->getName(), "</option>\n";
                            ?>
                            </select>
                        </td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Store ID</td>
                        <td><input type="text" name="store_id" size="12" value="<?php echo $Merchant->getStoreID(); ?>" /></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Discover Ext</td>
                        <td><input type="text" name="discover_external" size="32" value="<?php echo $Merchant->getDiscoverExt(); ?>" /></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Amex Ext</td>
                        <td><input type="text" name="amex_external" size="32" value="<?php echo $Merchant->getAmexExt(); ?>" /></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Agent Chain</td>
                        <td><input type="text" name="agent_chain" size="32" value="<?php echo $Merchant->getAgentChain(); ?>" /></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Main Contact</td>
                        <td><input type="text" name="main_contact" size="32" value="<?php echo $Merchant->getMainContact(); ?>" /></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Telephone Number</td>
                        <td><input type="text" name="telephone" size="32" value="<?php echo $Merchant->getTelephone(); ?>" /></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Address</td>
                        <td><input type="text" name="address" size="32" value="<?php echo $Merchant->getAddress(); ?>" /></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Address 2</td>
                        <td><input type="text" name="address2" size="32" value="<?php echo $Merchant->getAddress2(); ?>" /></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>City</td>
                        <td><input type="text" name="city" size="32" value="<?php echo $Merchant->getCity(); ?>" /></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>State</td>
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
                        <td>Zip</td>
                        <td><input type="text" name="zipcode" size="12" value="<?php echo $Merchant->getZipCode(); ?>" /></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Sale Rep</td>
                        <td><input type="text" name="sale_rep" size="32" value="<?php echo $Merchant->getSaleRep(); ?>" /></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Notes</td>
                        <td><textarea type="text" name="notes" rows="5" cols="34" ><?php echo $Merchant->getNotes(); ?></textarea></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Update</td>
                        <td><input type="submit" value="Update" /></td>
                    </tr>
                </table>
            </fieldset>
        </form>
    </section>