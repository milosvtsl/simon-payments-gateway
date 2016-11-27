<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/21/2016
 * Time: 10:21 AM
 */
namespace App\Provision;

class ProvisionAppConfig
{
    /**
     * Render all HTML Head Assets relevant to this APP
     */
    static function renderHTMLHeadContent() {
        if (self::$render_once)
            return;
        self::$render_once = true;

        echo "\t\t<script src='app/provision/assets/app-provision.js'></script>\n";
        echo "\t\t<link href='app/provision/assets/app-provision.css' type='text/css' rel='stylesheet' />\n";
    }

    private static $render_once = false;
}