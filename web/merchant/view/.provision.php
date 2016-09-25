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
    <section class="content">
        <div class="action-fields">
            <a href="merchant?" class="button">Merchant List</a>
            <a href="<?php echo $action_url; ?>view" class="button">View</a>
            <a href="<?php echo $action_url; ?>edit" class="button">Edit</a>
            <a href="<?php echo $action_url; ?>provision" class="button">Provision</a>
            <a href="<?php echo $action_url; ?>settle" class="button">Settle Funds</a>
        </div>

        <h1>Provision <?php echo $Merchant->getShortName(); ?></h1>

        <?php if($this->hasException()) { ?>
            <h5><?php echo $this->hasException(); ?></h5>

        <?php } else if ($this->hasSessionMessage()) { ?>
            <h5><?php echo $this->popSessionMessage(); ?></h5>

        <?php } else { ?>
            <h5>Choose an integration to provision this merchant...</h5>

        <?php } ?>

        <form class="form-view-merchant themed" method="POST">

            <fieldset class="themed">
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
                        <td>Status</td>
                        <td><?php echo $Merchant->getStatusName(); ?></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>Email</td>
                        <td><a href='mailto:<?php echo $Merchant->getMainEmailID(); ?>'><?php echo $Merchant->getMainEmailID(); ?></a></td>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td>URL</td>
                        <td><a target="_blank" href='<?php echo $Merchant->getURL(); ?>'><?php echo $Merchant->getURL(); ?></a></td>
                    </tr>
                </table>
                <table class="table-merchant-info themed">
                    <tr>
                        <th>Field</th>
                        <th>Value</th>
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
                        <td colspan="2">
                            <pre><?php echo $Merchant->getNotes() ?: "No Notes"; ?></pre>
                        </td>
                    </tr>
                </table>
            </fieldset>

            <?php if(empty($_GET['integration_id'])) { ?>

            <fieldset class="themed" style="max-width: 59em;">
                <legend>Choose Integration</legend>
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
                    $isProduction = $IntegrationRow->getAPIType() === IntegrationRow::ENUM_API_TYPE_PRODUCTION;
                    $reason = null;
                    ?>
                    <fieldset style="display: inline-block; margin-bottom: 1em; <?php if(!$isProduction) echo 'opacity:0.5;'; ?>">
                        <legend>
                            <?php echo $IntegrationRow->getName(); ?>
                            (<?php echo ucwords($IntegrationRow->getAPIType()); ?>)
                        </legend>
                        <table class="table-merchant-info themed" style="float: left; min-width: 27em; min-height: 22em;">
                            <tr>
                                <th>Field</th>
                                <th>Value</th>
                                <th>Action</th>
                            </tr>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td>Profile</td>
                                <?php if ($MerchantIdentity->isProfileComplete($reason)) { ?>
                                    <td><span style='color:green'><?php echo $reason; ?></span></td>
                                    <td><a href="<?php echo $action_url; ?>edit" class="button">Edit</a></td>
                                <?php } else { ?>
                                    <td><span style='color:red'><?php echo $reason; ?></span></td>
                                    <td><a href="<?php echo $action_url; ?>edit" class="button">Finish</a></td>
                                <?php } ?>
                            </tr>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td>Provision</td>
                                <?php if ($MerchantIdentity->isProvisioned($reason)) { ?>
                                    <td><span style='color:green'><?php echo $reason; ?></span></td>
                                    <td><a href="<?php echo $action_url; ?>provision&integration_id=<?php echo $id; ?>" class="button">View</a></td>
                                <?php } else { ?>
                                    <td><span style='color:red'><?php echo $reason; ?></span></td>
                                    <td><a href="<?php echo $action_url; ?>provision&integration_id=<?php echo $id; ?>" class="button">Provision</a></td>
                                <?php } ?>
                            </tr>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td>Payout</td>
                                <?php if ($MerchantIdentity->canSettleFunds($reason)) { ?>
                                    <td><span style='color:green'><?php echo $reason; ?></span></td>
                                    <td><a href="<?php echo $action_url; ?>settle&integration_id=<?php echo $id; ?>" class="button">Settle Now</a></td>
                                <?php } else { ?>
                                    <td><span style='color:red'><?php echo $reason; ?></span></td>
                                    <td><a href="<?php echo $action_url; ?>settle&integration_id=<?php echo $id; ?>" disabled="disabled" class="button">N/A</a></td>
                                <?php } ?>
                            </tr>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td>MID</td>
                                <td colspan="2"><?php echo $MerchantIdentity->getRemoteID() ?: 'N/A'; ?></td>
                            </tr>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td>Created</td>
                                <td colspan="2"><?php echo $MerchantIdentity->getCreateDate() ?: 'N/A'; ?></td>
                            </tr>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td>Updated</td>
                                <td colspan="2"><?php echo $MerchantIdentity->getUpdateDate() ?: 'N/A'; ?></td>
                            </tr>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td colspan="3">
                                    <pre><?php echo $IntegrationRow->getNotes() ?: "No Notes"; ?></pre>
                                </td>
                            </tr>
                        </table>
                    </fieldset>

                <?php } ?>

            </fieldset>

            <?php } else { ?>

            <fieldset class="themed" style="max-width: 59em;">
                <?php
                $integration_id = $_GET['integration_id'];
                $IntegrationRow = IntegrationRow::fetchByID($integration_id);
                $MerchantIdentity = $IntegrationRow->getMerchantIdentity($Merchant);

                ?>

                <legend>Provision Now: <?php echo $IntegrationRow->getName(); ?></legend>
                <table class="table-merchant-info themed" style="float: left; min-width: 27em;">
                    <tr>
                        <th>Action</th>
                        <th>Notes</th>
                    </tr>
                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                        <td><button type="submit">Provision <br/> <?php echo $Merchant->getName(); ?></button> </td>
                        <td>
                            <pre><?php echo $IntegrationRow->getNotes() ?: "No Notes"; ?></pre>
                        </td>
                    </tr>
                </table>
            </fieldset>
            <?php } ?>

        </form>
    </section>