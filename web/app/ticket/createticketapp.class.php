<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/16/2016
 * Time: 8:19 PM
 */
namespace App\Ticket;

use App\AbstractApp;

class CreateTicketApp extends AbstractApp
{
    const SESSION_KEY = __FILE__;

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
                <fieldset>
                    <legend>Submit a new Ticket</legend>

                    <input type="text" name="subject" placeholder="Subject" class="themed"/>
                    <br />
                    <textarea name="content" class="themed" placeholder="Message"></textarea>
                    <br />
                    <button name="submit" type="submit" class="themed">Create</button>
                </fieldset>
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