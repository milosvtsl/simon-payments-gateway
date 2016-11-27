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

class CreateTicketApp extends AbstractApp
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
        $appClassName = 'app-ticket-create';

        echo <<<HTML
        <div class="app-ticket {$appClassName}">
            <form name="{$appClassName}">
                <div class="app-section-top">
                    <div class="app-section-text-large" style="text-align: center;">Create a Support Ticket</div>
                </div>

                <textarea name="content" class="themed" placeholder="Coming Soon..." rows="5" cols="30" style="width: 19.4em;"></textarea>

                <select name="category" required style="min-width: 15em;">
                    <option value="">Choose a Category</option>
                    <option>Technical</option>
                    <option>Reporting</option>
                    <option>Billing</option>
                    <option>Sales</option>
                </select>

                <button type="submit" name="submit" style="margin-bottom: 0.5em; float: right;" disabled="disabled">Create</button>
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