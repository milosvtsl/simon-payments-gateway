<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/29/2016
 * Time: 1:25 PM
 */
namespace Merchant\View;

use Merchant\Model\MerchantRow;
use User\Session\SessionManager;
use View\AbstractView;

class AddMerchantView extends AbstractView
{

    public function renderHTMLBody(Array $params) {

        // Render Header
        $this->getTheme()->renderHTMLBodyHeader();

        $SessionUser = SessionManager::get()->getSessionUser();
        if(!$SessionUser->hasAuthority('ROLE_ADMIN', 'ROLE_SUB_ADMIN')) {
            // Only admins may add other merchants
            $this->setSessionMessage("Unable to add merchant. Permission required: ROLE_ADMIN or ROLE_SUB_ADMIN");
            header('Location: /merchant?action=add&message=Unable to manage integration: Admin required');
            die();
        }

        // Render Page
        include('.add.php');

        // Render footer
        $this->getTheme()->renderHTMLBodyFooter();
    }

    public function processFormRequest(Array $post) {
        $SessionUser = SessionManager::get()->getSessionUser();
        if(!$SessionUser->hasAuthority('ROLE_ADMIN', 'ROLE_SUB_ADMIN')) {
            // Only admins may add merchants
            $this->setSessionMessage("Unable to add merchant. Permission required: ROLE_ADMIN or ROLE_SUB_ADMIN");
                header('Location: /merchant?action=add&message=Unable to manage integration: Admin required');
                die();
        }

        $Merchant = MerchantRow::createNewMerchant($post);
        $SessionUser->addMerchantID($Merchant->getID());

        $this->setSessionMessage("Merchant created successfully: " . $Merchant->getUID());
        header('Location: /merchant?id=' . $Merchant->getID() . '&action=edit');
        die();

    }
}