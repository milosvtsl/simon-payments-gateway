<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/29/2016
 * Time: 1:25 PM
 */
namespace Order\View;

use Dompdf\Exception;
use Integration\Model\Ex\IntegrationException;
use Integration\Model\IntegrationRow;
use Merchant\Model\MerchantFormRow;
use Merchant\Model\MerchantRow;
use Order\Model\OrderRow;
use Payment\Model\PayeeRow;
use Payment\Model\PaymentRow;
use User\Session\SessionManager;
use View\AbstractView;
use View\Error\Mail\ErrorEmail;

class ChargeView extends AbstractView
{
    protected $integration;
    /** @var MerchantFormRow */
    private $form;
    /** @var MerchantRow */
    private $merchantIdentity;

    public function __construct($merchant_id, $formUID=null)    {
        $SessionManager = new SessionManager();
        $SessionUser = $SessionManager->getSessionUser();

        try {
            if($formUID) {
                $OrderForm = MerchantFormRow::fetchByUID($formUID);
            } else {
                if($SessionUser->getMerchantFormID()) {
                    $OrderForm = MerchantFormRow::fetchByID($SessionUser->getMerchantFormID());
                } else {
                    $OrderForm = MerchantFormRow::fetchGlobalForm();
                }
            }
        } catch (\Exception $ex) {
            $SessionManager->setMessage($ex->getMessage());
            $OrderForm = MerchantFormRow::fetchGlobalForm();
        }
        $this->form = $OrderForm;

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

        $Merchant = MerchantRow::fetchByID($merchant_id);

        $SessionUser->setDefaultOrderForm($OrderForm);

        $integrationIDs = $Merchant->getProvisionedIntegrationIDs();
        $selectedIntegrationID = $Merchant->getDefaultIntegrationID() ?: $integrationIDs[0];
        $IntegrationRow = IntegrationRow::fetchByID($selectedIntegrationID);
        $Integration = $IntegrationRow->getIntegration();
        $this->integration = $IntegrationRow;

        $MerchantIdentity = $Integration->getMerchantIdentity($Merchant, $IntegrationRow);
        $this->merchantIdentity = $MerchantIdentity;

        parent::__construct($OrderForm->getTitle() . ' - ' . $Merchant->getShortName());
    }

    /**
     * @param array $params
     */
    public function renderHTMLBody(Array $params) {
        $MerchantIdentity = $this->merchantIdentity;
        $Merchant = $MerchantIdentity->getMerchantRow();

        /** @var MerchantFormRow $MerchantForm */
        $MerchantForm = $this->form;

//        $IntegrationRow = $this->integration;
//        $Integration = $IntegrationRow->getIntegration();


        $Theme = $this->getTheme();
        $Theme->addPathURL('/merchant?uid='.$Merchant->getUID(), $Merchant->getName());
        $Theme->addPathURL('/order/charge.php', 'New Charge');
        $Theme->renderHTMLBodyHeader();

        if(!@$params['iframe']) {
            $Theme->addPathURL('order',               'Transactions');
            $Theme->addPathURL('order/charge.php',    $MerchantForm->getTitle() . ' - ' . $Merchant->getShortName());
            $Theme->printHTMLMenu('order-charge');
        }

//        if($this->hasMessage())
//            echo "<h5>", $this->getMessage(), "</h5>";

        // Render Order Form
        $MerchantForm->renderHTML($MerchantIdentity, $params);

        if(!@$params['iframe'])
            $Theme->renderHTMLBodyFooter();

    }

    public function processFormRequest(Array $post) {
        $Order = null;
        $SessionManager = new SessionManager();
        $SessionUser = $SessionManager->getSessionUser();

        try {
//            if(isset($_SESSION['order/charge.php']['order_id']))
//                $post['order_id'] = $_SESSION['order/charge.php']['order_id'];

            // $_SESSION['order/charge.php'] = $post;
            $MerchantIdentity = $this->merchantIdentity;
            $Merchant = $MerchantIdentity->getMerchantRow();
//            $Integration = $MerchantIdentity->getIntegrationRow();

            if($SessionUser->hasAuthority('ROLE_ADMIN')) {

            } else {
                if(!$SessionUser->hasMerchant($Merchant->getID()))
                    throw new \Exception("User does not have authority");
            }

            $OrderForm = $this->form;

            // Get Payment info
            if(!empty($post['payment_uid'])) {
                $PaymentInfo = PaymentRow::fetchByUID($post['payment_uid']);

            } else {
                $PayeeInfo = PayeeRow::createPayerFromPost($post);
                if(!empty($post['payment_save']))
                    PayeeRow::insertOrUpdate($PayeeInfo);

                $PaymentInfo = PaymentRow::createPaymentFromPost($post, $PayeeInfo);
                if(!empty($post['payment_save']))
                    PaymentRow::insertOrUpdate($PaymentInfo);
            }

            $Order = $MerchantIdentity->createNewOrder($PaymentInfo, $OrderForm, $post);

            $OrderForm->processFormRequest($Order, $post);

            // Perform Fraud Scrubbing
            $Order->performFraudScrubbing($MerchantIdentity, $SessionUser, $post);

            // Submit Transaction
            $Transaction = $MerchantIdentity->submitNewTransaction($Order, $SessionUser, $post);

            // Insert custom order fields

            foreach($OrderForm->getAllCustomFields(false) as $customField) {
                if(!empty($post[$customField])) {
                    $Order->insertCustomField($customField, $post[$customField]);
                }
            }


            // TODO: If AJAX


            // Else POST

            $SessionManager->setMessage(
                "<div class='info'>Success: " . $Transaction->getStatusMessage() . "</div>"
            );
            header('Location: /order/receipt.php?uid=' . $Order->getUID(false));
//            unset($_SESSION['order/charge.php']);
            die();

        } catch (\Exception $ex) {
            $SessionManager->setMessage(
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

        $MerchantForm = $this->form;
        $MerchantIdentity = $this->merchantIdentity;

        // Render Head Content
        $MerchantForm->renderHTMLHeadLinks($MerchantIdentity);


    }

}