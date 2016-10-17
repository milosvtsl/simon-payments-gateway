<?php
namespace User\View;

use User\Session\SessionManager;
use View\AbstractView;


class LoginView extends AbstractView {

    const VIEW_PATH = '?';
    const VIEW_NAME = 'Login';

    private $action;
    public function __construct($action='login') {
        parent::__construct();
        $this->action = $action;
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
                include ('.reset.php');
                break;

            default:
                $this->setException(new \InvalidArgumentException("Unknown action"));
                include ('.login.php');
                break;
        }

    }



    public function processFormRequest(Array $post) {
        $action = isset($post['action']) ? $post['action'] : $this->action;
        try {
            switch ($action) {
                case 'login':
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
                    break;

                case 'logout':
                    $SessionManager = new SessionManager();
                    $SessionManager->logout();

                    $this->setSessionMessage("Logged out successfully");
                    header("Location: /");
                    break;

                case 'reset':
                    $this->setSessionMessage("TODO");
                    header("Location: login.php?action=reset");
                    break;

                default:
                    $this->setSessionMessage("Unknown action");
                    header("Location: login.php");
                    break;
            }

        } catch (\Exception $ex) {
            $this->setSessionMessage($ex->getMessage());
            header("Location: login.php");
        }
    }

}

