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

class ReceiptView extends AbstractView
{
    private $_transaction;

    public function __construct($uid) {
        $this->_transaction = TransactionRow::fetchByUID($uid);
        if(!$this->_transaction)
            throw new \InvalidArgumentException("Invalid Transaction UID: " . $uid);
        parent::__construct();
    }

    /** @return TransactionRow */
    public function getTransaction() { return $this->_transaction; }

    public function renderHTMLBody(Array $params) {

        // Render Header
        $this->getTheme()->renderHTMLBodyHeader();

        include('.receipt.php');

        // Render footer
        $this->getTheme()->renderHTMLBodyFooter();
    }

    public function processFormRequest(Array $post) {
        try {
            throw new \InvalidArgumentException("Invalid Action: " . $this->_action);

        } catch (\Exception $ex) {
            $this->setSessionMessage($ex->getMessage());
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            die();
        }
    }
}