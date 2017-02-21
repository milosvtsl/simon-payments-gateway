<?php
/**
 * Created by PhpStorm.
 * Merchant: ari
 * Date: 8/29/2016
 * Time: 1:25 PM
 */
namespace Merchant\View;

use Integration\Model\Ex\IntegrationException;
use Integration\Model\IntegrationRow;
use Merchant\Model\MerchantRow;
use User\Model\UserRow;
use User\Session\SessionManager;
use View\AbstractView;

class MerchantView extends AbstractView
{
    private $_merchant;
    private $_action;

    public function __construct($id, $action=null) {
        $this->_action = $action ?: 'view';
        $this->_merchant = MerchantRow::fetchByID($id);
        if(!$this->_merchant)
            throw new \InvalidArgumentException("Invalid Merchant ID: " . $id);
        parent::__construct();
    }

    /** @return MerchantRow */
    public function getMerchant() { return $this->_merchant; }

    public function renderHTMLBody(Array $params) {
        $SessionManager = new SessionManager();
        $SessionUser = $SessionManager->getSessionUser();
        if(!$SessionUser->hasAuthority('ROLE_ADMIN', 'ROLE_SUB_ADMIN')) {
            // Only admins may edit/view merchants
            $SessionManager->setMessage("Unable to view merchant. Permission required: ROLE_ADMIN");
            $baseHREF = defined("BASE_HREF") ? \BASE_HREF : '';
            header("Location: {$baseHREF}merchant?uid={Merchant->getUID()}&action={$this->_action}&message=Unable to manage integration: Admin required");
            die();
        }

        // Render Page
        switch($this->_action) {
            case 'view':
                include('.view.php');
                break;
            case 'settle':
                include('.settle.php');
                break;
            case 'provision':
                include('.provision.php');
                break;
            case 'edit':
                include('.edit.php');
                break;
            case 'delete':
                include('.delete.php');
                break;
            default:
                throw new \InvalidArgumentException("Invalid Action: " . $this->_action);
        }
    }

    public function processFormRequest(Array $post) {
        $Merchant = $this->getMerchant();
        $SessionManager = new SessionManager();
        $SessionUser = $SessionManager->getSessionUser();
        if(!$SessionUser->hasAuthority('ROLE_ADMIN', 'ROLE_SUB_ADMIN')) {
            // Only admins may edit/view merchants
            $SessionManager->setMessage("Unable to view/edit merchant. Permission required: ROLE_ADMIN");
            $baseHREF = defined("BASE_HREF") ? \BASE_HREF : '';
            header('Location: ' . $baseHREF . 'merchant?uid=' . $Merchant->getUID());
            die();
        }

        if(!$SessionUser->hasAuthority('ROLE_ADMIN')) {
            if(!in_array($Merchant->getID(), $SessionUser->getMerchantList())) {
                // Only admins may edit/view merchants
                $SessionManager->setMessage("Unable to view/edit merchant. This account does not have permission.");
                $baseHREF = defined("BASE_HREF") ? \BASE_HREF : '';
                header('Location: ' . $baseHREF . 'merchant?uid=' . $Merchant->getUID());
                die();
            }
        }

        $baseHREF = defined("BASE_HREF") ? \BASE_HREF : '';

        // Render Page
        switch($this->_action) {
            case 'view':
                $User = UserRow::fetchByUID($_POST['login_user_uid']);
                if(!$SessionUser->hasAuthority('ROLE_ADMIN') && $SessionUser->getID() !== $User->getAdminID()) {
                    $SessionManager->setMessage("Could not log in as user. Permission required: ROLE_ADMIN");
                    header("Location: {$baseHREF}user?uid={$User->getUID()}");
                    die();
                }
                $SessionManager->adminLoginAsUser($User);
                $SessionManager->setMessage("Admin Login as: " . $User->getUsername());
                header("Location: {$baseHREF}user?uid={$User->getUID()}");
                die();

            case 'edit':
                try {
                    $Merchant->updateFields($post)
                        ? $SessionManager->setMessage("<div class='info'>Merchant Updated Successfully: " . $Merchant->getName() . "</div>")
                        : $SessionManager->setMessage("<div class='info'>No changes detected: " . $Merchant->getName() . "</div>");

                } catch (\Exception $ex) {
                    $SessionManager->setMessage($ex->getMessage());
                }
                header("Location: {$baseHREF}merchant?uid={Merchant->getUID()}");
                die();
                break;

            case 'provision':
                $IntegrationRow = IntegrationRow::fetchByID($_GET['integration_id']);
                $MerchantIdentity = $IntegrationRow->getMerchantIdentity($this->getMerchant());
                if($MerchantIdentity->isProvisioned()) {
                    $SessionManager->setMessage("Merchant already provisioned: " . $this->getMerchant()->getName());
                    header("Location: {$baseHREF}merchant?uid={Merchant->getUID()}");
                    die();
                }

                try {
                    $MerchantIdentity->provisionRemote();
                    $SessionManager->setMessage("Merchant provisioned successfully: " . $this->getMerchant()->getName());
                } catch (IntegrationException $ex) {
                    $SessionManager->setMessage("Merchant failed to provision: " . $ex->getMessage());
                }
                header("Location: {$baseHREF}merchant?uid={Merchant->getUID()}");
                die();

                break;
            case 'delete':
                print_r($post);
                die();
                break;
            default:
                throw new \InvalidArgumentException("Invalid Action: " . $this->_action);
        }
    }

    protected function renderHTMLHeadScripts() {
        echo <<<HEAD
        <script src="merchant/view/assets/merchant.js"></script>
HEAD;
        parent::renderHTMLHeadScripts();
    }
}