<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/29/2016
 * Time: 1:25 PM
 */
namespace Order\View;

use Config\DBConfig;
use Order\Model\OrderRow;
use Transaction\Model\TransactionRow;
use View\AbstractView;

class OrderView extends AbstractView
{
    private $_order;
    private $_action;

    public function __construct($id, $action=null) {
        $this->_action = $action ?: 'view';
        $this->_order = OrderRow::fetchByID($id);
        if(!$this->_order)
            throw new \InvalidArgumentException("Invalid Order ID: " . $id);
        parent::__construct();
    }

    /** @return OrderRow */
    public function getOrder() { return $this->_order; }

    public function renderHTMLBody(Array $params) {
        // Add Breadcrumb links
        $this->getTheme()->addCrumbLink('order?', "Orders");
        $this->getTheme()->addCrumbLink('order?id=' . $this->_order->getID(), $this->_order->getID());
        $this->getTheme()->addCrumbLink($_SERVER['REQUEST_URI'], ucfirst($this->_action));

        // Render Header
        $this->getTheme()->renderHTMLBodyHeader();

        // Render Page
        switch($this->_action) {
            case 'view':

                $DB = DBConfig::getInstance();
                $TransactionQuery = $DB->prepare(TransactionRow::SQL_SELECT . "WHERE t.order_item_id = ?");
                /** @noinspection PhpMethodParametersCountMismatchInspection */
                $TransactionQuery->setFetchMode(\PDO::FETCH_CLASS, 'Transaction\Model\TransactionRow');
                $TransactionQuery->execute(array($this->getOrder()->getID()));

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
        try {
            // Render Page
            switch($this->_action) {
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
                default:
                    throw new \InvalidArgumentException("Invalid Action: " . $this->_action);
            }

        } catch (\Exception $ex) {
            $this->setSessionMessage($ex->getMessage());
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            die();
        }
    }
}