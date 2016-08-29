<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/29/2016
 * Time: 1:25 PM
 */
namespace View\User;

use Config\DBConfig;
use User\UserRow;
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
    }

    /** @return UserRow */
    public function getUser() { return $this->_user; }

    public function renderHTMLBody(Array $params) {
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
        }

        // Render footer
        $this->getTheme()->renderHTMLBodyFooter();
    }

    protected function processRequest(Array $post) {
        // Render on POST
        $this->renderHTML();
    }
}