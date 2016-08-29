<?php
namespace View\Login;

use User\SessionManager;
use View\AbstractView;


class LoginView extends AbstractView {
    private $_action;

    public function __construct($action=null) {
        $this->_action = $action ?: 'login';
        parent::__construct();
    }

    protected function renderHTMLHeadLinks() {
        echo "\t\t<link href='assets/css/login.css' type='text/css' rel='stylesheet' />\n";
        parent::renderHTMLHeadLinks();
    }

    protected function renderHTMLBody(Array $params) {
        switch($this->_action) {
            case 'login':
                include ('.login.php');
                break;

            case 'logout':
                include ('.logout.php');
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



    protected function processRequest(Array $post) {
        switch ($this->_action) {
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
                header("Location: home.php?action=start");
                break;

            case 'logout':
                $SessionManager = new SessionManager();
                $SessionManager->logout();

                $this->setSessionMessage("Logged out successfully");
                header("Location: login.php");
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
    }

}

