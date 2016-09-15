<?php
use Merchant\Model\MerchantRow;
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
<!--                <a href="--><?php //echo $action_url; ?><!--delete" class="button">Delete</a>-->
            </fieldset>
            <fieldset>
                <legend>Merchant Information</legend>
                <table class="table-merchant-info themed" style="float: left;">
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
                        <td>Email</td>
                        <td><a href='mailto:<?php echo $Merchant->getMainEmailID(); ?>'><?php echo $Merchant->getMainEmailID(); ?></a></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>URL</td>
                        <td><a href='<?php echo $Merchant->getURL(); ?>'><?php echo $Merchant->getURL(); ?></a></td>
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
                        <td>Sale Rep</td>
                        <td><?php echo $Merchant->getSaleRep(); ?></td>
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
                </table>
                <table class="table-merchant-info themed">
                    <tr>
                        <th>Field</th>
                        <th>Value</th>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Title</td>
                        <td><?php echo $Merchant->getTitle(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>DOB</td>
                        <td><?php echo $Merchant->getDOB(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Tax ID</td>
                        <td><?php echo $Merchant->getTaxID(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Business Tax ID</td>
                        <td><?php echo $Merchant->getBusinessTaxID(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Business Type</td>
                        <td><?php echo MerchantRow::$ENUM_BUSINESS_TYPE[$Merchant->getBusinessType()]; ?></td>
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
                        <td><?php echo $Merchant->getRegionCode(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Zip</td>
                        <td><?php echo $Merchant->getZipCode(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Country</td>
                        <td><?php echo $Merchant->getCountryCode(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td colspan="2">
                            <pre><?php echo $Merchant->getNotes() ?: "No Notes"; ?></pre>
                        </td>
                    </tr>
                </table>
            </fieldset>

            <fieldset>
                <legend>Transactions: <?php echo $Merchant->getShortName(); ?></legend>
                <table class="table-results themed small">
                    <tr>
                        <th>ID</th>
                        <th>Order</th>
                        <th>Batch</th>
                        <th>Card Holder</th>
                        <th>Date</th>
                        <th>Invoice ID</th>
                        <th>User Name</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Merchant</th>
                    </tr>
                    <?php
                    /** @var \Transaction\Model\TransactionRow $Transaction */

                    $DB = \Config\DBConfig::getInstance();
                    $TransactionQuery = $DB->prepare(\Transaction\Model\TransactionRow::SQL_SELECT . "WHERE oi.merchant_id = ? LIMIT 100");
                    /** @noinspection PhpMethodParametersCountMismatchInspection */
                    $TransactionQuery->setFetchMode(\PDO::FETCH_CLASS, \Transaction\Model\TransactionRow::_CLASS);
                    $TransactionQuery->execute(array($this->getMerchant()->getID()));

                    $odd = false;
                    foreach($TransactionQuery as $Transaction) { ?>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td><a href='transaction?id=<?php echo $Transaction->getID(); ?>'><?php echo $Transaction->getID(); ?></a></td>
                            <td><?php if($Transaction->getOrderID()) { ?><a href='order?id=<?php echo $Transaction->getOrderID(); ?>'><?php echo $Transaction->getOrderID(); ?></a><?php } else echo 'N/A'; ?></td>
                            <td><?php if($Transaction->getBatchID()) { ?><a href='batch?id=<?php echo $Transaction->getBatchID(); ?>'><?php echo $Transaction->getBatchID(); ?></a><?php } else echo 'N/A'; ?></td>
                            <td><?php echo $Transaction->getHolderFullFullName(); ?></td>
                            <td><?php echo date("M jS Y G:i:s", strtotime($Transaction->getTransactionDate())); ?></td>
                            <td><?php echo $Transaction->getInvoiceNumber(); ?></td>
                            <td><?php echo $Transaction->getUsername(); ?></td>
                            <td><?php echo $Transaction->getAmount(); ?></td>
                            <td><?php echo $Transaction->getStatus(); ?></td>
                            <td><a href='merchant?id=<?php echo $Transaction->getMerchantID(); ?>'><?php echo $Transaction->getMerchantShortName(); ?></a></td>
                        </tr>
                    <?php } ?>
                </table>
            </fieldset>
        </form>
    </section>