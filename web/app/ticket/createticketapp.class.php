<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/16/2016
 * Time: 8:19 PM
 */
namespace App\ticket;

use App\AbstractApp;

class CreateTicketApp extends AbstractApp
{
    const SESSION_KEY = __FILE__;

    /**
     * Render all HTML Head Assets relevant to this APP
     */
    function renderHTMLHeadContent() {
        if(self::$render_once)
            return;
        self::$render_once = true;

        echo "\t\t<script src='app/ticket/assets/app-ticket.js'></script>\n";
        echo "\t\t<link href='app/ticket/assets/app-ticket.css' type='text/css' rel='stylesheet' />\n";
    }
    private static $render_once = false;

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
}