<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/29/2016
 * Time: 1:25 PM
 */
namespace User\View;

use User\Model\UserRow;
use User\Session\SessionManager;
use View\AbstractView;

class AddUserView extends AbstractView
{

    public function renderHTMLBody(Array $params) {
        $SessionUser = SessionManager::get()->getSessionUser();
        if(!$SessionUser->hasAuthority('ROLE_ADMIN', 'ROLE_SUB_ADMIN')) {
            // Only admins may add other users
            $this->setSessionMessage("Unable to add user. Permission required: ROLE_ADMIN or ROLE_SUB_ADMIN");
            header('Location: /user?action=add&message=Unable to manage integration: Admin required');
            die();
        }

        // Render Page
        include('.add.php');
    }

    public function processFormRequest(Array $post) {
        $SessionUser = SessionManager::get()->getSessionUser();
        if(!$SessionUser->hasAuthority('ROLE_ADMIN', 'ROLE_SUB_ADMIN')) {
            // Only admins may add users
            $this->setSessionMessage("Unable to add user. Permission required: ROLE_ADMIN or ROLE_SUB_ADMIN");
                header('Location: /user?action=add&message=Unable to manage integration: Admin required');
                die();
        }

        try {
            $User = UserRow::createNewUser($post, $SessionUser);
            $this->setSessionMessage("User created successfully: " . $User->getUID());
            header('Location: /user?id=' . $User->getID());
            die();

        } catch (\InvalidArgumentException $ex) {
            $this->setSessionMessage("User creation failed: " . $ex->getMessage());
            header('Location: /user/add.php');
            die();
        }

    }
}