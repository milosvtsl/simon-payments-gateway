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
        $Merchant = $this->_merchant;
        if(!$SessionUser->hasAuthority('ADMIN', 'SUB_ADMIN')) {

            if($SessionUser->getMerchantID() !== $Merchant->getID()) {
                // Only admins may edit/view merchants, unless it's their own account
                $SessionManager->setMessage("Unable to view merchant. Permission required: ADMIN/SUB_ADMIN");
                $baseHREF = defined("BASE_HREF") ? \BASE_HREF : '';

                $Merchant = $this->_merchant;
                header("Location: {$baseHREF}index.php");
                die();
            }
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
//        if(!$SessionUser->hasAuthority('ADMIN', 'SUB_ADMIN')) {
//            // Only admins may edit/view merchants
//            $SessionManager->setMessage("Unable to view/edit merchant. Permission required: ADMIN");
//            $baseHREF = defined("BASE_HREF") ? \BASE_HREF : '';
//            header('Location: ' . $baseHREF . 'merchant?uid=' . $Merchant->getUID());
//            die();
//        }

        if(!$SessionUser->hasAuthority('ADMIN')) {
            if($SessionUser->getMerchantID() !== $Merchant->getID()) {
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
                if(!$SessionUser->hasAuthority('ADMIN') && $SessionUser->getID() !== $User->getAdminID()) {
                    $SessionManager->setMessage("Could not log in as user. Permission required: ADMIN");
                    header("Location: {$baseHREF}user?uid={$User->getUID()}");
                    die();
                }
                $SessionManager->adminLoginAsUser($User);
                $SessionManager->setMessage("Admin Login as: " . $User->getUsername());
                header("Location: {$baseHREF}user?uid={$User->getUID()}");
                die();

            case 'edit':
                try {
                    $updated = false;
                    if(!empty($_FILES['logo_path'])) {
                        $Merchant->updateLogo($_FILES['logo_path']);
                        $updated = true;
                    }

                    $Merchant->updateFields($post) || $updated
                        ? $SessionManager->setMessage("<div class='info'>Merchant Updated Successfully: " . $Merchant->getName() . "</div>")
                        : $SessionManager->setMessage("<div class='info'>No changes detected: " . $Merchant->getName() . "</div>");

                } catch (\Exception $ex) {
                    $SessionManager->setMessage($ex->getMessage());
                }

                header("Location: {$baseHREF}merchant?uid={$Merchant->getUID()}");
                die();
                break;

            case 'provision':
                $IntegrationRow = IntegrationRow::fetchByID($_GET['integration_id']);
                $MerchantIdentity = $IntegrationRow->getMerchantIdentity($this->getMerchant());
                if($MerchantIdentity->isProvisioned()) {
                    $SessionManager->setMessage("Merchant already provisioned: " . $this->getMerchant()->getName());
                    header("Location: {$baseHREF}merchant?uid={$Merchant->getUID()}");
                    die();
                }

                try {
                    $MerchantIdentity->provisionRemote();
                    $SessionManager->setMessage("Merchant provisioned successfully: " . $this->getMerchant()->getName());
                } catch (IntegrationException $ex) {
                    $SessionManager->setMessage("Merchant failed to provision: " . $ex->getMessage());
                }
                header("Location: {$baseHREF}merchant?uid={$Merchant->getUID()}");
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