<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/29/2016
 * Time: 1:25 PM
 */
namespace Transaction\View;

use System\Config\DBConfig;
use Integration\Model\Ex\IntegrationException;
use Integration\Model\IntegrationRow;
use Merchant\Model\MerchantRow;
use Order\Model\OrderRow;
use Transaction\Model\TransactionRow;
use User\Session\SessionManager;
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
        $Order = null;
        try {
            if(isset($_SESSION['transaction/charge.php']['order_id']))
                $post['order_id'] = $_SESSION['transaction/charge.php']['order_id'];

            $_SESSION['transaction/charge.php'] = $post;
            $Integration = IntegrationRow::fetchByID($post['integration_id']);
            $Merchant = MerchantRow::fetchByID($post['merchant_id']);
            $MerchantIdentity = $Integration->getMerchantIdentity($Merchant);

            $SessionManager = new SessionManager();
            $SessionUser = $SessionManager->getSessionUser();
            if($SessionUser->hasAuthority('ROLE_ADMIN')) {

            } else {
                if(!$SessionUser->hasMerchant($Merchant->getID()))
                    throw new IntegrationException("User does not have authority");
            }
            $Order = $MerchantIdentity->createOrResumeOrder($post);
            $_SESSION['transaction/charge.php']['order_id'] = $Order->getID();

            $Transaction = $MerchantIdentity->submitNewTransaction($Order, $post);

            $this->setSessionMessage(
                "<span class='info'>Success: " . $Transaction->getStatusMessage() . "</span>"
            );
            header('Location: /transaction/receipt.php?uid=' . $Order->getUID());
            unset($_SESSION['transaction/charge.php']);
            die();

        } catch (\Exception $ex) {
            $this->setSessionMessage(
                "<span class='error'>Error: " . $ex->getMessage() . "</span>"
            );
            header('Location: /transaction/charge.php');
            if($Order)
                OrderRow::delete($Order);
            // Delete pending orders that didn't complete
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