<?php
/**
 * Created by PhpStorm.
 * Integration: ari
 * Date: 8/29/2016
 * Time: 1:25 PM
 */
namespace Integration\View;

use Integration\Model\IntegrationRow;
use User\Session\SessionManager;
use View\AbstractView;

class IntegrationView extends AbstractView
{
    const VIEW_PATH = 'integration';
    const VIEW_NAME = 'Integration';

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
        $baseHREF = defined("BASE_HREF") ? \BASE_HREF : '';

        $SessionManager = new SessionManager();
        $SessionUser = $SessionManager->getSessionUser();
        if(!$SessionUser->hasAuthority('ROLE_ADMIN')) {
            // Only admins may edit/view integrations
            $SessionManager->setMessage("Unable to view integration. Permission required: ROLE_ADMIN");
            header("Location: {$baseHREF}integration?id=" . $this->getIntegration()->getID() . '&action=edit&message=Unable to manage integration: Admin required');
            die();
        }

        // Render Page
        switch($this->_action) {
            case 'view':
                $this->renderHTMLViewBody($params);
                break;
            case 'edit':
                $this->renderHTMLEditBody($params);
                break;
            default:
                throw new \InvalidArgumentException("Invalid Action: " . $this->_action);
        }
    }

    public function processFormRequest(Array $post) {
        $baseHREF = defined("BASE_HREF") ? \BASE_HREF : '';

        $SessionManager = new SessionManager();
        $SessionUser = $SessionManager->getSessionUser();
        if(!$SessionUser->hasAuthority('ROLE_ADMIN')) {
            // Only admins may edit/view integrations
            $SessionManager->setMessage("Unable to view/edit integration. Permission required: ROLE_ADMIN");
            header("Location: {$baseHREF}integration?id=" . $this->getIntegration()->getID() . '&action='.$this->_action.'&message=Unable to manage integration: Admin required');
            die();
        }

        try {
            // Render Page
            switch($this->_action) {
                case 'edit':
                    $EditIntegration = $this->getIntegration();
                    $EditIntegration->updateFields($post)
                        ? $SessionManager->setMessage("Integration Updated Successfully: " . $EditIntegration->getName())
                        : $SessionManager->setMessage("No changes detected: " . $EditIntegration->getName());
                    header("Location: {$baseHREF}integration?id=" . $EditIntegration->getID());
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
            $SessionManager->setMessage($ex->getMessage());
            header("Location: {$baseHREF}integration?id=" . $this->getIntegration()->getID() . '&action='.$this->_action.'&message=Unable to manage integration: Admin required');
            die();
        }
    }

    private function renderHTMLEditBody($params)
    {
    }
    
    private function renderHTMLViewBody($params)
    {
        $Integration = $this->getIntegration();
        $odd = false;
        $action_url = 'integration?id=' . $Integration->getID() . '&action=';

        $Theme = $this->getTheme();
        $Theme->addPathURL('integration',                   'Integration');
        $Theme->addPathURL($action_url,                     $Integration->getName());
        $Theme->renderHTMLBodyHeader();
        $Theme->printHTMLMenu('integration-view',    $action_url);
        ?>

        <article class="themed">
            <section class="content">


                <?php if($SessionManager->hasMessage()) echo "<h5>", $SessionManager->popMessage(), "</h5>"; ?>

                <form class="form-view-integration themed" onsubmit="return false;">
                    <fieldset>
                        <div class="legend">Integration Information</div>
                        <table class="table-integration-info themed striped-rows">
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">ID</td>
                                <td><?php echo $Integration->getID(); ?></td>
                            </tr>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">UID</td>
                                <td><?php echo $Integration->getUID(); ?></td>
                            </tr>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">Name</td>
                                <td><?php echo $Integration->getName(); ?></td>
                            </tr>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">Class Path</td>
                                <td><?php echo $Integration->getClassPath(); ?></td>
                            </tr>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">API Username</td>
                                <td><?php echo $Integration->getAPIUsername(); ?></td>
                            </tr>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">API Password</td>
                                <td><?php echo $Integration->getAPIPassword(); ?></td>
                            </tr>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">API URL Base</td>
                                <td><?php echo $Integration->getAPIURLBase(); ?></td>
                            </tr>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td colspan="2">
                                    <pre><?php echo $Integration->getNotes() ?: "No Notes"; ?></pre>
                                </td>
                            </tr>
                        </table>
                    </fieldset>
                </form>
            </section>
        </article>
        <?php
    }
}