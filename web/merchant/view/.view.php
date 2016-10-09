<?php
use Merchant\Model\MerchantRow;
use Integration\Model\IntegrationRow;
use Integration\Request\Model\IntegrationRequestRow;
/**
 * @var \Merchant\View\MerchantView $this
 * @var PDOStatement $MerchantQuery
 **/
$Merchant = $this->getMerchant();
$odd = false;
$action_url = 'merchant?id=' . $Merchant->getID() . '&action=';
?>
    <!-- Page Navigation -->
    <nav class="page-menu hide-on-print">
        <a href="merchant?" class="button">Merchants</a>
        <a href="<?php echo $action_url; ?>view" class="button current">View #<?php echo $Merchant->getID(); ?></a>
        <a href="<?php echo $action_url; ?>edit" class="button">Edit #<?php echo $Merchant->getID(); ?></a>
        <a href="<?php echo $action_url; ?>provision" class="button">Provision</a>
        <a href="<?php echo $action_url; ?>settle" class="button">Settle Funds</a>
    </nav>
    
    <!-- Bread Crumbs -->
    <aside class="bread-crumbs">
        <a href="home" class="nav_home">Home</a>
        <a href="merchant" class="nav_merchant">Merchants</a>
        <a href="<?php echo $action_url; ?>view" class="nav_merchant_view"><?php echo $Merchant->getShortName(); ?></a>
    </aside>
    
    <section class="content">
        <?php if($this->hasException()) echo "<h5>", $this->getException()->getMessage(), "</h5>"; ?>
    
        <form class="form-view-merchant themed " method="GET">
            <fieldset>
                <legend>Merchant Information</legend>
                <table class="table-merchant-info themed striped-rows" style="float: left;">
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
                        <td><a target="_blank" href='<?php echo $Merchant->getURL(); ?>'><?php echo $Merchant->getURL(); ?></a></td>
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
                <table class="table-merchant-info themed striped-rows">
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
                        <td><?php echo \System\Arrays\Locations::$STATES[$Merchant->getRegionCode()]; ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Zip</td>
                        <td><?php echo $Merchant->getZipCode(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Country</td>
                        <td><?php echo \System\Arrays\Locations::$COUNTRIES[$Merchant->getCountryCode()]; ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td colspan="2">
                            <pre><?php echo $Merchant->getNotes() ?: "No Notes"; ?></pre>
                        </td>
                    </tr>
                </table>
            </fieldset>

            <fieldset>
                <legend>Provisions: <?php echo $Merchant->getShortName(); ?></legend>
                <table class="table-merchant-info themed striped-rows">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Complete</th>
                        <th>Provisioned</th>
                        <th>Settle</th>
                        <th>Notes</th>
                    </tr>
                    <?php

                    $DB = \Config\DBConfig::getInstance();
                    $IntegrationQuery = $DB->prepare(IntegrationRow::SQL_SELECT . IntegrationRow::SQL_ORDER_BY);
                    /** @noinspection PhpMethodParametersCountMismatchInspection */
                    $IntegrationQuery->setFetchMode(\PDO::FETCH_CLASS, IntegrationRow::_CLASS);
                    $IntegrationQuery->execute(array($this->getMerchant()->getID()));

                    $odd = false;
                    /** @var IntegrationRow $IntegrationRow **/
                    foreach($IntegrationQuery as $IntegrationRow) {
                        $id = $IntegrationRow->getID();
                        $MerchantIdentity = $IntegrationRow->getMerchantIdentity($Merchant);
                        if(!$MerchantIdentity->isProvisioned())
                            continue;
                    ?>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td><?php echo $IntegrationRow->getID(); ?></td>
                            <td><?php echo $IntegrationRow->getName(); ?></td>
                            <td><?php echo $IntegrationRow->getAPIType(); ?></td>
                            <td><?php echo "<span style='color:", ($MerchantIdentity->isProfileComplete() ? "green'>Yes"  : "red'>No"), "</span>"; ?></td>
                            <td><?php echo "<span style='color:", ($MerchantIdentity->isProvisioned() ? "green'>Yes"  : "red'>No"), "</span>"; ?></td>
                            <td><?php echo "<span style='color:", ($MerchantIdentity->canSettleFunds() ? "green'>Yes"  : "red'>No"), "</span>"; ?></td>
                            <td style="max-width: 24em; overflow-x: hidden;"><?php echo $IntegrationRow->getNotes(); ?></td>
                        </tr>

                    <?php } ?>

                    </table>
            </fieldset>

            <fieldset>
                <legend>Transactions: <?php echo $Merchant->getShortName(); ?></legend>
                <table class="table-results themed small striped-rows">
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
                            <td><?php echo $Transaction->getHolderFullName(); ?></td>
                            <td><?php echo date("M jS Y G:i:s", strtotime($Transaction->getTransactionDate())); ?></td>
                            <td><?php echo $Transaction->getInvoiceNumber(); ?></td>
                            <td><?php echo $Transaction->getUsername(); ?></td>
                            <td><?php echo $Transaction->getAmount(); ?></td>
                            <td><?php echo $Transaction->getOrderStatus(); ?></td>
                            <td><a href='merchant?id=<?php echo $Transaction->getMerchantID(); ?>'><?php echo $Transaction->getMerchantShortName(); ?></a></td>
                        </tr>
                    <?php } ?>
                </table>
            </fieldset>
        </form>
    </section>