<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/29/2016
 * Time: 1:25 PM
 */
namespace Order\View;

use Dompdf\Exception;
use Merchant\Model\MerchantFormRow;
use Integration\Model\Ex\IntegrationException;
use Integration\Model\IntegrationRow;
use Merchant\Model\MerchantRow;
use Order\Model\OrderRow;
use System\Arrays\Locations;
use User\Session\SessionManager;
use View\AbstractView;

class ChargeView extends AbstractView
{
    /** @var MerchantFormRow */
    private $form;
    /** @var MerchantRow */
    private $merchant;

    public function __construct($formUID=null)    {
        if($formUID) {
            $OrderForm = MerchantFormRow::fetchByUID($formUID);
        } else {
            $OrderForm = MerchantFormRow::fetchGlobalForm();
        }
        $this->form = $OrderForm;

        $SessionManager = new SessionManager();
        $SessionUser = $SessionManager->getSessionUser();
        $merchant_id = $OrderForm->getMerchantID();
        if($merchant_id !== null) {
            if(!$SessionUser->hasAuthority('ROLE_ADMIN')) {
                if(!in_array($merchant_id, $SessionUser->getMerchantList()))
                    throw new \Exception("Invalid authorization to use form uid: " . $OrderForm->getUID());
            }
        } else {
            // Assign the first merchant id from the user's list
            $list = $SessionUser->getMerchantList();
            if(sizeof($list)==0)
                throw new \Exception("No merchants assigned to user");
            $merchant_id = $list[0];
        }

        $this->merchant = MerchantRow::fetchByID($merchant_id);
        parent::__construct($OrderForm->getTitle() . ' - ' . $this->merchant->getShortName());
    }

    public function renderHTMLBody(Array $params) {
        $MerchantRow = $this->merchant;
        /** @var MerchantFormRow $OrderForm */
        $OrderForm = $this->form;

        // Render Page
        $odd = false;
        $SessionManager = new SessionManager();
        $SessionUser = $SessionManager->getSessionUser();

        $LASTPOST = array();
        if(isset($_SESSION['order/charge.php']))
            $LASTPOST = $_SESSION['order/charge.php'];

        $Theme = $this->getTheme();
        $Theme->renderHTMLBodyHeader();

        if(!@$params['iframe']) {
            $Theme->addPathURL('order',               'Transactions');
            $Theme->addPathURL('order/charge.php',    $OrderForm->getTitle() . ' - ' . $MerchantRow->getShortName());
            $Theme->printHTMLMenu('order-charge');
        }

        $action_url = 'order/charge.php';

?>
        <article class="themed">
            <section class="content">

                <?php if($this->hasMessage()) echo "<h5>", $this->getMessage(), "</h5>"; ?>

                <form name="form-transaction-charge" class="<?php echo $OrderForm->getFormClasses(); ?> payment-method-keyed payment-method-card themed" method="POST">
                    <input type="hidden" name="convenience_fee_flat" value="<?php echo $MerchantRow->getConvenienceFeeFlat(); ?>" />
                    <input type="hidden" name="convenience_fee_limit" value="<?php echo $MerchantRow->getConvenienceFeeLimit(); ?>" />
                    <input type="hidden" name="convenience_fee_variable_rate" value="<?php echo $MerchantRow->getConvenienceFeeVariable(); ?>" />
                    <input type="hidden" name="merchant_id" value="<?php echo $MerchantRow->getID(); ?>" />
                    <input type="hidden" name="form_uid" value="<?php echo $OrderForm->getUID(); ?>" />

                    <fieldset class="inline-block-on-layout-full" style="min-width:48%; ">
                        <div class="legend">Choose a Payment Method</div>
                        <table class="table-payment-method themed" style="float: left;">
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?> required">
                                <td>
                                    <select name="entry_mode" class="" required autofocus title="Choose an entry method">
                                        <!--                        <option value="">Choose a method</option>-->
                                        <option value="Keyed">Keyed Card</option>
                                        <option value="Swipe">Swipe Card</option>
                                        <option value="Check">e-Check</option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                    </fieldset>

                    <fieldset class="inline-block-on-layout-full" style="min-width:48%;">
                        <div class="legend">Available Order Forms</div>
                        <table class="table-choose-merchant themed" style="float: left;">
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?> required">
                                <td>
                                    <select name="change_form_url" class=""
                                        title="Select a charge form template">
                                        <option value="">Switch Templates</option>
                                        <?php

//                                        if($SessionUser->hasAuthority('ROLE_ADMIN')) {
//                                            echo '<option value="">Choose an Order Form (as Admin ', $SessionUser->getUsername(), ')</option>';
//                                            $MerchantFormQuery = MerchantFormRow::queryAll();
//                                        } else {
                                            $MerchantFormQuery = MerchantFormRow::queryAvailableForms($SessionUser->getID());
//                                        }
                                        foreach ($MerchantFormQuery as $Form) {
                                            echo "\n\t\t\t\t\t\t\t<option",
                                            ($Form->getID() === $OrderForm->getID() ? ' selected="selected" value=""' :
                                            " value='?form_uid=" . $Form->getUID() . "&merchant_id=" . $MerchantRow->getID() . "'"),
                                            ">",
                                                $Form->getTitle(),
                                            "</option>";
                                        }
                                        ?>
                                    </select>
                                    <a href="merchant/form.php?uid=<?php echo $OrderForm->getUID(); ?>" style="float: right; display: inline-block; padding: 2px 8px;">
                                        <div class="app-button app-button-edit" style="font-size: 24px;"></div>
                                    </a>
                                </td>
                            </tr>
                        </table>
                    </fieldset>

                    <fieldset class="form-payment-method-credit inline-block-on-layout-full show-on-payment-method-card" style="min-width:48%; min-height: 21em;">
                        <div class="legend">Cardholder Information</div>
                        <table class="table-transaction-charge themed">
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?> required">
                                <td class="name">First Name</td>
                                <td><input type="text" name="payee_first_name" placeholder="First Name" required /></td>
                            </tr>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?> required">
                                <td class="name">Last Name</td>
                                <td><input type="text" name="payee_last_name" placeholder="Last Name" required /></td>
                            </tr>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?> required">
                                <td class="name">Card Number</td>
                                <td><input type="text" name="card_number" placeholder="xxxxxxxxxxxxxxxx" required /></td>
                            </tr>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?> required">
                                <td class="name">Card Type</td>
                                <td>
                                    <select name="card_type" required title="Choose a Card Type">
                                        <option value="">Choose an option</option>
                                        <option title="Visa">Visa</option>
                                        <option title="MasterCard">MasterCard</option>
                                        <option title="Amex">Amex</option>
                                        <option title="Discover">Discover</option>
                                    </select>
                                </td>
                            </tr>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">**CVV</td>
                                <td><input type="number" name="card_cvv2" placeholder="xxxx" autocomplete="off" style="width: 4em;" /></td>
                            </tr>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?> required">
                                <td class="name">Expiration</td>
                                <td>
                                    <select name='card_exp_month' id='expireMM' required title="Choose a card expiration month">
                                        <option value=''>Month</option>
                                        <option value='01'>January</option>
                                        <option value='02'>February</option>
                                        <option value='03'>March</option>
                                        <option value='04'>April</option>
                                        <option value='05'>May</option>
                                        <option value='06'>June</option>
                                        <option value='07'>July</option>
                                        <option value='08'>August</option>
                                        <option value='09'>September</option>
                                        <option value='10'>October</option>
                                        <option value='11'>November</option>
                                        <option value='12'>December</option>
                                    </select>
                                    <select name='card_exp_year' id='expireYY' required title="Choose an expiration year">
                                        <option value=''>Year</option>
                                        <?php for($i=16; $i<64; $i++) { ?>
                                            <option value='<?php echo $i; ?>'>20<?php echo $i; ?></option>
                                        <?php } ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <div class="credit-image"></div>

                                        <span style="font-size: x-small; color: grey">
                                            **The CVV Number ("Card Verification Value") on your credit card <br/>
                                            or debit card is a 3-4 digit number on credit and debit cards.
                                        </span>
                                </td>
                            </tr>

                        </table>
                    </fieldset>


                    <div class="swipe-fullscreen-box-container show-on-payment-method-swipe">
                        <fieldset class="themed swipe-fullscreen-box " style="min-width:48%; padding: 8px;">
                            <div class="legend alert reader-status">Please swipe your card now</div>
                            <br />

                            <div>
                                <textarea name="card_track" rows="12" placeholder="[MagTrack Data will appear here]" style="font-size: 1.3em; width: 90%;" ><?php // echo @$LASTPOST['card_track']; ?></textarea>
                                <br />
                                <input type="button" class='submit-button themed' value="Close" onclick="this.form.classList.add('swipe-input-successful'); return false;" />
                            </div>

                            <br />
                        </fieldset>
                    </div>

                    <fieldset class="form-payment-method-check inline-block-on-layout-full show-on-payment-method-check" style="min-width:48%; min-height: 21em;">
                        <div class="legend">e-Check Information</div>
                        <table class="table-transaction-charge themed">
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?> required">
                                <td class="name">Account Name</td>
                                <td><input type="text" name="check_account_name" placeholder="" required /></td>
                            </tr>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?> required">
                                <td class="name">Account Type</td>
                                <td>
                                    <select name="check_account_type" required title="Choose a Checking Account Type">
                                        <option value="">Choose an option</option>
                                        <option>Checking</option>
                                        <option>Savings</option>
                                    </select>
                                </td>
                            </tr>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?> required" style="color: #000092;">
                                <td class="name">Account Number</td>
                                <td><input type="text" name="check_account_number" placeholder="" required /></td>
                            </tr>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?> required" style="color: #920000;">
                                <td class="name">Routing Number</td>
                                <td><input type="text" name="check_routing_number" placeholder="" required /></td>
                            </tr>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?> required" style="color: #009200;">
                                <td class="name">Check Number</td>
                                <td><input type="text" name="check_number" placeholder="" /></td>
                            </tr>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">Check Type</td>
                                <td>
                                    <select name="check_type" required title="Choose a Check Type">
                                        <option value="">Choose an option</option>
                                        <option>Personal</option>
                                        <option>Business</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <div class="check-image"></div>
                                </td>
                            </tr>
                        </table>
                    </fieldset>

                    <fieldset class="inline-block-on-layout-full" style="min-width:48%; min-height: 21em;">
                        <div class="legend">Customer Fields</div>
                        <table class="table-transaction-charge themed" style="float: left;">
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?> required">
                                <td class="name">Payment Amount</td>
                                <td>
                                    <input type="text" name="amount" value=""  size="6" placeholder="x.xx" required autofocus/>
                                </td>
                            </tr>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">Customer Name</td>
                                <td>
                                    <input type="text" name="customer_first_name" placeholder="First Name" size="12" />
                                    <input type="text" name="customermi" placeholder="MI" size="1" /> <br/>
                                    <input type="text" name="customer_last_name" placeholder="Last Name" size="12" />
                                </td>
                            </tr>
                            <?php if($OrderForm->hasField('payee_receipt_email')) { ?>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">Email</td>
                                <td><input type="text" name="payee_reciept_email" placeholder="xxx@xxx.xxx" <?php echo $OrderForm->isFieldRequired('payee_receipt_email') ? 'required ' : ''; ?>/></td>
                            </tr>
                            <?php } ?>
                            <?php if($OrderForm->hasField('payee_phone_number')) { ?>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">Phone</td>
                                <td><input type="text" name="payee_phone_number" placeholder="xxx-xxx-xxxx" <?php echo $OrderForm->isFieldRequired('payee_phone_number') ? 'required ' : ''; ?> /></td>
                            </tr>
                            <?php } ?>

                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">Billing Address</td>
                                <td>
                                    <input type="text" name="payee_address" placeholder="Address" />
                                    <br/>
                                    <input type="text" name="payee_address2" placeholder="Address #2" />
                                </td>
                            </tr>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">Billing Zip/State</td>
                                <td>
                                    <input type="text" name="payee_zipcode" placeholder="ZipCode" size="6" class="zip-lookup-field-zipcode" />
                                    <select name="payee_state" style="width: 7em;" class='zip-lookup-field-state-short' title="Choose a billing state">
                                        <option value="">State</option>
                                        <?php
                                        foreach(Locations::$STATES as $code => $name)
                                            echo "\n\t<option value='", $code, "' ",
                                                //                                        ($code === @$LASTPOST['payee_state'] ? ' selected="selected"' : ''),
                                            ">", $name, "</option>";
                                        ?>
                                    </select>
                                </td>
                            </tr>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">Billing City</td>
                                <td>
                                    <input type="text" name="payee_city" size="10" placeholder="City" class='zip-lookup-field-city-title-case' />
                                </td>
                            </tr>

                            <?php if($OrderForm->hasField('customer_id')) { ?>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">Customer&nbsp;ID#</td>
                                <td><input type="text" name="customer_id" placeholder="Customer ID" <?php echo $OrderForm->isFieldRequired('customer_id') ? 'required ' : ''; ?>/></td>
                            </tr>
                            <?php } ?>

                            <?php if($OrderForm->hasField('invoice_number')) { ?>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">Invoice&nbsp;ID#</td>
                                <td><input type="text" name="invoice_number" placeholder="Invoice Number" <?php echo $OrderForm->isFieldRequired('invoice_number') ? 'required ' : ''; ?>/></td>
                            </tr>
                            <?php } ?>


                            <?php
                                foreach($OrderForm->getAllCustomFields(false) as $field) {
                                    $title = $OrderForm->getCustomFieldName($field);
                                    ?>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name"><?php echo $title; ?></td>
                                <td><input type="text" name="<?php echo $field; ?>" placeholder="<?php echo $title; ?>" <?php echo $OrderForm->isFieldRequired($field) ? 'required ' : ''; ?>/></td>
                            </tr>
                                    <?php
                                }
                            ?>

                        </table>
                    </fieldset>


                    <fieldset class="inline-block-on-layout-full" style="clear: both; min-width: 48%; min-height: 12em;" <?php echo $OrderForm->isRecurAvailable() ? '' : 'disabled '; ?>>
                        <div class="legend">Re-bill Schedule</div>
                        <table class="table-transaction-charge themed" style="float: left; width: 48%;">
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">Re-bill Count</td>
                                <td>
                                    <select name='recur_count' title="The number of times an order will automatically re-bill">
                                        <option value="0">Disabled</option>
                                        <?php
                                        for($i=1; $i<=99; $i++)
                                            echo "\n\t<option ",
        //                                        @$LASTPOST['recur_count'] == $i ? 'selected="selected"' : '',
                                            ">", $i, "</option>";
                                        ?>
                                    </select>
                                </td>
                            </tr>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">Re-bill Amount</td>
                                <td class="value"><input type="text" name="recur_amount" placeholder="x.xx" size="6" required="required"/></td>
                            </tr>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">Re-bill Frequency</td>
                                <td>
                                    <select name='recur_frequency' title="Choose the frequency in which this order will automatically re-bill">
                                        <?php
                                        //                                    if(empty($LASTPOST['recur_frequency']))
                                        //                                        $LASTPOST['recur_frequency'] = 'Monthly';
                                        foreach(OrderRow::$ENUM_RUN_FREQUENCY as $type => $name)
                                            echo "\n\t<option value='", $type, "'",
        //                                        @$LASTPOST['recur_frequency'] === $type ? ' selected="selected"' : '',
                                            ">", $name, "</option>";
                                        ?>
                                    </select>
                                </td>
                            </tr>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">First Re-bill Date</td>
                                <td><input type="date" name="recur_next_date" required="required" style="max-width: 10em;"/></td>
                            </tr>
                        </table>
                    </fieldset>

                    <fieldset class="inline-block-on-layout-full" style="clear: both; min-width: 48%; min-height: 12em;">
                        <div class="legend">Submit Order</div>
                        <table class="table-transaction-charge themed" style="width: 48%;">
                            <!--                        <tr class="row---><?php //echo ($odd=!$odd)?'odd':'even';?><!--">-->
                            <!--                            <td class="name">Convenience Fee</td>-->
                            <!--                            <td><input type="text" size="6" name="convenience_fee_total" value="$0.00" disabled="disabled" /></td>-->
                            <!--                        </tr>-->
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">
                                    Total Charge Amount
                                </td>
                                <td>
                                    <input type="text" size="6" name="total_amount" value="$0.00" disabled="disabled" />
                                </td>
                            </tr>
                            <tr>
                                <td></td>
                                <td style="">
                                    <span class="conv-fee-text">*Includes Conv. Fee</span>
                                    <div class="conv-fee-pop-up-box">
                                        *Charge includes a
                                        <br />
                                        convenience fee of
                                        <br />
                                        <br />
                                        <input type="text" size="6" name="convenience_fee" value="$0.00" disabled="disabled" style="float: right;" />
                                    </div>
                                </td>
                            </tr>
                            <!--                    <tr class="row---><?php //echo ($odd=!$odd)?'odd':'even';?><!--">-->
                            <!--                        <td class="name">Method</td>-->
                            <!--                        <td><input type="text" name="entry_method" value="Keyed" disabled="disabled" /></td>-->
                            <!--                    </tr>-->
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">Submit</td>
                                <td>
                                    <input type="submit" value="Pay Now" class="themed" />
                                </td>
                            </tr>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">Reset</td>
                                <td>
                                    <input type="reset" value="Reset" class="themed" onclick="return confirm('Are you sure you want to reset all form values?');" />
                                </td>
                            </tr>
                        </table>
                    </fieldset>

                </form>
            </section>
        </article>

<?php
        if(sizeof($LASTPOST) > 0) {
            $json = json_encode($LASTPOST);
            ?>
        <script>
            var LASTPOST = <?php echo $json; ?>;
            var chargeForm = document.getElementsByName('form-transaction-charge')[0];
            for(var key in LASTPOST) {
                if(LASTPOST.hasOwnProperty(key)) {
                    var value = LASTPOST[key];
                    console.log("Updating form with saved value: " + key);
                    chargeForm[key].value = value;
                }
            }
            if(LASTPOST.card_track)
                chargeForm.classList.add('swipe-input-successful');
        </script>
        <?php
        }

        if(!@$params['iframe'])
            $Theme->renderHTMLBodyFooter();
    }

    public function processFormRequest(Array $post) {
        $Order = null;
        try {
            if(isset($_SESSION['order/charge.php']['order_id']))
                $post['order_id'] = $_SESSION['order/charge.php']['order_id'];

            $_SESSION['order/charge.php'] = $post;
            $Integration = IntegrationRow::fetchByID($post['integration_id']);
            $Merchant = MerchantRow::fetchByID($post['merchant_id']);
            $MerchantIdentity = $Integration->getMerchantIdentity($Merchant);

            $SessionManager = new SessionManager();
            $SessionUser = $SessionManager->getSessionUser();
            if($SessionUser->hasAuthority('ROLE_ADMIN')) {

            } else {
                if(!$SessionUser->hasMerchant($Merchant->getID()))
                    throw new IntegrationException("User does not have authority");
            }
            $Order = $MerchantIdentity->createOrResumeOrder($post);
            $_SESSION['order/charge.php']['order_id'] = $Order->getID();

            $Transaction = $MerchantIdentity->submitNewTransaction($Order, $SessionUser, $post);

            $this->setSessionMessage(
                "<div class='info'>Success: " . $Transaction->getStatusMessage() . "</div>"
            );
            header('Location: /order/receipt.php?uid=' . $Order->getUID(false));
            unset($_SESSION['order/charge.php']);
            die();

        } catch (\Exception $ex) {
            $this->setSessionMessage(
                "<div class='error'>Error: " . $ex->getMessage() . "</div>"
            );
            header('Location: /order/charge.php');
            if($Order)
                OrderRow::delete($Order);
            // Delete pending orders that didn't complete
            die();
        }
    }

    protected function renderHTMLHeadLinks() {
        parent::renderHTMLHeadLinks();
        echo <<<HEAD
        <script src="order/view/assets/charge.js"></script>
        <script src="https://clevertree.github.io/zip-lookup/zip-lookup.min.js" type="text/javascript" ></script>
        <link href='order/view/assets/charge.css' type='text/css' rel='stylesheet' />
        <link href='order/view/assets/template/full.charge.css' type='text/css' rel='stylesheet' />
        <link href='order/view/assets/template/simple.charge.css' type='text/css' rel='stylesheet' />
HEAD;

    }

}