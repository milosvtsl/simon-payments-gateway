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
        $SessionManager = new SessionManager();
        $SessionUser = $SessionManager->getSessionUser();
        if(!$SessionUser->hasAuthority('ROLE_ADMIN', 'ROLE_SUB_ADMIN')) {
            // Only admins may add other users
            $SessionManager->setMessage("Unable to add user. Permission required: ROLE_ADMIN or ROLE_SUB_ADMIN");

            $baseHREF = defined("BASE_HREF") ? \BASE_HREF : '';
            header("Location: {$baseHREF}user?action=add&message=Unable to manage integration: Admin required");
            die();
        }

        // Render Page
        include('.add.php');
    }

    public function processFormRequest(Array $post) {
        $baseHREF = defined("BASE_HREF") ? \BASE_HREF : '';

        $SessionManager = new SessionManager();
        $SessionUser = $SessionManager->getSessionUser();
        if(!$SessionUser->hasAuthority('ROLE_ADMIN', 'ROLE_SUB_ADMIN')) {
            // Only admins may add users
            $SessionManager->setMessage("Unable to add user. Permission required: ROLE_ADMIN or ROLE_SUB_ADMIN");
                header("Location: {$baseHREF}user?action=add&message=Unable to manage integration: Admin required");
                die();
        }

        try {
            $User = UserRow::createNewUser($post, $SessionUser);
            $SessionManager->setMessage("User created successfully: " . $User->getUID());
            header("Location: {$baseHREF}user?uid={$User->getUID()}");
            die();

        } catch (\InvalidArgumentException $ex) {
            $SessionManager->setMessage("User creation failed: " . $ex->getMessage());
            header("Location: {$baseHREF}user/add.php");
            die();
        }

    }
}