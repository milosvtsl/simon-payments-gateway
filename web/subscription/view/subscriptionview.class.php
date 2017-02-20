<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/29/2016
 * Time: 1:25 PM
 */
namespace Subscription\View;

use Integration\Model\IntegrationRow;
use Merchant\Model\MerchantRow;
use Subscription\Model\SubscriptionRow;
use User\Session\SessionManager;
use View\AbstractView;

class SubscriptionView extends AbstractView
{
    const VIEW_PATH = 'subscription';
    const VIEW_NAME = 'Transactions';

    private $_subscription;
    private $_action;

    public function __construct($id, $action=null) {
        $this->_action = strtolower($action) ?: 'view';
        $this->_subscription = SubscriptionRow::fetchByID($id);
        if(!$this->_subscription)
            throw new \InvalidArgumentException("Invalid Subscription ID: " . $id);
        parent::__construct();
    }

    /** @return SubscriptionRow */
    public function getSubscription() { return $this->_subscription; }

    public function renderHTMLBody(Array $params) {
        // Render Page
        switch($this->_action) {
            case 'download':
                include('.download.php');
                break;
            case 'receipt':
            case 'email':
            case 'print':
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
    }

    public function processFormRequest(Array $post) {
        $action = $this->_action;
        if(!empty($post['action']))
            $action = strtolower($post['action']);

        $Subscription = $this->getSubscription();
        $Integration = IntegrationRow::fetchByID($Subscription->getIntegrationID());
        $Merchant = MerchantRow::fetchByID($Subscription->getMerchantID());
        $MerchantIdentity = $Integration->getMerchantIdentity($Merchant);

        $SessionManager = new SessionManager();
        $SessionUser = $SessionManager->getSessionUser();

        try {
            // Render Page
            switch($action) {
//                case 'edit':
//                    $EditSubscription = $this->getSubscription();
//                    $EditSubscription->updateFields($post)
//                        ? $this->setSessionMessage("Subscription Updated Successfully: " . $EditSubscription->getUID())
//                        : $this->setSessionMessage("No changes detected: " . $EditSubscription->getUID());
//                    header('Location: subscription?id=' . $EditSubscription->getID() . '');
//                    die();

                case 'delete':
                    print_r($post);
                    die();

                case 'change':
                    print_r($post);
                    die();

                case 'cancel':
                    $message = "Canceled by " . $SessionUser->getUsername();
                    $Subscription = SubscriptionRow::fetchByID($Subscription->getSubscriptionID());
                    $MerchantIdentity->cancelSubscription($Subscription, $message);

                    $this->setSessionMessage(
                        "<span class='info'>Success: ".$Subscription->getStatusMessage() . "</span>"
                    );
                    header('Location: /subscription/receipt.php?uid=' . $Subscription->getUID() . '');
                    die();

                case 'void':
                    if(!$SessionUser->hasAuthority('ROLE_VOID_CHARGE', 'ROLE_ADMIN'))
                        throw new \Exception("Invalid Authority to Void Charges");

                    $Transaction = $MerchantIdentity->voidTransaction($Subscription, $post);

                    $this->setSessionMessage(
                        "<span class='info'>Success: ".$Transaction->getStatusMessage() . "</span>"
                    );
                    header('Location: /subscription/receipt.php?uid=' . $Subscription->getUID() . '');
                    die();

                case 'return':
                    if(!$SessionUser->hasAuthority('ROLE_RETURN_CHARGES', 'ROLE_ADMIN'))
                        throw new \Exception("Invalid Authority to Return Charges");

                    $Transaction = $MerchantIdentity->returnTransaction($Subscription, $post);

                    $this->setSessionMessage(
                        "<span class='info'>Success: ".$Transaction->getStatusMessage() . "</span>"
                    );
                    header('Location: /subscription/receipt.php?uid=' . $Subscription->getUID() . '');
                    die();

                case 'reverse':
                    if(!$SessionUser->hasAuthority('ROLE_RETURN_CHARGES', 'ROLE_ADMIN'))
                        throw new \Exception("Invalid Authority to Return Charges");

                    $Transaction = $MerchantIdentity->reverseTransaction($Subscription, $post);

                    $this->setSessionMessage(
                        "<span class='info'>Success: ".$Transaction->getStatusMessage() . "</span>"
                    );
                    header('Location: /subscription/receipt.php?uid=' . $Subscription->getUID() . '');
                    die();

                default:
                    throw new \InvalidArgumentException("Invalid Action: " . $this->_action);
            }

        } catch (\Exception $ex) {
            $this->setSessionMessage(
                "<span class='error'>Error: ".$ex->getMessage() . "</span>"
            );
            header('Location: /subscription/receipt.php?uid=' . $Subscription->getUID() . '&action='.$this->_action.'&message=' . $ex->getMessage()  . '');
            die();
        }
    }
}