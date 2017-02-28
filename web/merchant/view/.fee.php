<?php
use Integration\Model\IntegrationRow;
use Order\Fee\Model\MerchantFeeRow;
use Merchant\Model\MerchantRow;

/**
 * @var \Merchant\View\MerchantView $this
 * @var PDOStatement $MerchantQuery
 **/
$Merchant = $this->getMerchant();
$odd = false;
$action_url = 'merchant?uid=' . $Merchant->getUID() . '&action=';

$Theme = $this->getTheme();
$Theme->addPathURL('merchant',      'Merchants');
$Theme->addPathURL($action_url,     $Merchant->getShortName());
$Theme->addPathURL($action_url.'provision',     'Provision');
$Theme->renderHTMLBodyHeader();
$Theme->printHTMLMenu('merchant-provision', $action_url);


?>


<article class="themed">
     <section class="content">

            <?php if($SessionManager->hasMessage()) echo "<h5>", $SessionManager->popMessage(), "</h5>"; ?>

            <form class="form-view-merchant themed" method="POST">

                <fieldset class="themed">
                    <div class="legend">Merchant Information</div>

                    <div class="page-buttons order-page-buttons hide-on-print">
                        <a href="<?php echo $action_url; ?>view" class="page-button page-button-view">
                            <div class="app-button large app-button-view" ></div>
                            View Merchant
                        </a>
                        <a href="<?php echo $action_url; ?>edit" class="page-button page-button-edit">
                            <div class="app-button large app-button-edit" ></div>
                            Edit Merchant
                        </a>
                        <a href="<?php echo $action_url; ?>fee" class="page-button page-button-fee disabled">
                            <div class="app-button large app-button-fee" ></div>
                            Rates & Fees
                        </a>
                        <?php if($SessionUser->hasAuthority('ADMIN', 'PROVISION')) { ?>
                            <a href="<?php echo $action_url; ?>provision" class="page-button page-button-provision">
                                <div class="app-button large app-button-provision" ></div>
                                Provision
                            </a>
                        <?php } ?>
                    </div>

                    <hr/>


                    <table class="table-merchant-info themed striped-rows" style="width: 100%;">
                        <tr>
                            <th colspan="2" class="section-break">Information</th>
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

                <fieldset>
                    <div class="legend">Set Rates and Fees for Merchant: <?php echo $Merchant->getName(); ?></div>
                    <table class="table-merchant-fee-info themed striped-rows" style="width: 100%;">
                        <tr>
                            <th>ID</th>
                            <th>Type</th>
                            <th>Entry</th>
                            <th>Flat</th>
                            <th>Variable</th>
                            <th>Limit</th>
<!--                            <th>Comment</th>-->
                            <th>Account</th>
                            <th>Integration</th>
                            <th>Remove</th>
                        </tr>
                        <?php $calcTableRow = function($id, $amount_flat, $amount_variable, $amount_limit, $type, $entry_mode, $merchant_id, $merchant_fee_account_id, $integration_id, $comment) use ($Merchant) {
                            $disabled = ''; // $merchant_id ? '' : 'disabled="disabled"';
                            ?>
                            <tr>
                                <td><?php echo $id; ?></td>
                                <td>
                                    <select name="type">
                                        <?php
                                        foreach(\Order\Fee\Model\FeeRow::$FEE_TYPES as $otype => $oname)
                                            echo "\n\t\t\t<option value='{$otype}'",
                                            ($type === $otype ? 'selected="selected"' : ''),
                                            ">{$oname}</option>";
                                        ?>
                                    </select>
                                </td>
                                <td>
                                    <select name="entry_mode" <?php echo $disabled; ?>>
                                        <option>Any</option>
                                        <?php
                                        foreach(array('Keyed', 'Swipe', 'Check') as $otype => $oname)
                                            echo "\n\t\t\t<option value='{$otype}'",
                                            ($entry_mode === $otype ? 'selected="selected"' : ''),
                                            ">{$oname}</option>";
                                        ?>
                                    </select>
                                </td>
                                <td><input type="text" name="amount_flat" value="<?php echo $amount_flat; ?>" size="4" <?php echo $disabled; ?>/></td>
                                <td><input type="text" name="amount_variable" value="<?php echo $amount_variable; ?>" size="4" <?php echo $disabled; ?>/></td>
                                <td><input type="text" name="amount_limit" value="<?php echo $amount_limit; ?>" size="4" <?php echo $disabled; ?>/></td>
                                <td>
                                    <select name="merchant_fee_account_id" <?php echo $disabled; ?>>
                                        <option>Default</option>
                                        <?php
                                        $MerchantQuery = MerchantRow::queryAll();
                                        foreach($MerchantQuery as $MerchantAccount)
                                            /** @var \Merchant\Model\MerchantRow $MerchantAccount */
                                            echo "<option value='", $MerchantAccount->getID(), "'",
                                            ($MerchantAccount->getID() === $merchant_fee_account_id ? 'selected="selected"' : ''),
                                            ">", $MerchantAccount->getShortName(), "</option>";
                                        ?>
                                    </select>
                                </td>
                                <td>
                                    <select name="integration_id" <?php echo $disabled; ?>>
                                        <option>Any</option>
                                        <?php
                                        $IntegrationQuery = IntegrationRow::queryAll();
                                        foreach($IntegrationQuery as $IntegrationRow) {
                                            /** @var IntegrationRow $IntegrationRow */
                                            if($IntegrationRow->getAPIType() !== 'production')
                                                continue;
                                            echo "<option value='", $IntegrationRow->getID(), "'",
                                            ($IntegrationRow->getID() === $integration_id ? 'selected="selected"' : ''),
                                            ">", $IntegrationRow->getName(), "</option>";
                                        }
                                        ?>
                                    </select>
                                </td>
                                <td><button onclick="this.parentNode.parentNode.outerHTML = '';" <?php echo $disabled; ?>>X</button></td>
                            </tr>

                        <?php } ?>


                        <?php
                        $FeeQuery = MerchantFeeRow::queryAll($Merchant->getID());
                        foreach($FeeQuery as $MerchantFee) {
                            /** @var MerchantFeeRow $MerchantFee */
                            $calcTableRow(
                                $MerchantFee->getID(),
                                $MerchantFee->getAmountFlat(),
                                $MerchantFee->getAmountVariable(),
                                $MerchantFee->getAmountLimit(),
                                $MerchantFee->getType(),
                                $MerchantFee->getEntryMode(),
                                $MerchantFee->getMerchantID(),
                                $MerchantFee->getMerchantFeeAccountID(),
                                $MerchantFee->getIntegrationID(),
                                $MerchantFee->getComment()
                            );
                        }

                        for($i=0; $i<6; $i++)
                            $calcTableRow(
                                'New',
                                '0.00',
                                '0.00',
                                '0.00',
                                null,
                                null,
                                null,
                                null,
                                null,
                                null
                            );

                        ?>



                        <tr >
                            <td colspan="8">
                                <input type="submit" value="Update Fee Schedule" class="themed"/>
                            </td>
                        </tr>
                    </table>
                </fieldset>

            </form>
        </section>
    </article>