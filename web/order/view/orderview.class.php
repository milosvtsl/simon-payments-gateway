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
use Transaction\Model\TransactionRow;
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
                include('.view.php');
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
//                    header('Location: order?id=' . $EditOrder->getID() . '#form-order-view');
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

                    $this->setSessionMessage($Subscription->getStatusMessage());
                    header('Location: /transaction/receipt.php?uid=' . $Order->getUID() . '#form-order-view');
                    die();

                case 'void':
                    if(!$SessionUser->hasAuthority('ROLE_VOID_CHARGE', 'ROLE_ADMIN'))
                        throw new Exception("Invalid Authority to Void Charges");

                    $Transaction = $MerchantIdentity->voidTransaction($Order, $post);

                    $this->setSessionMessage($Transaction->getStatusMessage());
                    header('Location: /transaction/receipt.php?uid=' . $Order->getUID() . '#form-order-view');
                    die();

                case 'return':
                    if(!$SessionUser->hasAuthority('ROLE_RETURN_CHARGES', 'ROLE_ADMIN'))
                        throw new Exception("Invalid Authority to Return Charges");

                    $Transaction = $MerchantIdentity->returnTransaction($Order, $post);

                    $this->setSessionMessage($Transaction->getStatusMessage());
                    header('Location: /transaction/receipt.php?uid=' . $Order->getUID() . '#form-order-view');
                    die();

                case 'reverse':
                    if(!$SessionUser->hasAuthority('ROLE_RETURN_CHARGES', 'ROLE_ADMIN'))
                        throw new Exception("Invalid Authority to Return Charges");

                    $Transaction = $MerchantIdentity->reverseTransaction($Order, $post);

                    $this->setSessionMessage($Transaction->getStatusMessage());
                    header('Location: /transaction/receipt.php?uid=' . $Order->getUID() . '#form-order-view');
                    die();

                default:
                    throw new \InvalidArgumentException("Invalid Action: " . $this->_action);
            }

        } catch (\Exception $ex) {
            $this->setSessionMessage($ex->getMessage());
            header('Location: /order?id=' . $this->getOrder()->getID() . '&action='.$this->_action.'&message=' . $ex->getMessage());
            die();
        }
    }
}