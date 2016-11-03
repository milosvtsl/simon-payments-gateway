<?php
/**
 * Created by PhpStorm.
 * Merchant: ari
 * Date: 8/29/2016
 * Time: 1:25 PM
 */
namespace Merchant\View;

use System\Config\DBConfig;
use Integration\Model\Ex\IntegrationException;
use Integration\Model\IntegrationRow;
use Merchant\Model\MerchantRow;
use User\Session\SessionManager;
use View\AbstractView;

class MerchantView extends AbstractView
{
    const VIEW_PATH = 'integration';
    const VIEW_NAME = 'Integration';

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
        $SessionUser = SessionManager::get()->getSessionUser();
        if(!$SessionUser->hasAuthority('ROLE_ADMIN', 'ROLE_SUB_ADMIN')) {
            // Only admins may edit/view merchants
            $this->setSessionMessage("Unable to view merchant. Permission required: ROLE_ADMIN");
            header('Location: /merchant?id=' . $this->getMerchant()->getID() . '&action='.$this->_action.'&message=Unable to manage integration: Admin required');
            die();
        }

        // Render Header
        $this->getTheme()->renderHTMLBodyHeader();

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

        // Render footer
        $this->getTheme()->renderHTMLBodyFooter();
    }

    public function processFormRequest(Array $post) {
        $Merchant = $this->getMerchant();

        $SessionUser = SessionManager::get()->getSessionUser();
        if(!$SessionUser->hasAuthority('ROLE_ADMIN', 'ROLE_SUB_ADMIN')) {
            // Only admins may edit/view merchants
            $this->setSessionMessage("Unable to view/edit merchant. Permission required: ROLE_ADMIN");
            header('Location: /merchant?id=' . $Merchant->getID());
            die();
        }

        if(!$SessionUser->hasAuthority('ROLE_ADMIN')) {
            if(!in_array($Merchant->getID(), $SessionUser->getMerchantList())) {
                // Only admins may edit/view merchants
                $this->setSessionMessage("Unable to view/edit merchant. This account does not have permission.");
                header('Location: /merchant?id=' . $Merchant->getID());
                die();
            }
        }

            // Render Page
        switch($this->_action) {
            case 'edit':
                try {
                    $Merchant->updateFields($post)
                        ? $this->setSessionMessage("Merchant Updated Successfully: " . $Merchant->getName())
                        : $this->setSessionMessage("No changes detected: " . $Merchant->getName());

                } catch (\Exception $ex) {
                    $this->setSessionMessage($ex->getMessage());
                }
                header('Location: /merchant?id=' . $Merchant->getID());
                die();
                break;

            case 'provision':
                $IntegrationRow = IntegrationRow::fetchByID($_GET['integration_id']);
                $MerchantIdentity = $IntegrationRow->getMerchantIdentity($this->getMerchant());
                if($MerchantIdentity->isProvisioned()) {
                    $this->setSessionMessage("Merchant already provisioned: " . $this->getMerchant()->getName());
                    header('Location: /merchant?id=' . $this->getMerchant()->getID());
                    die();
                }

                try {
                    $MerchantIdentity->provisionRemote();
                    $this->setSessionMessage("Merchant provisioned successfully: " . $this->getMerchant()->getName());
                } catch (IntegrationException $ex) {
                    $this->setSessionMessage("Merchant failed to provision: " . $ex->getMessage());
                }
                header('Location: /merchant?id=' . $this->getMerchant()->getID());
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