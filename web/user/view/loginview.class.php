<?php
namespace User\View;

use User\Mail\ResetPasswordEmail;
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
        $SessionManager = new SessionManager();

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
                    $SessionManager->setMessage("Logged in as " . $NewUser->getUsername());
                    header("Location: index.php");

                } catch (\Exception $ex) {
                    $SessionManager->setMessage($ex->getMessage());
                    header("Location: login.php");
                }
                break;

            case 'logout':
                try {
                    $SessionManager = new SessionManager();
                    $SessionUser = $SessionManager->getSessionUser();
                    $SessionManager->logout();
                    $SessionManager->setMessage("<div class='info'>Logged out successfully: " . $SessionUser->getUsername() . "</div>");

                    $baseHREF = defined("BASE_HREF") ? \BASE_HREF : '';
                    if($SessionManager->isLoggedIn()) {
                        header("Location: {$baseHREF}user?uid={$SessionUser->getUID()}");

                    } else {
                        header("Location: {$baseHREF}index.php");
                    }

                } catch (\Exception $ex) {
                    $SessionManager->setMessage($ex->getMessage());
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
                    if($key) {
                        if(!$User->isValidResetKey($key)) {
                            $SessionManager->setMessage("<div class='error'>Invalid Reset Key</div>");
                            header("Location: reset.php?email=".$email);
                            die();
                        }

                        $update = $User->updateFields($post);
                        if($update)
                            $SessionManager->setMessage("<div class='info'>Password was reset successfully</div>");
                        else
                            $SessionManager->setMessage("<div class='error'>Error: Password was not reset</div>");
                        header("Location: login.php");
                        die();
                    }

                    // If no key, send a reset link
                    if(!$User) {
                        $SessionManager->setMessage("<div class='error'>User was not found</div>");
                        header("Location: reset.php?key={$key}&email={$email}");
                        die();
                    }

                    if(!$Email->send()){
                        $SessionManager->setMessage("<div class='error'>". $Email->ErrorInfo . "</div>");
                        header("Location: reset.php?key={$key}&email={$email}");
                        die();
                    } else {
                        $SessionManager->setMessage("<div class='info'>Email was sent successfully</div>");
                    }

                    header("Location: login.php");
                    die();
                } catch (\Exception $ex) {
                    $SessionManager->setMessage("<div class='error'>" . $ex->getMessage() . "</div>");
                    header("Location: reset.php?key={$key}&email={$email}");
                    die();
                }
            break;

            default:
                $SessionManager->setMessage("Unknown action");
                header("Location: login.php");
                die();
        }
    }

}

