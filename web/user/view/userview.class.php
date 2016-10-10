<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/29/2016
 * Time: 1:25 PM
 */
namespace User\View;

use Config\DBConfig;
use User\Model\UserRow;
use User\Session\SessionManager;
use View\AbstractView;

class UserView extends AbstractView
{
    private $user;
    public function __construct($user_id) {
        $this->user = UserRow::fetchByID($user_id);
        if(!$this->user)
            throw new \InvalidArgumentException("Invalid User ID: " . $user_id);
        parent::__construct();
    }

    /** @return UserRow */
    public function getUser() { return $this->user; }

    public function renderHTMLBody(Array $params) {
        $action = @$params['action'] ?: 'view';

        $User = $this->getUser();

        // Render Header
        $this->getTheme()->renderHTMLBodyHeader();

        $SessionUser = SessionManager::get()->getSessionUser();
        if(!$SessionUser->hasAuthority('ROLE_ADMIN')) {
            // Only admins may edit other users
            if($SessionUser->getID() !== $User->getID()) {
                $this->setSessionMessage("Unable to view user. Permission required: ROLE_ADMIN");
                header('Location: ' . @$_SERVER['HTTP_REFERER']?:'/');
                die();
            }
        }

        // Render Page
        switch($action) {
            default:
            case 'view':
                include('.view.php');
                break;
            case 'edit':
                include('.edit.php');
                break;
            case 'delete':
                include('.delete.php');
                break;
        }

        // Render footer
        $this->getTheme()->renderHTMLBodyFooter();
    }

    public function processFormRequest(Array $post) {
        $User = UserRow::fetchByID($post['id']);
        if(!$User)
            throw new \InvalidArgumentException("Invalid User ID: " . $post['id']);

        $SessionUser = SessionManager::get()->getSessionUser();
        if(!$SessionUser->hasAuthority('ROLE_ADMIN')) {
            // Only admins may edit other users
            if($SessionUser->getID() !== $User->getID()) {
                $this->setSessionMessage("Could not make changes to other user. Permission required: ROLE_ADMIN");
                header('Location: ' . @$_SERVER['HTTP_REFERER']?:'/');
                die();
            }
        }

        // Process POST
        switch(@$post['action']) {
            case 'edit':
                try {
                    // Update User fields
                    $updates = $User->updateFields($post['fname'], $post['lname'], $post['username'], $post['email']);

                    // Change Password
                    if(!empty($post['password']))
                        $updates += $User->changePassword($post['password'], $post['password_confirm']);

                    // Set message and redirect
                    $updates > 0
                        ? $this->setSessionMessage($updates . " user fields updated successfully: " . $User->getUID())
                        : $this->setSessionMessage("No changes detected: " . $User->getUID());
                    header('Location: user?id=' . $User->getID());
                    die();

                } catch (\Exception $ex) {
                    $this->setSessionMessage($ex->getMessage());
                    header('Location: ' . @$_SERVER['HTTP_REFERER']?:'/');
                    die();
                }
                break;

            case 'delete':
                if(!$SessionUser->hasAuthority('ROLE_ADMIN')) {
                    $this->setSessionMessage("Could not delete user. Permission required: ROLE_ADMIN");
                    header('Location: ' . @$_SERVER['HTTP_REFERER']?:'/');
                    die();
                }
                print_r($post);
                die();
                break;

            default:
                throw new \InvalidArgumentException("Invalid Action");
        }

    }
}