<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/29/2016
 * Time: 1:25 PM
 */
namespace Transaction\View;

use Transaction\Model\TransactionRow;
use View\AbstractView;

class TransactionView extends AbstractView
{
    const VIEW_PATH = 'transaction';
    const VIEW_NAME = 'Transactions';

    private $_transaction;
    private $_action;

    public function __construct($id, $action=null) {
        $this->_action = $action ?: 'view';
        $this->_transaction = TransactionRow::fetchByID($id);
        if(!$this->_transaction)
            throw new \InvalidArgumentException("Invalid Transaction ID: " . $id);
        parent::__construct();
    }

    /** @return TransactionRow */
    public function getTransaction() { return $this->_transaction; }

    public function renderHTMLBody(Array $params) {
        // Add Breadcrumb links
        $this->getTheme()->addCrumbLink(static::VIEW_PATH, static::VIEW_NAME);
        $this->getTheme()->addCrumbLink(static::VIEW_PATH . '?id=' . $this->getTransaction()->getID(), $this->getTransaction()->getID());
        $this->getTheme()->addCrumbLink($_SERVER['REQUEST_URI'], ucfirst($this->_action));

        // Render Header
        $this->getTheme()->renderHTMLBodyHeader();

        // Render Page
        switch($this->_action) {
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
        try {
            // Render Page
            switch($this->_action) {
                case 'edit':
                    $EditTransaction = $this->getTransaction();
                    $EditTransaction->updateFields($post)
                        ? $this->setSessionMessage("Transaction Updated Successfully: " . $EditTransaction->getUID())
                        : $this->setSessionMessage("No changes detected: " . $EditTransaction->getUID());
                    header('Location: transaction?id=' . $EditTransaction->getID());
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