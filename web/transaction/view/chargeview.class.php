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


    public function renderHTMLBody(Array $params) {
        // Render Header
        $this->getTheme()->renderHTMLBodyHeader();

        // Render Page
        include('.charge.php');

        // Render footer
        $this->getTheme()->renderHTMLBodyFooter();
    }

    public function processFormRequest(Array $post) {
        try {
            echo "<pre>";
            print_r($post);die();

        } catch (\Exception $ex) {
            $this->setSessionMessage($ex->getMessage());
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            die();
        }
    }

    protected function renderHTMLHeadLinks() {
        parent::renderHTMLHeadLinks();
        echo <<<HEAD
        <script src="transaction/view/assets/charge.js"></script>
        <link href='transaction/view/assets/charge.css' type='text/css' rel='stylesheet' />
        <link href='transaction/view/assets/template/full.charge.css' type='text/css' rel='stylesheet' />
        <link href='transaction/view/assets/template/simple.charge.css' type='text/css' rel='stylesheet' />
HEAD;

    }

}