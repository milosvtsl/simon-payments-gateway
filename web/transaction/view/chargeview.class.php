<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/29/2016
 * Time: 1:25 PM
 */
namespace Transaction\View;

use Config\DBConfig;
use Merchant\Model\MerchantRow;
use Transaction\Model\TransactionRow;
use View\AbstractView;

class ChargeView extends AbstractView
{
    private $_transaction;
    private $_action;


    public function renderHTMLBody(Array $params) {
        // Add Breadcrumb links
        $this->getTheme()->addCrumbLink('home', "Home");
        $this->getTheme()->addCrumbLink('transaction?', "Transactions");
        $this->getTheme()->addCrumbLink($_SERVER['REQUEST_URI'], 'New Charge');

        // Render Header
        $this->getTheme()->renderHTMLBodyHeader();

        // Render Page
        include('.charge.php');

        // Render footer
        $this->getTheme()->renderHTMLBodyFooter();
    }

    public function processFormRequest(Array $post) {
        try {
            print_r($post);die();

        } catch (\Exception $ex) {
            $this->setSessionMessage($ex->getMessage());
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            die();
        }
    }
}