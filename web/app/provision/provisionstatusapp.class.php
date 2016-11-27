<?php
namespace App\Provision;
use App\AbstractApp;
use Integration\Model\IntegrationRow;
use Merchant\Model\MerchantRow;
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
        /** @var UserRow $SessionUser */
        $SessionUser = $this->user;

        $statusHTML = '';
        $MerchantQuery = $SessionUser->queryUserMerchants();
        foreach ($MerchantQuery as $Merchant) {
            /** @var MerchantRow $Merchant */
            foreach ($Merchant->getMerchantIdentities() as $MerchantIdentity) {
                $reason = null;
                $Integration = $MerchantIdentity->getIntegrationRow();
                if($Integration->getAPIType() === IntegrationRow::ENUM_API_TYPE_DISABLED)
                    continue;
                if($Integration->getAPIType() === IntegrationRow::ENUM_API_TYPE_TESTING)
                    continue;

                if($MerchantIdentity->isProvisioned($reason)) {
                    $statusHTML .= "\n\t\t\t\t<li>" . $Merchant->getShortName() . ": <span class='ready'>Ready</span></li>";
                } else {
                    $statusHTML .= "\n\t\t\t\t<li>" . $Merchant->getShortName() . ": <span class='not-ready'>{$reason}</span></li>";
                }
            }
        }


        $appClassName = 'app-provision-status';
        echo <<<HTML
        <div class="app-provision app-provision-status">
            <form name="app-provision-status">
                <div class="app-section-top">
                    <div class="app-section-text-large" style="text-align: center;">Provision Status</div>
                    <hr />
                </div>
                <ul class="app-provision-list">
                    {$statusHTML}
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

