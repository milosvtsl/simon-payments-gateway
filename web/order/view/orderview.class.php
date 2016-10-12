<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/29/2016
 * Time: 1:25 PM
 */
namespace Order\View;

use Config\DBConfig;
use Integration\Model\IntegrationRow;
use Merchant\Model\MerchantRow;
use Order\Model\OrderRow;
use Transaction\Model\TransactionRow;
use View\AbstractView;

class OrderView extends AbstractView
{
    const VIEW_PATH = 'order';
    const VIEW_NAME = 'Orders';

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
        // Render Header
        $this->getTheme()->renderHTMLBodyHeader();

        // Render Page
        switch($this->_action) {
            case 'receipt':
            case 'email':
            case 'print':
            case 'download':
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

        // Render footer
        $this->getTheme()->renderHTMLBodyFooter();
    }

    public function processFormRequest(Array $post) {
        $action = $this->_action;
        if(!empty($post['action']))
            $action = strtolower($post['action']);

        $Order = $this->getOrder();
        $Integration = IntegrationRow::fetchByID($Order->getIntegrationID());
        $Merchant = MerchantRow::fetchByID($Order->getMerchantID());
        $MerchantIdentity = $Integration->getMerchantIdentity($Merchant);

        try {
            // Render Page
            switch($action) {
                case 'edit':
                    $EditOrder = $this->getOrder();
                    $EditOrder->updateFields($post)
                        ? $this->setSessionMessage("Order Updated Successfully: " . $EditOrder->getUID())
                        : $this->setSessionMessage("No changes detected: " . $EditOrder->getUID());
                    header('Location: order?id=' . $EditOrder->getID());
                    die();

                    break;
                case 'delete':
                    print_r($post);
                    die();
                    break;
                case 'change':
                    print_r($post);
                    die();
                    break;

                case 'void':
                    $Transaction = $MerchantIdentity->voidTransaction($Order, $post);

                    $this->setSessionMessage($Transaction->getStatusMessage());
                    header('Location: /transaction/receipt.php?uid=' . $Order->getUID());
                    die();

                case 'return':
                    $Transaction = $MerchantIdentity->returnTransaction($Order, $post);

                    $this->setSessionMessage($Transaction->getStatusMessage());
                    header('Location: /transaction/receipt.php?uid=' . $Order->getUID());
                    die();

                case 'reverse':
                    $Transaction = $MerchantIdentity->reverseTransaction($Order, $post);

                    $this->setSessionMessage($Transaction->getStatusMessage());
                    header('Location: /transaction/receipt.php?uid=' . $Order->getUID());
                    die();

                default:
                    throw new \InvalidArgumentException("Invalid Action: " . $this->_action);
            }

        } catch (\Exception $ex) {
            $this->setSessionMessage($ex->getMessage());
            header('Location: ' . @$_SERVER['HTTP_REFERER']?:'/');
            die();
        }
    }
}