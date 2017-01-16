<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/29/2016
 * Time: 1:25 PM
 */
namespace Order\View;

use Dompdf\Exception;
use Merchant\Model\MerchantFormRow;
use Integration\Model\Ex\IntegrationException;
use Integration\Model\IntegrationRow;
use Merchant\Model\MerchantRow;
use Order\Forms\DefaultOrderForm;
use Order\Model\OrderRow;
use System\Arrays\Locations;
use User\Session\SessionManager;
use View\AbstractView;
use View\Error\Mail\ErrorEmail;

class ChargeView extends AbstractView
{
    /** @var MerchantFormRow */
    private $form;
    /** @var MerchantRow */
    private $merchant;

    public function __construct($merchant_id, $formUID=null)    {
        if($formUID) {
            $OrderForm = MerchantFormRow::fetchByUID($formUID);
        } else {
            $OrderForm = MerchantFormRow::fetchGlobalForm();
        }
        $this->form = $OrderForm;

        $SessionManager = new SessionManager();
        $SessionUser = $SessionManager->getSessionUser();
//        $merchant_id = $OrderForm->getMerchantID();
        if($merchant_id !== null) {
            if(!$SessionUser->hasAuthority('ROLE_ADMIN')) {
                if(!in_array($merchant_id, $SessionUser->getMerchantList()))
                    throw new \Exception("Invalid authorization to use form uid: " . $OrderForm->getUID());
            }
        } else {
            // Assign the first merchant id from the user's list
            $MerchantQuery = $SessionUser->queryUserMerchants();
            $merchant_id = $MerchantQuery->fetch()->getID();
        }

        $this->merchant = MerchantRow::fetchByID($merchant_id);
        parent::__construct($OrderForm->getTitle() . ' - ' . $this->merchant->getShortName());
    }

    public function renderHTMLBody(Array $params) {
        $Merchant = $this->merchant;
        /** @var MerchantFormRow $MerchantForm */
        $MerchantForm = $this->form;

        $Theme = $this->getTheme();
        $Theme->addPathURL('/merchant?uid='.$Merchant->getUID(), $Merchant->getName());
        $Theme->addPathURL('/order/charge.php', 'New Charge');
        $Theme->renderHTMLBodyHeader();

        if(!@$params['iframe']) {
            $Theme->addPathURL('order',               'Transactions');
            $Theme->addPathURL('order/charge.php',    $MerchantForm->getTitle() . ' - ' . $Merchant->getShortName());
            $Theme->printHTMLMenu('order-charge');
        }

        if($this->hasMessage()) 
            echo "<h5>", $this->getMessage(), "</h5>";

        // Render Order Form
        $MerchantForm->renderHTML($Merchant, $params);
        
        if(!@$params['iframe'])
            $Theme->renderHTMLBodyFooter();
    }

    public function processFormRequest(Array $post) {
        $Order = null;
        try {
//            if(isset($_SESSION['order/charge.php']['order_id']))
//                $post['order_id'] = $_SESSION['order/charge.php']['order_id'];

            // $_SESSION['order/charge.php'] = $post;
            $Merchant = $this->merchant; // MerchantRow::fetchByID($post['merchant_id']);
            $Integration = IntegrationRow::fetchByID($Merchant->getDefaultIntegrationID());
            $MerchantIdentity = $Integration->getMerchantIdentity($Merchant);

            $SessionManager = new SessionManager();
            $SessionUser = $SessionManager->getSessionUser();
            if($SessionUser->hasAuthority('ROLE_ADMIN')) {

            } else {
                if(!$SessionUser->hasMerchant($Merchant->getID()))
                    throw new IntegrationException("User does not have authority");
            }
            $OrderForm = $this->form;
            $Order = $MerchantIdentity->createOrResumeOrder($post);
            $Order->setFormID($OrderForm->getID());
            $OrderForm->processFormRequest($Order, $post);

//            $_SESSION['order/charge.php']['order_id'] = $Order->getID();

            $Transaction = $MerchantIdentity->submitNewTransaction($Order, $SessionUser, $post);

            // Insert custom order fields

            foreach($OrderForm->getAllCustomFields(false) as $customField) {
                if(!empty($post[$customField])) {
                    $Order->insertCustomField($customField, $post[$customField]);
                }
            }

            $this->setSessionMessage(
                "<div class='info'>Success: " . $Transaction->getStatusMessage() . "</div>"
            );
            header('Location: /order/receipt.php?uid=' . $Order->getUID(false));
//            unset($_SESSION['order/charge.php']);
            die();

        } catch (\Exception $ex) {
            $this->setSessionMessage(
                "<div class='error'>Error: " . $ex->getMessage() . "</div>"
            );
            header('Location: /order/charge.php');

            // Delete pending orders that didn't complete
            if($Order)
                OrderRow::delete($Order);

            error_log($ex->getMessage());
            
            // Send error email
            $Email = new ErrorEmail($ex);
            $Email->send();


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