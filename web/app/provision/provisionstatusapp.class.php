<?php
namespace App\Provision;
use App\AbstractApp;
use User\Model\UserRow;

/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/14/2016
 * Time: 4:11 PM
 */
class ProvisionStatusApp extends AbstractApp {
    const SESSION_KEY = __FILE__;

    private $user;
    private $config;

    public function __construct(UserRow $SessionUser, $config) {
        $this->user = $SessionUser;
        $this->config = $config;
    }

    public function getUser() { return $this->user; }

    /**
     * Print an HTML representation of this app
     * @param array $params
     */
    function renderAppHTML(Array $params = array())
    {
        $appClassName = 'app-provision-status';
        echo <<<HTML
        <div class="app-provision app-provision-status">
            <form name="app-provision-status">
                <div class="app-section-top">
                    <div class="app-section-text-large" style="text-align: center;"> Merchant Status</div>
                    <hr />
                </div>
                <ul class="app-provision-list">
                    <li>Provision Status: N/A</li>
                </ul>
            </form>
            <div class="app-button-config">
                <ul>
                    <li><a href="#" onclick="appProvisionAction('move-up', '{$appClassName}');">Move up</a></li>
                    <li><a href="#" onclick="appProvisionAction('move-down', '{$appClassName}');">Move down</a></li>
                    <li><a href="#" onclick="appProvisionAction('move-top', '{$appClassName}');">Move to top</a></li>
                    <li><a href="#" onclick="appProvisionAction('move-bottom', '{$appClassName}');">Move to bottom</a></li>
                    <li><a href="#" onclick="appProvisionAction('config', '{$appClassName}');">Configure...</a></li>
                    <li><a href="#" onclick="appProvisionAction('remove', '{$appClassName}');">Remove</a></li>
                </ul>
            </div>
        </div>
HTML;
    }

    /**
     * Render all HTML Head Assets relevant to this APP
     */
    function renderHTMLHeadContent() {
        ProvisionAppConfig::renderHTMLHeadContent();
    }

}

