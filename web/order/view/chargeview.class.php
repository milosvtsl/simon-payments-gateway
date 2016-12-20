<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/29/2016
 * Time: 1:25 PM
 */
namespace Order\View;

use System\Config\DBConfig;
use Integration\Model\Ex\IntegrationException;
use Integration\Model\IntegrationRow;
use Merchant\Model\MerchantRow;
use Order\Model\OrderRow;
use Order\Model\TransactionRow;
use User\Session\SessionManager;
use View\AbstractView;

class ChargeView extends AbstractView
{


    public function renderHTMLBody(Array $params) {
        // Render Page
        include('.charge.php');
    }

    public function processFormRequest(Array $post) {
        $Order = null;
        try {
            if(isset($_SESSION['order/charge.php']['order_id']))
                $post['order_id'] = $_SESSION['order/charge.php']['order_id'];

            $_SESSION['order/charge.php'] = $post;
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
            $_SESSION['order/charge.php']['order_id'] = $Order->getID();

            $Transaction = $MerchantIdentity->submitNewTransaction($Order, $SessionUser, $post);

            $this->setSessionMessage(
                "<span class='info'>Success: " . $Transaction->getStatusMessage() . "</span>"
            );
            header('Location: /order/receipt.php?uid=' . $Order->getUID(false));
            unset($_SESSION['order/charge.php']);
            die();

        } catch (\Exception $ex) {
            $this->setSessionMessage(
                "<span class='error'>Error: " . $ex->getMessage() . "</span>"
            );
            header('Location: /order/charge.php');
            if($Order)
                OrderRow::delete($Order);
            // Delete pending orders that didn't complete
            die();
        }
    }

    protected function renderHTMLHeadLinks() {
        parent::renderHTMLHeadLinks();
        echo <<<HEAD
        <script src="order/view/assets/charge.js"></script>
        <script src="https://clevertree.github.io/zip-lookup/zip-lookup.min.js" type="text/javascript" ></script>
        <link href='order/view/assets/charge.css' type='text/css' rel='stylesheet' />
        <link href='order/view/assets/template/full.charge.css' type='text/css' rel='stylesheet' />
        <link href='order/view/assets/template/simple.charge.css' type='text/css' rel='stylesheet' />
HEAD;

    }

}