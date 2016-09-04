<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/29/2016
 * Time: 1:25 PM
 */
namespace Batch\View;

use Config\DBConfig;
use Batch\Model\BatchRow;
use Order\Model\OrderRow;
use Transaction\Model\TransactionRow;
use View\AbstractView;

class BatchView extends AbstractView
{
    private $_batch;
    private $_action;

    public function __construct($id, $action=null) {
        $this->_action = $action ?: 'view';
        $this->_batch = BatchRow::fetchByID($id);
        if(!$this->_batch)
            throw new \InvalidArgumentException("Invalid Batch ID: " . $id);
        parent::__construct();
    }

    /** @return BatchRow */
    public function getBatch() { return $this->_batch; }

    public function renderHTMLBody(Array $params) {
        // Add Breadcrumb links
        $this->getTheme()->addCrumbLink('batch?', "Batchs");
        $this->getTheme()->addCrumbLink('batch?id=' . $this->_batch->getID(), $this->_batch->getID());
        $this->getTheme()->addCrumbLink($_SERVER['REQUEST_URI'], ucfirst($this->_action));

        // Render Header
        $this->getTheme()->renderHTMLBodyHeader();

        // Render Page
        switch($this->_action) {
            case 'view':

                $DB = DBConfig::getInstance();
                $OrderQuery = $DB->prepare(OrderRow::SQL_SELECT
                    . "\nLEFT JOIN batch_orderitems boi on oi.id = boi.id_orderitem"
                    . "\nWHERE boi.id_batch = ?");
                /** @noinspection PhpMethodParametersCountMismatchInspection */
                $OrderQuery->setFetchMode(\PDO::FETCH_CLASS, 'Transaction\Model\TransactionRow');
                $OrderQuery->execute(array($this->getBatch()->getID()));

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
                    $EditBatch = $this->getBatch();
                    $EditBatch->updateFields($post)
                        ? $this->setSessionMessage("Batch Updated Successfully: " . $EditBatch->getUID())
                        : $this->setSessionMessage("No changes detected: " . $EditBatch->getUID());
                    header('Location: batch?id=' . $EditBatch->getID());
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