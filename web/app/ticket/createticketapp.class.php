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
        echo <<<HTML
        <div class="app-ticket app-ticket-create">
            <form name="app-ticket-create">
                <fieldset>
                    <legend>Submit a new Ticket</legend>

                    <input type="text" name="subject" placeholder="Subject" class="themed"/>
                    <br />
                    <textarea name="content" class="themed" placeholder="Message"></textarea>
                    <br />
                    <button name="submit" type="submit" class="themed">Create</button>
                </fieldset>
            </form>
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