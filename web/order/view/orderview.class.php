<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/29/2016
 * Time: 1:25 PM
 */
namespace Order\View;

use Subscription\Model\SubscriptionRow;
use System\Config\DBConfig;
use Dompdf\Exception;
use Integration\Model\IntegrationRow;
use Merchant\Model\MerchantRow;
use Order\Model\OrderRow;
use Order\PDF\ReceiptPDF;
use Order\Model\TransactionRow;
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
                    $message = "Canceled by " . $SessionUser->getUsername();
                    $Subscription = SubscriptionRow::fetchByID($Order->getSubscriptionID());
                    $MerchantIdentity->cancelSubscription($Subscription, $message);

                    $this->setSessionMessage(
                        "<span class='info'>Success: ".$Subscription->getStatusMessage() . "</span>"
                    );
                    header('Location: /order/receipt.php?uid=' . $Order->getUID(false) . '');
                    die();

                case 'void':
                    if(!$SessionUser->hasAuthority('ROLE_VOID_CHARGE', 'ROLE_ADMIN'))
                        throw new Exception("Invalid Authority to Void Charges");

                    $Transaction = $MerchantIdentity->voidTransaction($Order, $post);

                    $this->setSessionMessage(
                        "<span class='info'>Success: ".$Transaction->getStatusMessage() . "</span>"
                    );
                    header('Location: /order/receipt.php?uid=' . $Order->getUID(false) . '');
                    die();

                case 'return':
                    if(!$SessionUser->hasAuthority('ROLE_RETURN_CHARGES', 'ROLE_ADMIN'))
                        throw new Exception("Invalid Authority to Return Charges");

                    $Transaction = $MerchantIdentity->returnTransaction($Order, $post);

                    $this->setSessionMessage(
                        "<span class='info'>Success: ".$Transaction->getStatusMessage() . "</span>"
                    );
                    header('Location: /order/receipt.php?uid=' . $Order->getUID(false) . '');
                    die();

                case 'reverse':
                    if(!$SessionUser->hasAuthority('ROLE_RETURN_CHARGES', 'ROLE_ADMIN'))
                        throw new Exception("Invalid Authority to Return Charges");

                    $Transaction = $MerchantIdentity->reverseTransaction($Order, $post);

                    $this->setSessionMessage(
                        "<span class='info'>Success: ".$Transaction->getStatusMessage() . "</span>"
                    );
                    header('Location: /order/receipt.php?uid=' . $Order->getUID(false) . '');
                    die();

                default:
                    throw new \InvalidArgumentException("Invalid Action: " . $this->_action);
            }

        } catch (\Exception $ex) {
            $this->setSessionMessage(
                "<span class='error'>Error: ".$ex->getMessage() . "</span>"
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
        ?>

        <article class="themed">

            <section class="content">

                <?php if($this->hasMessage()) echo "<h5>", $this->getMessage(), "</h5>"; ?>

                <form name="form-order-view" id="form-order-view" class="themed" method="POST">
                    <fieldset class="inline-block-on-layout-full" style="min-height: 9em;">
                        <div class="legend">Receipt</div>
                        <table class="table-transaction-info themed cell-borders small" style="width: 100%;">
                            <tbody>
                            <?php $odd = true; ?>

                            <?php if ($Order->getConvenienceFee()) { ?>
                                <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                    <td class="name" style="width: 30%;">Subtotal</td>
                                    <td class="value">$<?php echo $Order->getAmount(); ?></td>
                                </tr>
                                <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                    <td class="name" style="width: 30%;">Fee</td>
                                    <td class="value">$<?php echo $Order->getConvenienceFee(); ?></td>
                                </tr>
                            <?php } ?>

                                <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                    <td class="name" style="width: 30%;">Total</td>
                                    <td class="value">$<?php echo number_format($Order->getAmount()+$Order->getConvenienceFee(), 2); ?></td>
                                </tr>

                                <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                    <td class="name" style="width: 30%;">Date</td>
                                    <td class="value"><?php echo date("F jS Y", strtotime($Order->getDate()) + $offset); ?></td>
                                </tr>
                                <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                    <td class="name" style="width: 30%;">Time</td>
                                    <td class="value"><?php echo date("g:i:s A", strtotime($Order->getDate()) + $offset); ?></td>
                                </tr>
                                <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                    <td class="name" style="width: 30%;">Time Zone</td>
                                    <td class="value"><?php echo str_replace('_', '', $SessionUser->getTimeZone()); ?></td>
                                </tr>

                                <?php if($Order->getInvoiceNumber()) { ?>
                                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                        <td class="name" style="width: 30%;">Invoice</td>
                                        <td class="value"><?php echo $Order->getInvoiceNumber() ?: 'N/A'; ?></td>
                                    </tr>
                                <?php } ?>
                                <?php if($Order->getCustomerID()) { ?>
                                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                        <td class="name" style="width: 30%;">Customer</td>
                                        <td class="value"><?php echo $Order->getCustomerID() ?: 'N/A' ?></td>
                                    </tr>
                                <?php } ?>
                                <?php if ($Order->getPayeeEmail()) { ?>
                                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                        <td class="name" style="width: 30%;">Email</td>
                                        <td class="value"><a href="mailto:<?php echo $Order->getPayeeEmail() ?>"><?php echo $Order->getPayeeEmail() ?></a></td>
                                    </tr>
                                <?php }  ?>
                            </tbody>
                        </table>
                    </fieldset>

                    <?php if ($Order->getCardNumber()) { ?>

                        <fieldset class="inline-block-on-layout-full" style="min-height: 9em;">
                            <div class="legend">Card Holder: <?php echo $Order->getCardHolderFullName(); ?></div>
                            <table class="table-transaction-info themed cell-borders small" style="width: 100%;">
                                <tbody>
                                    <?php if($Order->getUsername()) { ?>
                                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                            <td class="name" style="width: 30%;">User ID</td>
                                            <td class="value"><?php echo $Order->getUsername(); ?></td>
                                        </tr>
                                    <?php }  ?>
                                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                        <td class="name" style="width: 30%;">Credit Card</td>
                                        <td class="value"><?php echo $Order->getCardNumber() ? substr($Order->getCardNumber(), -16) : 'N/A'; ?></td>
                                    </tr>
                                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                        <td class="name" style="width: 30%;">Card Type</td>
                                        <td class="value"><?php echo $Order->getCardType(); ?></td>
                                    </tr>
                                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                        <td class="name" style="width: 30%;">Status</td>
                                        <td class="value"><?php echo $Order->getStatus(); ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </fieldset>

                    <?php } else  { ?>

                        <fieldset class="inline-block-on-layout-full" style="min-height: 9em;">
                            <div class="legend">e-Check : <?php echo $Order->getCheckAccountName(); ?></div>
                            <table class="table-transaction-card-info themed cell-borders small"  style="width: 100%">
                                <tbody>
                                    <?php if($Order->getUsername()) { ?>
                                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                        <td class="name" style="width: 30%;">User&nbsp;ID</td>
                                        <td class="value"><?php echo $Order->getUsername(); ?></td>
                                    </tr>
                                    <?php }  ?>
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
                                        <td class="name" style="width: 30%;">Usage</td>
                                        <td class="value"><?php echo $Order->getCheckAccountType(); ?></td>
                                    </tr>
                                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                        <td class="name" style="width: 30%;">Check Num</td>
                                        <td class="value"><?php echo $Order->getCheckNumber(); ?></td>
                                    </tr>
                                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                        <td class="name" style="width: 30%;">Status</td>
                                        <td class="value"><?php echo $Order->getStatus(); ?></td>
                                    </tr>
                                    <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                        <td class="name" style="width: 30%;">Order&nbsp;ID</td>
                                        <td class="value"><?php echo $Order->getID(); ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </fieldset>
                    <?php } ?>

                    <fieldset class="inline-block-on-layout-full" style="min-height: 9em;">
                        <div class="legend"><?php echo $Merchant->getShortName(); ?></div>
                        <table class="table-transaction-info themed cell-borders small" style="width: 100%;">
                            <tbody>
                            <?php $odd = true; ?>
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
                            </tbody>
                        </table>
                    </fieldset>


                    <fieldset class="show-on-print" style="clear: both;">
                        <br/>
                        <br/>
                        <br/>
                        <hr style="height: 2px;">
                        Customer Signature
                    </fieldset>

                    <?php if ($Order->getSubscriptionCount() > 0) { ?>
                    <fieldset class="hide-on-print">
                        <div class="legend">Subscription Status</div>
                        <table class="table-results themed small cell-borders">
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

                    <fieldset class="hide-on-print inline-block-on-layout-full">
                        <div class="legend">Transaction History</div>
                        <table class="table-results themed small cell-borders" style="width: 100%;">
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
                                                    echo "<input name='action' type='submit' value='Void'{$disabled}/>";
                                                }
                                                break;

                                            case 'Settled':
                                                if($Order->getStatus() === 'Settled') {
                                                    $disabled = $SessionUser->hasAuthority('ROLE_RETURN_CHARGE', 'ROLE_ADMIN') ? '' : " disabled='disabled'";
                                                    echo "<input name='action' type='submit' value='Return'{$disabled}/>";
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
}