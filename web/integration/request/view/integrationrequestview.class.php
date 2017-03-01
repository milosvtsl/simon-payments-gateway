<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/29/2016
 * Time: 1:25 PM
 */
namespace Integration\Request\View;

use Integration\Request\Model\IntegrationRequestRow;
use User\Session\SessionManager;
use View\AbstractView;

class IntegrationRequestView extends AbstractView
{
    const VIEW_PATH = 'integration/request';
    const VIEW_NAME = 'Integration Requests';

    private $_request;
    private $_action;

    public function __construct($id, $action=null) {
        $this->_action = $action ?: 'view';
        $this->_request = IntegrationRequestRow::fetchByID($id);
        if(!$this->_request)
            throw new \InvalidArgumentException("Invalid Integration Request ID: " . $id);
        parent::__construct();
    }

    /** @return IntegrationRequestRow */
    public function getRequest() { return $this->_request; }

    public function renderHTMLBody(Array $params) {

        // Render Page
        switch($this->_action) {
            case 'view':
                include('.view.php');
                break;
            case 'edit':
                include('.edit.php');
                break;
            default:
                throw new \InvalidArgumentException("Invalid Action: " . $this->_action);
        }
    }

    public function processFormRequest(Array $post) {
        try {
            // Render Page
            switch($this->_action) {
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
            $SessionManager = new SessionManager();
            $SessionManager->setMessage("<div class='error'>" . $ex->getMessage() . "</div>");
            $baseHREF = defined("BASE_HREF") ? \BASE_HREF : '';
            header("Location: {$baseHREF}integration/request?id=" . $this->getRequest()->getID() . '&action=edit&message=Unable to manage batch: ' . $ex->getMessage());

            die();
        }
    }
}