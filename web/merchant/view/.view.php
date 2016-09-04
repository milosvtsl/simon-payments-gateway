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
        <h1>View Merchant</h1>

        <?php if($this->hasException()) { ?>
            <h5><?php echo $this->hasException(); ?></h5>

        <?php } else if ($this->hasSessionMessage()) { ?>
            <h5><?php echo $this->popSessionMessage(); ?></h5>

        <?php } else { ?>
            <h5>View a Merchant Account...</h5>

        <?php } ?>
    </section>

    <section class="content">
        <form class="form-view-merchant themed" onsubmit="return false;">
            <fieldset class="action-fields">
                <legend>Actions</legend>
                <a href="merchant?" class="button">Merchant List</a>
                <a href="<?php echo $action_url; ?>edit" class="button">Edit</a>
                <a href="<?php echo $action_url; ?>delete" class="button">Delete</a>
            </fieldset>
            <fieldset>
                <legend>Merchant Information</legend>
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
                        <td>Name</td>
                        <td><?php echo $Merchant->getName(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Short Name</td>
                        <td><?php echo $Merchant->getShortName(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>UID</td>
                        <td><?php echo $Merchant->getUID(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Merchant ID</td>
                        <td><?php echo $Merchant->getMerchantID(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Merchant SIC</td>
                        <td><?php echo $Merchant->getMerchantSIC(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Fee: Limit</td>
                        <td>$<?php echo $Merchant->getFeeLimit(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Fee: Flat</td>
                        <td>$<?php echo $Merchant->getFeeFlat(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Fee: Variable</td>
                        <td>$<?php echo $Merchant->getFeeVariable(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Batch Close</td>
                        <td><?php echo $Merchant->getBatchTime(), ' ', $Merchant->getBatchTimeZone(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Open Date</td>
                        <td><?php echo $Merchant->getOpenDate(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Status</td>
                        <td><?php echo $Merchant->getStatusName(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Store ID</td>
                        <td><?php echo $Merchant->getStoreID(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Discover Ext</td>
                        <td><?php echo $Merchant->getDiscoverExt(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Amex Ext</td>
                        <td><?php echo $Merchant->getAmexExt(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Agent Chain</td>
                        <td><?php echo $Merchant->getAgentChain(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Main Contact</td>
                        <td><?php echo $Merchant->getMainContact(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Telephone Number</td>
                        <td><?php echo $Merchant->getTelephone(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Address</td>
                        <td><?php echo $Merchant->getAddress(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Address 2</td>
                        <td><?php echo $Merchant->getAddress2(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>City</td>
                        <td><?php echo $Merchant->getCity(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>State</td>
                        <td><?php echo $Merchant->getStateCode(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Zip</td>
                        <td><?php echo $Merchant->getZipCode(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Email</td>
                        <td><a href='mailto:<?php echo $Merchant->getMainEmailID(); ?>'><?php echo $Merchant->getMainEmailID(); ?></a></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Sale Rep</td>
                        <td><?php echo $Merchant->getSaleRep(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Notes</td>
                        <td><?php echo $Merchant->getNotes(); ?></td>
                    </tr>
                </table>
            </fieldset>
        </form>
    </section>