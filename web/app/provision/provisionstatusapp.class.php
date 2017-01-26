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
                if($Integration->getAPIType() !== IntegrationRow::ENUM_API_TYPE_PRODUCTION)
                    continue;
                if($Integration->getAPIType() === IntegrationRow::ENUM_API_TYPE_TESTING)
                    continue;

                $statusHTML .= "\n\t\t\t\t<li>"
                            . "<a href='merchant?id=" . $Merchant->getID() . "'>" . $Merchant->getShortName() . "</a>: ";

                if($MerchantIdentity->isProvisioned($reason)) {
                    $statusHTML .= "<span class='ready'>Ready</span></li>";
                } else {
                    $statusHTML .= "<span class='not-ready'>{$reason}</span></li>";
                }
            }
        }

        // TODO: cache?

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
            <div class="app-button app-button-config app-button-top-right">
                <ul class="app-menu">
                    <li><div class='app-button app-button-top'></div><a href="#" onclick="appProvisionAction('move-top', '{$appClassName}');">Move to top</a></li>
                    <li><div class='app-button app-button-up'></div><a href="#" onclick="appProvisionAction('move-up', '{$appClassName}');">Move up</a></li>
                    <li><div class='app-button app-button-down'></div><a href="#" onclick="appProvisionAction('move-down', '{$appClassName}');">Move down</a></li>
                    <li><div class='app-button app-button-bottom'></div><a href="#" onclick="appProvisionAction('move-bottom', '{$appClassName}');">Move to bottom</a></li>
                    <li><div class='app-button app-button-config'></div><a href="#" onclick="appProvisionAction('config', '{$appClassName}');">Configure...</a></li>
                    <li><div class='app-button app-button-remove'></div><a href="#" onclick="appProvisionAction('remove', '{$appClassName}');">Remove</a></li>
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

