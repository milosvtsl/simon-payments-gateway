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
$SessionManager = new \User\Session\SessionManager();
$SessionUser = $SessionManager->getSessionUser();

// Get Timezone diff
$offset = $SessionUser->getTimeZoneOffset('now');

$Theme = $this->getTheme();
$Theme->addPathURL('merchant',      'Merchants');
$Theme->addPathURL($action_url,     $Merchant->getShortName());
$Theme->renderHTMLBodyHeader();
$Theme->printHTMLMenu('merchant-view', $action_url);

?>
    <article class="themed">

        <section class="content" >
            <?php if($this->hasMessage()) echo "<h5>", $this->getMessage(), "</h5>"; ?>

            <form class="form-view-merchant themed " method="GET">
                <fieldset style="position: relative;">
                    <div class="legend">
                        <a href="merchant?action=edit&id=<?php echo $Merchant->getID(); ?>" style="text-decoration: none;">
                            <div class="app-button app-button-edit" style="display: inline-block;"></div>
                        </a>
                        Merchant: <?php echo $Merchant->getName(); ?>
                    </div>
                    <?php $odd = true; ?>
                    <table class="table-merchant-info themed small striped-rows float-left-on-layout-horizontal" style="width: 50%;">
                        <tr>
                            <th colspan="2">Information</th>
                        </tr>

                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">ID</td>
                            <td><?php echo $Merchant->getID(); ?></td>
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
<!--                        <tr class="row---><?php //echo ($odd=!$odd)?'odd':'even';?><!--">-->
<!--                            <td class="name">Merchant ID</td>-->
<!--                            <td>--><?php //echo $Merchant->getMerchantID(); ?><!--</td>-->
<!--                        </tr>-->

                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Merchant MCC</td>
                            <td><?php echo $Merchant->getMerchantMCC(), ' - ', \System\Arrays\Merchants::getDescription($Merchant->getMerchantMCC(), false); ?></td>
                        </tr>

                        <tr>
                            <th colspan="2">Business</th>
                        </tr>
                        <?php $odd = true; ?>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Status</td>
                            <td><?php echo $Merchant->getStatusName(); ?></td>
                        </tr>
<!--                        <tr class="row---><?php //echo ($odd=!$odd)?'odd':'even';?><!--">-->
<!--                            <td class="name">Title</td>-->
<!--                            <td>--><?php //echo $Merchant->getTitle(); ?><!--</td>-->
<!--                        </tr>-->
<!--                        <tr class="row---><?php //echo ($odd=!$odd)?'odd':'even';?><!--">-->
<!--                            <td class="name">DOB</td>-->
<!--                            <td>--><?php //echo $Merchant->getDOB(); ?><!--</td>-->
<!--                        </tr>-->
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
                            <td class="name">Store ID</td>
                            <td><?php echo $Merchant->getStoreID(); ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Sale Rep</td>
                            <td><?php echo $Merchant->getSaleRep(); ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Main Contact</td>
                            <td><?php echo $Merchant->getMainContact(); ?></td>
                        </tr>

                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Address</td>
                            <td><?php echo $Merchant->getAddress(), '<br/>', $Merchant->getAddress2(); ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Location</td>
                            <td><?php echo $Merchant->getCity(), ' ' ,
                                \System\Arrays\Locations::$STATES[$Merchant->getRegionCode()],
                                ', ', $Merchant->getZipCode(),
                                '<br/>', @\System\Arrays\Locations::$COUNTRIES[$Merchant->getCountryCode()]; ?>
                            </td>
                        </tr>

                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Agent Chain</td>
                            <td><?php echo $Merchant->getAgentChain(); ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Discover Ext</td>
                            <td><?php echo $Merchant->getDiscoverExt(); ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Amex Ext</td>
                            <td><?php echo $Merchant->getAmexExt(); ?></td>
                        </tr>

                    </table>

                    <table class="table-merchant-info themed small striped-rows" style="width: 50%;">

                        <tr>
                            <th colspan="2">Fees</th>
                        </tr>

                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Fee: Limit</td>
                            <td>$<?php echo number_format($Merchant->getConvenienceFeeLimit(), 2); ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Fee: Flat</td>
                            <td>$<?php echo number_format($Merchant->getConvenienceFeeFlat(), 2); ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Fee: Variable</td>
                            <td>$<?php echo number_format($Merchant->getConvenienceFeeVariable(), 2); ?></td>
                        </tr>

                        <tr>
                            <th colspan="2">Fraud Scrubbing</th>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Transaction High Limit (USD)</td>
                            <td><?php echo $Merchant->getFraudHighLimit() ?: ''; ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Transaction Low Limit (USD)</td>
                            <td><?php echo $Merchant->getFraudLowLimit() ?: ''; ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Transaction High Monthly Limit (USD)</td>
                            <td><?php echo $Merchant->getFraudHighMonthlyLimit() ?: ''; ?></td>
                        </tr>
                        <?php
                        foreach(MerchantRow::$FRAUD_FLAG_DESCRIPTIONS as $type => $description) {
                            ?>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name"><?php echo $description; ?></td>
                                <td>
                                    <?php echo $Merchant->hasFlag($type) ? '<strong>Yes</strong>' : 'No'; ?>
                                </td>
                            </tr>
                        <?php } ?>



                        <tr>
                            <th colspan="2">Batching</th>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Batch Close</td>
                            <td><?php echo $Merchant->getBatchTime(), ' ', $Merchant->getBatchTimeZone(); ?></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Open Date</td>
                            <td><?php echo $Merchant->getOpenDate(); ?></td>
                        </tr>

                        <tr>
                            <th colspan="2">Notes</th>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td colspan="2">
                                <pre><?php echo $Merchant->getNotes() ?: "No Notes"; ?></pre>
                            </td>
                        </tr>
                    </table>
                </fieldset>

                <fieldset style="position: relative;">
                    <div class="legend">
                        <a href="merchant?action=edit&id=<?php echo $Merchant->getID(); ?>" style="text-decoration: none;">
                            <div class="app-button app-button-edit" style="display: inline-block;"></div>
                        </a>
                        Provisions: <?php echo $Merchant->getShortName(); ?>
                    </div>
                    <table class="table-merchant-info themed striped-rows" style="width: 100%;">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>MID</th>
                            <th>Complete</th>
                            <th>Provisioned</th>
                            <th>Settle</th>
                            <th>Notes</th>
                        </tr>
                        <?php

                        $DB = \System\Config\DBConfig::getInstance();
                        $UserQuery = $DB->prepare(IntegrationRow::SQL_SELECT . IntegrationRow::SQL_ORDER_BY);
                        /** @noinspection PhpMethodParametersCountMismatchInspection */
                        $UserQuery->setFetchMode(\PDO::FETCH_CLASS, IntegrationRow::_CLASS);
                        $UserQuery->execute(array($this->getMerchant()->getID()));

                        $odd = false;
                        /** @var IntegrationRow $UserRow **/
                        foreach($UserQuery as $UserRow) {
                            $id = $UserRow->getID();
                            $MerchantIdentity = $UserRow->getMerchantIdentity($Merchant);
                            if(!$MerchantIdentity->isProvisioned())
                                continue;
                            ?>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td><a href="integration?id=<?php echo $UserRow->getID(); ?>"><?php echo $UserRow->getID(); ?></a></td>
                                <td><a href="integration?id=<?php echo $UserRow->getID(); ?>"><?php echo $UserRow->getName(); ?></a></td>
                                <td><?php echo $UserRow->getAPIType(); ?></td>
                                <td><?php echo $MerchantIdentity->getRemoteID() ? '<strong>'.$MerchantIdentity->getRemoteID().'</strong>' : 'N/A'; ?></td>
                                <td><?php echo "<span style='color:", ($MerchantIdentity->isProfileComplete() ? "green'>Yes"  : "red'>No"), "</span>"; ?></td>
                                <td><?php echo "<span style='color:", ($MerchantIdentity->isProvisioned() ? "green'>Yes"  : "red'>No"), "</span>"; ?></td>
                                <td><?php echo "<span style='color:", ($MerchantIdentity->canSettleFunds() ? "green'>Yes"  : "red'>No"), "</span>"; ?></td>
                                <td style="max-width: 24em; overflow-x: hidden;"><?php echo $UserRow->getNotes(); ?></td>
                            </tr>

                        <?php } ?>

                    </table>
                </fieldset>

                <fieldset style="position: relative;">
                    <div class="legend">
                        <a href="merchant?action=edit&id=<?php echo $Merchant->getID(); ?>" style="text-decoration: none;">
                            <div class="app-button app-button-edit" style="display: inline-block;"></div>
                        </a>
                        Users: <?php echo $Merchant->getShortName(); ?>
                    </div>
                    <table class="table-merchant-users themed striped-rows" style="width: 100%;">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                        </tr>
                        <?php

                        $DB = \System\Config\DBConfig::getInstance();
                        $UserQuery = $DB->prepare(
                            "SELECT * FROM user u "
                            . "\nLEFT JOIN user_merchants um ON u.id = um.id_user"
                            . "\nWHERE um.id_merchant=?");
                        /** @noinspection PhpMethodParametersCountMismatchInspection */
                        $UserQuery->setFetchMode(\PDO::FETCH_CLASS, \User\Model\UserRow::_CLASS);
                        $UserQuery->execute(array($this->getMerchant()->getID()));

                        $odd = false;
                        /** @var \User\Model\UserRow $UserRow **/
                        foreach($UserQuery as $UserRow) {
                            ?>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td><a href="user?uid=<?php echo $UserRow->getUID(); ?>"><?php echo $UserRow->getID(); ?></a></td>
                                <td><a href="user?uid=<?php echo $UserRow->getUID(); ?>"><?php echo $UserRow->getUsername(); ?></a></td>
                            </tr>

                        <?php } ?>

                    </table>
                </fieldset>


                <fieldset>
                    <div class="legend">
                        Orders: <?php echo $Merchant->getShortName(); ?>
                    </div>
                    <table class="table-results themed small striped-rows" style="width: 100%;">
                        <tr>
                            <th>ID</th>
                            <th>Amount</th>
                            <th>Customer</th>
                            <th>Mode</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                        <?php
                        /** @var \Order\Model\OrderRow $Order */

                        $DB = \System\Config\DBConfig::getInstance();

                        $OrderQuery = $DB->prepare(OrderRow::SQL_SELECT
                            . "\nWHERE oi.merchant_id = ?"
                            . OrderRow::SQL_ORDER_BY
                            . "\nLIMIT 10");
                        /** @noinspection PhpMethodParametersCountMismatchInspection */
                        $OrderQuery->setFetchMode(\PDO::FETCH_CLASS, OrderRow::_CLASS);
                        $OrderQuery->execute(array($this->getMerchant()->getID()));

                        $odd = false;
                        foreach($OrderQuery as $Order) { ?>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td><a href='order?uid=<?php echo $Order->getUID(false); ?>'><?php echo $Order->getID(); ?></a></td>
                                <td>$<?php echo $Order->getAmount(); ?></td>
                                <td><?php echo $Order->getCardHolderFullName(); ?></td>
                                <td><?php echo ucfirst($Order->getEntryMode()); ?></td>
                                <td><?php echo date("M dS Y G:i:s", strtotime($Order->getDate()) + $offset); ?></td>
                                <td><?php echo $Order->getStatus(); ?></td>
                            </tr>
                        <?php } ?>
                    </table>
                </fieldset>

            </form>
        </section>
    </article>

<?php $this->getTheme()->renderHTMLBodyFooter(); ?>