<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/29/2016
 * Time: 1:25 PM
 */
namespace User\View;

use User\Mail\UserWelcomeEmail;
use User\Model\UserRow;
use User\Session\SessionManager;
use View\AbstractView;

class UserView extends AbstractView
{
    private $user;
    public function __construct($user_uid) {
        $this->user = UserRow::fetchByUID($user_uid);
        if(!$this->user)
            throw new \InvalidArgumentException("Invalid User ID: " . $user_uid);
        parent::__construct();
    }

    /** @return UserRow */
    public function getUser() { return $this->user; }

    public function renderHTMLBody(Array $params) {
        $action = @$params['action'] ?: 'view';
        
        $this->handleAuthority();

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
    }

    public function processFormRequest(Array $post) {
        $baseHREF = defined("BASE_HREF") ? \BASE_HREF : '';

        $User = $this->getUser();

        $this->handleAuthority();

        $SessionManager = new SessionManager();
        $SessionUser = $SessionManager->getSessionUser();

        // Process POST
        switch(strtolower(@$post['action'])) {
            case 'edit':
                try {
                    // Update User fields
                    $updates = $User->updateFields($post, $SessionUser);

                    // Set message and redirect
                    $updates > 0
                        ? $SessionManager->setMessage("<div class='info'>User updated successfully: " . $User->getUsername() . '</div>')
                        : $SessionManager->setMessage("<div class='info'>No changes detected: " . $User->getUserName() . '</div>');
                    header("Location: {$baseHREF}user/?uid={$User->getUID()}");
                    die();

                } catch (\Exception $ex) {
                    $SessionManager->setMessage("<div class='error'>" . $ex->getMessage() . "</div>");
//                    $this->renderHTML(array(
//                        'action' => 'edit'
//                    ));
                    header("Location: {$baseHREF}user/?uid={$User->getUID()}" . '&action=edit&message=' . $ex->getMessage());
                    die();
                }
                break;

            case 'delete':
                try {
                    if(!$SessionUser->hasAuthority('ADMIN'))
                        throw new \Exception("Only super admins may delete users");

                    $SessionUser->validatePassword($post['admin_password']);

                    if($User->getID() === $SessionUser->getID())
                        throw new \Exception("Cannot delete self");

                    UserRow::delete($User);
                    $SessionManager->setMessage("Successfully deleted user: " . $User->getUsername());
                    header("Location: {$baseHREF}user/");
                    die();
                } catch (\Exception $ex) {
                    $SessionManager->setMessage($ex->getMessage());
                    header("Location: {$baseHREF}user/?uid={$User->getUID()}" . '&action=delete&message=' . $ex->getMessage());
                    die();
                }

            case 'login':
                if(!$SessionUser->hasAuthority('ADMIN') && $SessionUser->getID() !== $User->getAdminID()) {
                    $SessionManager->setMessage("Could not log in as user. Permission required: ADMIN");
                    header("Location: {$baseHREF}user/?uid={$User->getUID()}");
                    die();
                }
                $SessionManager->adminLoginAsUser($User);
                $SessionManager->setMessage("Admin Login as: " . $User->getUsername());
                header("Location: {$baseHREF}user/?uid={$User->getUID()}");
                die();

            case 'resend-welcome-email':
                $Email = new UserWelcomeEmail($User);
                $Email->send();
                $SessionManager->setMessage("Resent welcome email to " . $User->getEmail());
                header("Location: {$baseHREF}user/?uid={$User->getUID()}");
                die();

            default:
                throw new \InvalidArgumentException("Invalid Action");
        }

    }

    private function handleAuthority()
    {
        $User = $this->getUser();
        $SessionManager = new SessionManager();
        $SessionUser = $SessionManager->getSessionUser();
        if(!$SessionUser->hasAuthority('ADMIN')) {
            // Only admins may edit other users
            if($SessionUser->getID() !== $User->getID() && $SessionUser->getID() !== $User->getAdminID()) {
                $SessionManager->setMessage("Could not make changes to other user. Permission required: ADMIN");

                $baseHREF = defined("BASE_HREF") ? \BASE_HREF : '';
                header("Location: {$baseHREF}user?message=Could not make changes to other user. Permission required: ADMIN");
                die();
            }
        }

    }
}
