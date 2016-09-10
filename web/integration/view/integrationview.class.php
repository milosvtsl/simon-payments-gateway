<?php
/**
 * Created by PhpStorm.
 * Integration: ari
 * Date: 8/29/2016
 * Time: 1:25 PM
 */
namespace Integration\View;

use Config\DBConfig;
use Integration\Model\IntegrationRow;
use User\Session\SessionManager;
use View\AbstractView;

class IntegrationView extends AbstractView
{
    private $_integration;
    private $_action;

    public function __construct($id, $action=null) {
        $this->_action = $action ?: 'view';
        $this->_integration = IntegrationRow::fetchByID($id);
        if(!$this->_integration)
            throw new \InvalidArgumentException("Invalid Integration ID: " . $id);
        parent::__construct();
    }

    /** @return IntegrationRow */
    public function getIntegration() { return $this->_integration; }

    public function renderHTMLBody(Array $params) {
        $SessionUser = SessionManager::get()->getSessionUser();
        if(!$SessionUser->hasAuthority('ROLE_ADMIN')) {
            // Only admins may edit/view integrations
            $this->setSessionMessage("Unable to view integration. Permission required: ROLE_ADMIN");
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            die();
        }

        // Add Breadcrumb links
        $this->getTheme()->addCrumbLink('integration?', "Integrations");
        $this->getTheme()->addCrumbLink('integration?id=' . $this->getIntegration()->getID(), $this->getIntegration()->getName());
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
            default:
                throw new \InvalidArgumentException("Invalid Action: " . $this->_action);
        }

        // Render footer
        $this->getTheme()->renderHTMLBodyFooter();
    }

    public function processFormRequest(Array $post) {
        $SessionUser = SessionManager::get()->getSessionUser();
        if(!$SessionUser->hasAuthority('ROLE_ADMIN')) {
            // Only admins may edit/view integrations
            $this->setSessionMessage("Unable to view/edit integration. Permission required: ROLE_ADMIN");
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            die();
        }

        try {
            // Render Page
            switch($this->_action) {
                case 'edit':
                    $EditIntegration = $this->getIntegration();
                    $EditIntegration->updateFields($post)
                        ? $this->setSessionMessage("Integration Updated Successfully: " . $EditIntegration->getName())
                        : $this->setSessionMessage("No changes detected: " . $EditIntegration->getName());
                    header('Location: integration?id=' . $EditIntegration->getID());
                    die();

                    break;
                case 'delete':
                    print_r($post);
                    die();
                    break;
                default:
                    throw new \InvalidArgumentException("Invalid Action: " . $this->_action);
            }

        } catch (\Exception $ex) {
            $this->setSessionMessage($ex->getMessage());
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            die();
        }
    }
}