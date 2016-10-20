<?php
use Merchant\Model\MerchantRow;
use Integration\Model\IntegrationRow;
use Integration\Request\Model\IntegrationRequestRow;
use Order\Model\OrderRow;
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
        <a href="merchant?" class="button">Merchants <div class="submenu-icon submenu-icon-list"></div></a>
        <a href="<?php echo $action_url; ?>view" class="button current">View <div class="submenu-icon submenu-icon-view"></div></a>
        <a href="<?php echo $action_url; ?>edit" class="button">Edit <div class="submenu-icon submenu-icon-edit"></div></a>
        <a href="<?php echo $action_url; ?>provision" class="button">Provision <div class="submenu-icon submenu-icon-provision"></div></a>
        <a href="<?php echo $action_url; ?>settle" class="button">Settle <div class="submenu-icon submenu-icon-settle"></div></a>
    </nav>

    <article class="themed">

        <section class="content" >
            <!-- Bread Crumbs -->
            <aside class="bread-crumbs">
                <a href="merchant" class="nav_merchant">Merchants</a>
                <a href="<?php echo $action_url; ?>view" class="nav_merchant_view"><?php echo $Merchant->getShortName(); ?></a>
            </aside>
            <?php if($this->hasMessage()) echo "<h5>", $this->getMessage(), "</h5>"; ?>

            <form class="form-view-merchant themed " method="GET">
                <fieldset style="display: inline-block;">
                    <legend>Merchant Information</legend>
                    <table class="table-merchant-info themed striped-rows" style="float: left;">
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">ID</td>
                            <td><?php echo $Merchant->getID(); ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Name</td>
                            <td><?php echo $Merchant->getName(); ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Short Name</td>
                            <td><?php echo $Merchant->getShortName(); ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">UID</td>
                            <td><?php echo $Merchant->getUID(); ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Email</td>
                            <td><a href='mailto:<?php echo $Merchant->getMainEmailID(); ?>'><?php echo $Merchant->getMainEmailID(); ?></a></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">URL</td>
                            <td><a target="_blank" href='<?php echo $Merchant->getURL(); ?>'><?php echo $Merchant->getURL(); ?></a></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Merchant ID</td>
                            <td><?php echo $Merchant->getMerchantID(); ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Merchant SIC</td>
                            <td><?php echo $Merchant->getMerchantSIC(); ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Fee: Limit</td>
                            <td>$<?php echo number_format($Merchant->getFeeLimit(), 2); ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Fee: Flat</td>
                            <td>$<?php echo number_format($Merchant->getFeeFlat(), 2); ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Fee: Variable</td>
                            <td>$<?php echo number_format($Merchant->getFeeVariable(), 2); ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Batch Close</td>
                            <td><?php echo $Merchant->getBatchTime(), ' ', $Merchant->getBatchTimeZone(); ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Open Date</td>
                            <td><?php echo $Merchant->getOpenDate(); ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Status</td>
                            <td><?php echo $Merchant->getStatusName(); ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Store ID</td>
                            <td><?php echo $Merchant->getStoreID(); ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Sale Rep</td>
                            <td><?php echo $Merchant->getSaleRep(); ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Discover Ext</td>
                            <td><?php echo $Merchant->getDiscoverExt(); ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Amex Ext</td>
                            <td><?php echo $Merchant->getAmexExt(); ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Agent Chain</td>
                            <td><?php echo $Merchant->getAgentChain(); ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Main Contact</td>
                            <td><?php echo $Merchant->getMainContact(); ?></td>
                        </tr>
                    </table>
                    <table class="table-merchant-info themed striped-rows">
                        <tr>
                            <th>Field</th>
                            <th>Value</th>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Title</td>
                            <td><?php echo $Merchant->getTitle(); ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">DOB</td>
                            <td><?php echo $Merchant->getDOB(); ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Tax ID</td>
                            <td><?php echo $Merchant->getTaxID(); ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Business Tax ID</td>
                            <td><?php echo $Merchant->getBusinessTaxID(); ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Business Type</td>
                            <td><?php echo MerchantRow::$ENUM_BUSINESS_TYPE[$Merchant->getBusinessType()]; ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Telephone Number</td>
                            <td><?php echo $Merchant->getTelephone(); ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Address</td>
                            <td><?php echo $Merchant->getAddress(); ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Address 2</td>
                            <td><?php echo $Merchant->getAddress2(); ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">City</td>
                            <td><?php echo $Merchant->getCity(); ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">State</td>
                            <td><?php echo \System\Arrays\Locations::$STATES[$Merchant->getRegionCode()]; ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Zip</td>
                            <td><?php echo $Merchant->getZipCode(); ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Country</td>
                            <td><?php echo \System\Arrays\Locations::$COUNTRIES[$Merchant->getCountryCode()]; ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td colspan="2">
                                <pre><?php echo $Merchant->getNotes() ?: "No Notes"; ?></pre>
                            </td>
                        </tr>
                    </table>
                </fieldset>

                <fieldset style="display: inline-block;">
                    <legend>Orders: <?php echo $Merchant->getShortName(); ?></legend>
                    <table class="table-results themed small striped-rows">
                        <tr>
                            <th>ID</th>
                            <th>Amount</th>
                            <th>Customer</th>
                            <th>Mode</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Item&nbsp;ID</th>
                            <th>Invoice&nbsp;ID</th>
                            <th>Customer&nbsp;ID</th>
                        </tr>
                        <?php
                        /** @var \Order\Model\OrderRow $Order */

                        $DB = \Config\DBConfig::getInstance();

                        $OrderQuery = $DB->prepare(OrderRow::SQL_SELECT
                            . "\nWHERE oi.merchant_id = ?"
                            . OrderRow::SQL_ORDER_BY
                            . "\nLIMIT 50");
                        /** @noinspection PhpMethodParametersCountMismatchInspection */
                        $OrderQuery->setFetchMode(\PDO::FETCH_CLASS, OrderRow::_CLASS);
                        $OrderQuery->execute(array($this->getMerchant()->getID()));

                        $odd = false;
                        foreach($OrderQuery as $Order) { ?>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td><a href='order?uid=<?php echo $Order->getUID(); ?>'><?php echo $Order->getID(); ?></a></td>
                                <td>$<?php echo $Order->getAmount(); ?></td>
                                <td><?php echo $Order->getCardHolderFullName(); ?></td>
                                <td><?php echo ucfirst($Order->getEntryMode()); ?></td>
                                <td><?php echo date("M jS Y G:i:s", strtotime($Order->getDate())); ?></td>
                                <td><?php echo $Order->getStatus(); ?></td>
                                <td><?php echo $Order->getOrderItemID(); ?></td>
                                <td><?php echo $Order->getInvoiceNumber(); ?></td>
                                <td><?php echo $Order->getCustomerID(); ?></td>

                            </tr>
                        <?php } ?>
                    </table>
                </fieldset>

                <fieldset style="display: inline-block;">
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
                                <td><a href="integration?id=<?php echo $IntegrationRow->getID(); ?>"><?php echo $IntegrationRow->getID(); ?></a></td>
                                <td><a href="integration?id=<?php echo $IntegrationRow->getID(); ?>"><?php echo $IntegrationRow->getName(); ?></a></td>
                                <td><?php echo $IntegrationRow->getAPIType(); ?></td>
                                <td><?php echo "<span style='color:", ($MerchantIdentity->isProfileComplete() ? "green'>Yes"  : "red'>No"), "</span>"; ?></td>
                                <td><?php echo "<span style='color:", ($MerchantIdentity->isProvisioned() ? "green'>Yes"  : "red'>No"), "</span>"; ?></td>
                                <td><?php echo "<span style='color:", ($MerchantIdentity->canSettleFunds() ? "green'>Yes"  : "red'>No"), "</span>"; ?></td>
                                <td style="max-width: 24em; overflow-x: hidden;"><?php echo $IntegrationRow->getNotes(); ?></td>
                            </tr>

                        <?php } ?>

                        </table>
                </fieldset>
            </form>
        </section>
    </article>