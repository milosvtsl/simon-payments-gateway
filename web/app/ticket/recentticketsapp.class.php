<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/16/2016
 * Time: 8:19 PM
 */
namespace App\Ticket;

use App\AbstractApp;
use User\Model\UserRow;

class RecentTicketsApp extends AbstractApp
{
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
        $appClassName = 'app-ticket-recent';
        echo <<<HTML
        <div class="app-ticket app-ticket-recent">
            <form name="app-ticket-recent">
                <div class="app-section-top">
                    <div class="app-section-text-large" style="text-align: center;"> Recent Tickets</div>
                    <hr />
                </div>
                <ul class="app-ticket-list">
                    <li style="font-style: italic;">No recent support tickets</li>
                </ul>
            </form>
            <div class="app-button-config">
                <ul>
                    <li><a href="#" onclick="appTicketAction('move-up', '{$appClassName}');">Move up</a></li>
                    <li><a href="#" onclick="appTicketAction('move-down', '{$appClassName}');">Move down</a></li>
                    <li><a href="#" onclick="appTicketAction('move-top', '{$appClassName}');">Move to top</a></li>
                    <li><a href="#" onclick="appTicketAction('move-bottom', '{$appClassName}');">Move to bottom</a></li>
                    <li><a href="#" onclick="appTicketAction('config', '{$appClassName}');">Configure...</a></li>
                    <li><a href="#" onclick="appTicketAction('remove', '{$appClassName}');">Remove</a></li>
                </ul>
            </div>
        </div>
HTML;
    }

    /**
     * Render all HTML Head Assets relevant to this APP
     */
    function renderHTMLHeadContent() {
        TicketAppConfig::renderHTMLHeadContent();
    }
}