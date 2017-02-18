<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/29/2016
 * Time: 1:25 PM
 */
namespace Order\View;

use Integration\Model\IntegrationRow;
use Merchant\Model\MerchantFormRow;
use Merchant\Model\MerchantRow;
use Order\Model\OrderRow;
use Subscription\Model\SubscriptionRow;
use System\Config\SiteConfig;
use User\Session\SessionManager;
use View\AbstractView;

class OrderView extends AbstractView
{
    const VIEW_PATH = 'order';
    const VIEW_NAME = 'Transactions';

    private $_order;
    private $_action;

    public function __construct($id, $action=null) {
        $this->_action = strtolower($action) ?: 'view';
        $this->_order = OrderRow::fetchByID($id);
        if(!$this->_order)
            throw new \InvalidArgumentException("Invalid Order ID: " . $id);
        parent::__construct();
    }

    /** @return OrderRow */
    public function getOrder() { return $this->_order; }

    public function renderHTMLBody(Array $params) {
        // Render Page
        switch($this->_action) {
            case 'download':
                include('.download.php');
                break;
            case 'receipt':
            case 'email':
            case 'print':
            case 'view':
                $this->renderViewHTMLBody($params);
                break;
            case 'edit':
                include('.edit.php');
                break;
            case 'delete':
                include('.delete.php');
                break;
            case 'change':
                include('.change.php');
                break;
            default:
                throw new \InvalidArgumentException("Invalid Action: " . $this->_action);
        }
    }

    public function processFormRequest(Array $post) {
        $action = $this->_action;
        if(!empty($post['action']))
            $action = strtolower($post['action']);

        $Order = $this->getOrder();
        $Integration = IntegrationRow::fetchByID($Order->getIntegrationID());
        $Merchant = MerchantRow::fetchByID($Order->getMerchantID());
        $MerchantIdentity = $Integration->getMerchantIdentity($Merchant);

        $SessionManager = new SessionManager();
        $SessionUser = $SessionManager->getSessionUser();

        try {
            // Render Page
            switch($action) {
//                case 'edit':
//                    $EditOrder = $this->getOrder();
//                    $EditOrder->updateFields($post)
//                        ? $this->setSessionMessage("Order Updated Successfully: " . $EditOrder->getUID())
//                        : $this->setSessionMessage("No changes detected: " . $EditOrder->getUID());
//                    header('Location: order?id=' . $EditOrder->getID() . '');
//                    die();

                case 'delete':
                    print_r($post);
                    die();

                case 'change':
                    print_r($post);
                    die();

                case 'cancel':
                    if(!SiteConfig::$SITE_LIVE)
                        throw new \Exception("Live Transaction Functions are disabled");

                    $message = "Canceled by " . $SessionUser->getUsername();
                    $Subscription = SubscriptionRow::fetchByID($Order->getSubscriptionID());
                    $MerchantIdentity->cancelSubscription($Subscription, $SessionUser, $message);

                    $SessionManager->setMessage(
                        "<div class='info'>Success: ".$Subscription->getStatusMessage() . "</div>"
                    );
                    header('Location: /order/receipt.php?uid=' . $Order->getUID(false) . '');
                    die();

                case 'void':
                    if(!SiteConfig::$SITE_LIVE)
                        throw new \Exception("Live Transaction Functions are disabled");

                    if(!$SessionUser->hasAuthority('ROLE_VOID_CHARGE', 'ROLE_ADMIN'))
                        throw new \Exception("Invalid Authority to Void Charges");

                    $Transaction = $MerchantIdentity->voidTransaction($Order, $SessionUser, $post);

                    $SessionManager->setMessage(
                        "<div class='info'>Success: ".$Transaction->getStatusMessage() . "</div>"
                    );
                    header('Location: /order/receipt.php?uid=' . $Order->getUID(false) . '');
                    die();

                case 'return':
                    if(!SiteConfig::$SITE_LIVE)
                        throw new \Exception("Live Transaction Functions are disabled");

                    if(!$SessionUser->hasAuthority('ROLE_RETURN_CHARGE', 'ROLE_ADMIN'))
                        throw new \Exception("Invalid Authority to Return Charges");

//                    $partial_return_amount = $post['partial_return_amount'];
                    $Transaction = $MerchantIdentity->returnTransaction($Order, $SessionUser, $post);

                    $SessionManager->setMessage(
                        "<div class='info'>Success: ".$Transaction->getStatusMessage() . "</div>"
                    );
                    header('Location: /order/receipt.php?uid=' . $Order->getUID(false) . '');
                    die();

                case 'reverse':
                    if(!$SessionUser->hasAuthority('ROLE_RETURN_CHARGE', 'ROLE_ADMIN'))
                        throw new \Exception("Invalid Authority to Return Charges");

                    $Transaction = $MerchantIdentity->reverseTransaction($Order, $SessionUser, $post);

                    $SessionManager->setMessage(
                        "<div class='info'>Success: ".$Transaction->getStatusMessage() . "</div>"
                    );
                    header('Location: /order/receipt.php?uid=' . $Order->getUID(false) . '');
                    die();

                default:
                    throw new \InvalidArgumentException("Invalid Action: " . $this->_action);
            }

        } catch (\Exception $ex) {
            $SessionManager->setMessage(
                "<div class='error'>Error: ".$ex->getMessage() . "</div>"
            );
            header('Location: /order/receipt.php?uid=' . $Order->getUID(false) . '&action='.$this->_action.'&message=' . $ex->getMessage()  . '');
            die();
        }
    }

    private function renderViewHTMLBody($params)
    {
        $Order = $this->getOrder();
        $Transaction = $Order->fetchAuthorizedTransaction();
        $Merchant = MerchantRow::fetchByID($Order->getMerchantID());
        $odd = true;
        $action_url = 'order/receipt.php?uid=' . $Order->getUID(false) . '&action=';
        $action_url_pdf = 'order/pdf.php?uid=' . $Order->getUID(false);
        $SessionManager = new SessionManager();
        $SessionUser = $SessionManager->getSessionUser();

// Get Timezone diff
        $offset = $SessionUser->getTimeZoneOffset('now');


        $Theme = $this->getTheme();
        $Theme->addPathURL('order',        'Transactions');
        $Theme->addPathURL($action_url,    $Order->getUID(true));
        $Theme->renderHTMLBodyHeader();
        $Theme->printHTMLMenu('order-view', $action_url);
        $SITE_CUSTOMER_NAME = SiteConfig::$SITE_DEFAULT_CUSTOMER_NAME;
        ?>

        <article class="themed">

            <section class="content">

                <?php if($SessionManager->hasMessage()) echo "<h5>", $SessionManager->popMessage(), "</h5>"; ?>


                <form name="form-order-view" id="form-order-view" class="themed" method="POST">
                    <fieldset style="padding: 1em;">


                        <div class="page-buttons order-page-buttons hide-on-print">
                            <a onclick="window.print(); return false;" class="page-button page-button-print">
                                <div class="app-button large app-button-print" ></div>
                                Print
                            </a>
                            <a href="<?php echo $action_url_pdf; ?>" class="page-button page-button-download">
                                <div class="app-button large app-button-download" ></div>
                                Download
                            </a>
                            <a onclick="window.void(); return false;" class="page-button page-button-void disabled">
                                <div class="app-button large app-button-void" ></div>
                                Void
                            </a>
                            <a onclick="window.refund(); return false;" class="page-button page-button-refund disabled">
                                <div class="app-button large app-button-refund" ></div>
                                Return
                            </a>
                        </div>

                        <hr/>

                        <div style="text-align: center; ">
                            <table class="table-transaction-info themed small inline-block-on-layout-full striped-rows" style="width: 47%; display: block; vertical-align: top; text-align: left;">
                                <tbody>
                                <tr>
                                    <td colspan="2" class="legend">
                                        <?php echo $SITE_CUSTOMER_NAME; ?>: <?php echo $Order->getCustomerFullName() ?: $Order->getPayeeFullName(); ?>
                                    </td>
                                </tr>

                                <!-- Customer Information -->

                                <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                    <td class="name" style="width: 30%;">Payee Full Name</td>
                                    <td class="value"><?php echo $Order->getPayeeFullName(); ?></td>
                                </tr>

                                <?php if($Order->getPayeeAddress()) { ?>
                                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                        <td class="name" style="width: 30%;">Address</td>
                                        <td class="value"><?php echo $Order->getPayeeAddress(); ?></td>
                                    </tr>
                                <?php }  ?>

                                <?php if($Order->getPayeeCity()) { ?>
                                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                        <td class="name" style="width: 30%;">City</td>
                                        <td class="value"><?php echo $Order->getPayeeCity(); ?></td>
                                    </tr>
                                <?php }  ?>

                                <?php if($Order->getPayeeState()) { ?>
                                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                        <td class="name" style="width: 30%;">State</td>
                                        <td class="value"><?php echo $Order->getPayeeState(); ?></td>
                                    </tr>
                                <?php }  ?>

                                <?php if($Order->getPayeeZipCode()) { ?>
                                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                        <td class="name" style="width: 30%;">Zip Code</td>
                                        <td class="value"><?php echo $Order->getPayeeZipCode(); ?></td>
                                    </tr>
                                <?php }  ?>

                                <?php if($Order->getPayeePhone()) { ?>
                                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                        <td class="name" style="width: 30%;">Phone</td>
                                        <td class="value"><?php echo $Order->getPayeePhone(); ?></td>
                                    </tr>
                                <?php }  ?>

                                <?php if($Order->getPayeeEmail()) { ?>
                                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                        <td class="name" style="width: 30%;">Email</td>
                                        <td class="value"><?php echo $Order->getPayeeEmail(); ?></td>
                                    </tr>
                                <?php }  ?>

                        <?php if ($Order->getCardNumber()) { ?>

                                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                        <td class="name" style="width: 30%;">Credit Card</td>
                                        <td class="value"><?php echo $Order->getCardNumber() ? substr($Order->getCardNumber(), -16) : 'N/A'; ?></td>
                                    </tr>
                                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                        <td class="name" style="width: 30%;">Card Type</td>
                                        <td class="value"><?php echo $Order->getCardType(); ?></td>
                                    </tr>
                                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                        <td class="name" style="width: 30%;">Card Exp</td>
                                        <td class="value"><?php echo $Order->getCardExpMonth(), '/', $Order->getCardExpYear(); ?></td>
                                    </tr>

                        <?php } else  { ?>

                                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                        <td class="name" style="width: 30%;">Type</td>
                                        <td class="value"><?php echo $Order->getCheckType(); ?></td>
                                    </tr>
                                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                        <td class="name" style="width: 30%;">Account</td>
                                        <td class="value"><?php echo $Order->getCheckAccountNumber(); ?></td>
                                    </tr>
                                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                        <td class="name" style="width: 30%;">Routing</td>
                                        <td class="value"><?php echo $Order->getCheckRoutingNumber(); ?></td>
                                    </tr>
                                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                        <td class="name" style="width: 30%;">Account Type</td>
                                        <td class="value"><?php echo $Order->getCheckAccountType(); ?></td>
                                    </tr>
                                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                        <td class="name" style="width: 30%;">Check Num</td>
                                        <td class="value"><?php echo $Order->getCheckNumber(); ?></td>
                                    </tr>
                                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                        <td class="name" style="width: 30%;">Method</td>
                                        <td class="value"><?php echo ucfirst($Order->getEntryMode()); ?></td>
                                    </tr>
                        <?php } ?>

                                <!-- Built-in Order Fields -->

                                <?php if($Order->getUsername()) { ?>
                                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                        <td class="name" style="width: 30%;">User ID</td>
                                        <td class="value"><?php echo $Order->getUsername(); ?></td>
                                    </tr>
                                <?php }  ?>
                                <?php if($Order->getInvoiceNumber()) { ?>
                                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                        <td class="name" style="width: 30%;">Invoice</td>
                                        <td class="value"><?php echo $Order->getInvoiceNumber() ?: 'N/A'; ?></td>
                                    </tr>
                                <?php } ?>
                                <?php if($Order->getCustomerID()) { ?>
                                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                        <td class="name" style="width: 30%;"><?php echo $SITE_CUSTOMER_NAME; ?></td>
                                        <td class="value"><?php echo $Order->getCustomerID() ?: 'N/A' ?></td>
                                    </tr>
                                <?php } ?>

                                <!-- Custom Order Fields -->

                                <?php

                                $OrderForm = $Order->getFormID() ? MerchantFormRow::fetchByID($Order->getFormID()) : NULL;
                                foreach($Order->getCustomFieldValues() as $field=>$value) {
                                    $title = $field;
                                    if($OrderForm)
                                        $title = $OrderForm->getCustomFieldName($field);
                                    ?>
                                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                        <td class="name"><?php echo $title; ?></td>
                                        <td><?php echo $value; ?></td>
                                    </tr>
                                    <?php
                                }
                                ?>


                                <!-- Reference Number -->
                                <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                    <td colspan="2" class="name" style="width: 30%;">Reference Number</td>
                                </tr>
                                <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                    <td colspan="2"><?php echo $Order->getReferenceNumber(); ?></td>
                                </tr>

                                </tbody>
                            </table>


                            <table class="table-transaction-info themed small inline-block-on-layout-full striped-rows" style="width: 48%; display: block; vertical-align: top; text-align: left;">
                                <tbody>
                                    <tr>
                                        <td colspan="2" class="legend">
                                            Merchant: <?php echo $Merchant->getName(); ?>
                                        </td>
                                    </tr>
                                <?php $odd = true; ?>

                                    <!-- Merchant Location Information -->
                                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                        <td class="name" style="width: 30%;">Address</td>
                                        <td class="value"><?php echo $Merchant->getAddress(), $Merchant->getAddress2(); ?></td>
                                    </tr>
                                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                        <td class="name" style="width: 30%;">City</td>
                                        <td class="value"><?php echo $Merchant->getCity(); ?></td>
                                    </tr>
                                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                        <td class="name" style="width: 30%;">State</td>
                                        <td class="value"><?php echo $Merchant->getState(); ?></td>
                                    </tr>
                                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                        <td class="name" style="width: 30%;">Zip Code</td>
                                        <td class="value"><?php echo $Merchant->getZipCode(); ?></td>
                                    </tr>
                                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                        <td class="name" style="width: 30%;">Phone</td>
                                        <td class="value"><?php echo $Merchant->getTelephone(); ?></td>
                                    </tr>


                                    <!-- Date and Time -->
                                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                        <td class="name" style="width: 30%;">Date</td>
                                        <td class="value"><?php echo date("F jS, Y", strtotime($Order->getDate()) + $offset); ?></td>
                                    </tr>
                                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                        <td class="name" style="width: 30%;">Time</td>
                                        <td class="value"><?php echo date("g:i:s A", strtotime($Order->getDate()) + $offset); ?></td>
                                    </tr>
                                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                        <td class="name" style="width: 30%;">Time Zone</td>
                                        <td class="value"><?php echo str_replace('_', '', $SessionUser->getTimeZone()); ?></td>
                                    </tr>



                                    <!-- Totals and Fees -->
                                    <?php if ($Order->getConvenienceFee()) { ?>
                                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                            <td class="name" style="width: 30%;">Subtotal</td>
                                            <td class="value">$<?php echo $Order->getAmount(); ?></td>
                                        </tr>
                                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                            <td class="name" style="width: 30%;">Conv. Fee</td>
                                            <td class="value">$<?php echo $Order->getConvenienceFee(); ?></td>
                                        </tr>
                                    <?php } ?>

                                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                        <td class="name" style="width: 30%; font-size: larger;">Total</td>
                                        <td class="value" style="font-size: larger;">$<?php echo number_format($Order->getAmount()+$Order->getConvenienceFee(), 2); ?></td>
                                    </tr>

                                    <?php if ($Order->getTotalReturnedAmount() > 0) { ?>
                                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                            <td class="name" style="width: 30%;">Total Returned</td>
                                            <td class="value" style="color: red;">$<?php echo $Order->getTotalReturnedAmount(); ?></td>
                                        </tr>
                                    <?php } ?>

                                </tbody>
                            </table>
                        </div>

                    </fieldset>

                    <fieldset class="show-on-print" style="clear: both;">
                        <br/>
                        <br/>
                        <br/>
                        <hr style="height: 2px;">
                        <?php echo $SITE_CUSTOMER_NAME; ?> Signature
                    </fieldset>

                    <?php if ($Order->getSubscriptionCount() > 0) { ?>
                    <fieldset class="hide-on-print">
                        <div class="legend">Subscription Status</div>
                        <table class="table-results themed small cell-borders striped-rows">
                            <tr>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Frequency</th>
                                <th>Next Recurrence</th>
                                <th>Perform</th>
                            </tr>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td>$<?php echo $Order->getSubscriptionAmount(), ' (', $Order->getSubscriptionCount(),')'; ?></td>
                                <td><?php echo $Order->getSubscriptionStatus(), $Order->getSubscriptionMessage() ? ': ' : '', $Order->getSubscriptionMessage(); ?></td>
                                <td><?php echo $Order->getSubscriptionFrequency(); ?></td>
                                <td><?php echo date("Y M j g:i A", strtotime($Order->getSubscriptionNextDate()) + $offset); ?></td>
                                <td>
                                    <?php
                                    $disabled = $Order->getSubscriptionStatus() == 'Active' ? '' : " disabled='disabled'";
                                    echo "<input name='action' type='submit' value='Cancel'{$disabled}/>";
                                    ?>
                                </td>
                            </tr>
                        </table>
                    </fieldset>
                    <?php } ?>

                    <fieldset class="hide-on-print">
                        <div class="legend">Transaction History</div>
                        <table class="table-results themed small cell-borders striped-rows" style="width: 100%;">
                            <tr>
                                <th class="hide-on-layout-narrow">TID</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Fee</th>
                                <th>Action</th>
                                <th>Perform</th>
                            </tr>
                            <?php
                            /** @var \Order\Model\TransactionRow $Transaction */
                            $DB = \System\Config\DBConfig::getInstance();
                            $TransactionQuery = $DB->prepare(\Order\Model\TransactionRow::SQL_SELECT . "WHERE t.order_item_id = ? LIMIT 100");
                            /** @noinspection PhpMethodParametersCountMismatchInspection */
                            $TransactionQuery->setFetchMode(\PDO::FETCH_CLASS, \Order\Model\TransactionRow::_CLASS);
                            $TransactionQuery->execute(array($this->getOrder()->getID()));
                            $odd = false;
                            foreach($TransactionQuery as $Transaction) { ?>
                                <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                    <td class="hide-on-layout-narrow"><a href='/order/receipt.php?uid=<?php echo $Order->getUID(false); ?>'><?php echo $Transaction->getTransactionID(); ?></a></td>
                                    <td><?php echo date("M j g:i A", strtotime($Transaction->getTransactionDate()) + $offset); ?></td>
                                    <td>$<?php echo $Transaction->getAmount(); ?></td>
                                    <td>$<?php echo $Transaction->getServiceFee(); ?></td>
                                    <td>
                                        <a href="integration/request?id=<?php echo $Transaction->getIntegrationRequestID(); ?>">
                                            <?php echo $Transaction->getAction(); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <?php
                                        switch($Transaction->getAction()) {
                                            case 'Authorized':
                                                if($Order->getStatus() === 'Authorized') {
                                                    $disabled = $SessionUser->hasAuthority('ROLE_VOID_CHARGE', 'ROLE_ADMIN') ? '' : " disabled='disabled'";
                                                    echo <<<HTML
                                        <input name='action' type='submit' value='Void'`{$disabled} onclick='return confirmOrderViewAction("Void", event);'/>
HTML;
                                                }
                                                break;

                                            case 'Settled':
                                                if($Order->getStatus() === 'Settled' && !floatval($Order->getTotalReturnedAmount())) {
                                                    $disabled = $SessionUser->hasAuthority('ROLE_RETURN_CHARGE', 'ROLE_ADMIN') ? '' : " disabled='disabled'";
                                                    echo <<<HTML
                                        <input name='partial_return_amount' size="10" placeholder="Return Amount" />
                                        <input name='action' type='submit' value='Return'{$disabled} onclick='return confirmOrderViewAction("Return", event);'/>
HTML;
                                                }
                                                break;
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        </table>
                    </fieldset>
                </form>
            </section>
        </article>

        <?php

        // Render Footer
        $this->getTheme()->renderHTMLBodyFooter();
    }

    protected function renderHTMLHeadScripts()
    {
        parent::renderHTMLHeadScripts();

        echo <<<HTML
        <script>
            function confirmOrderViewAction(action, e) {
                switch(action.toLowerCase()) {
                    case 'return':
                        e.target.form.partial_return_amount.value = prompt("Please enter a PARTIAL RETURN AMOUNT, or leave blank to return the FULL AMOUNT");
                        break;
                }

                var message = "Action: " + action + "\\nAre you sure you want to perform this action?";
                var ret = confirm(message);
                if(!ret) {
                    if(e)
                        e.preventDefault();
                    return false;
                }
                
            }
        </script>
HTML;

    }

}