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
use View\AbstractView;

class UserView extends AbstractView
{
    private $_user;
    private $_action;

    public function __construct($id, $action=null) {
        $this->_action = $action ?: 'view';
        $this->_user = UserRow::fetchByID($id);
        if(!$this->_user)
            throw new \InvalidArgumentException("Invalid User ID: " . $id);
        parent::__construct();
    }

    /** @return UserRow */
    public function getUser() { return $this->_user; }

    public function renderHTMLBody(Array $params) {
        // Add Breadcrumb links
        $this->getTheme()->addCrumbLink('user', "Users");
        $this->getTheme()->addCrumbLink('user?id=' . $this->_user->getID(), $this->_user->getUsername());
        $this->getTheme()->addCrumbLink($_SERVER['REQUEST_URI'], ucfirst($this->_action));

        // Render Header
        $this->getTheme()->renderHTMLBodyHeader();

        // Render Page
        switch($this->_action) {
            case 'view':
                include('.view.php');
                break;
            case 'edit':
                include('.edit.php');
                break;
            case 'delete':
                include('.delete.php');
                break;
            case 'change':
                include('.change.php');
                break;
            default:
                throw new \InvalidArgumentException("Invalid Action: " . $this->_action);
        }

        // Render footer
        $this->getTheme()->renderHTMLBodyFooter();
    }

    public function processFormRequest(Array $post) {
        try {
            // Render Page
            switch($this->_action) {
                case 'edit':
                    $EditUser = $this->getUser();
                    $EditUser->updateFields($post)
                        ? $this->setSessionMessage("User Updated Successfully: " . $EditUser->getUID())
                        : $this->setSessionMessage("No changes detected: " . $EditUser->getUID());
                    header('Location: user?id=' . $EditUser->getID());

                    break;
                case 'delete':
                    print_r($post);
                    die();
                    break;
                case 'change':
                    print_r($post);
                    die();
                    break;
                default:
                    throw new \InvalidArgumentException("Invalid Action: " . $this->_action);
            }

        } catch (\Exception $ex) {
            $this->setSessionMessage($ex->getMessage());
            header('Location: ' . $_SERVER['HTTP_REFERER']);
        }
    }
}