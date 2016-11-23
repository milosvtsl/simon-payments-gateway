<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/21/2016
 * Time: 10:21 AM
 */
namespace App\Ticket;

class TicketAppConfig
{
    /**
     * Render all HTML Head Assets relevant to this APP
     */
    static function renderHTMLHeadContent() {
        if (self::$render_once)
            return;
        self::$render_once = true;

        echo "\t\t<script src='app/ticket/assets/app-ticket.js'></script>\n";
        echo "\t\t<link href='app/ticket/assets/app-ticket.css' type='text/css' rel='stylesheet' />\n";
    }

    private static $render_once = false;
}