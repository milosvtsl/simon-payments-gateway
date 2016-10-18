<?php
namespace User\View;

use System\Mail\ResetPasswordEmail;
use User\Model\UserRow;
use User\Session\SessionManager;
use View\AbstractView;


class LoginView extends AbstractView {

    const VIEW_PATH = '?';
    const VIEW_NAME = 'Login';

    private $action;
    public function __construct($action='login') {
        parent::__construct();
        $this->action = $action ?: 'login';
    }

    protected function renderHTMLHeadLinks() {
        parent::renderHTMLHeadLinks();
        echo "\t\t<link href='user/view/assets/login.css' type='text/css' rel='stylesheet' />\n";
    }

    protected function renderHTMLBody(Array $params) {
        $Theme = $this->getTheme();

        if(!empty($params['message']))
            $this->setException(new \Exception($params['message']));

        $action = isset($params['action']) ? $params['action'] : $this->action;
        switch($action) {
            case 'login':
                include ('.login.php');
                break;

            case 'logout':
                $this->processFormRequest(array());
//                include ('.logout.php');
                break;

            case 'signup':
                include ('.signup.php');
                break;

            case 'reset':
                include('.reset.php');
                break;

            default:
                $this->setException(new \InvalidArgumentException("Unknown action"));
                include ('.login.php');
                break;
        }

    }



    public function processFormRequest(Array $post) {
        $action = isset($post['action']) ? $post['action'] : $this->action;
        switch ($action) {
            case 'login':
                try {
                    if (!isset($post['username']))
                        throw new \InvalidArgumentException("Missing field: username");
                    $username = $post['username'];

                    if (!isset($post['password']))
                        throw new \InvalidArgumentException("Missing field: password");
                    $password = $post['password'];


                    $SessionManager = new SessionManager();
                    $NewUser = $SessionManager->login($username, $password);

                    $this->setSessionMessage("Welcome, " . $NewUser->getUsername());
                    header("Location: /?action=start");

                } catch (\Exception $ex) {
                    $this->setSessionMessage($ex->getMessage());
                    header("Location: login.php");
                }
                break;

            case 'logout':
                try {
                    $SessionManager = new SessionManager();
                    $SessionManager->logout();

                    $this->setSessionMessage("Logged out successfully");
                    header("Location: /");
                } catch (\Exception $ex) {
                    $this->setSessionMessage($ex->getMessage());
                    header("Location: login.php");
                }
                break;

            case 'reset':
                $email = @$post['email'];
                $key = @$post['key'];
                try {

                    $User = UserRow::fetchByEmail($email);
                    $Email = new ResetPasswordEmail($User);

                    // If Key was given, reset password
                    if(!$key) {
                        if(!$User->isValidResetKey($key))
                            throw new \InvalidArgumentException("Invalid Reset Key");

                        $User->changePassword($post['password'], $post['password_confirm']);
                        $this->setSessionMessage("Password was reset successfully");
                        header("Location: login.php");
                        die();
                    }

                    // If no key, send a reset link
                    if(!$User) {
                        $this->setSessionMessage("User was not found");
                        header("Location: reset.php?key={$key}&email={$email}");
                        die();
                    }

                    if(!$Email->send()){
                        $this->setSessionMessage($Email->ErrorInfo);
                        header("Location: reset.php?key={$key}&email={$email}");
                        die();
                    } else {
                        $this->setSessionMessage("Email was sent successfully");
                    }

                    header("Location: login.php");
                    die();
                } catch (\Exception $ex) {
                    $this->setSessionMessage($ex->getMessage());
                    header("Location: reset.php?key={$key}&email={$email}");
                }

            default:
                $this->setSessionMessage("Unknown action");
                header("Location: login.php");
                break;
        }
    }

}

