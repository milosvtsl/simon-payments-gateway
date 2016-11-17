<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/16/2016
 * Time: 8:19 PM
 */
namespace App\Total;

use App\AbstractApp;
use User\Model\UserRow;

abstract class AbstractTotalsApp extends AbstractApp
{
    const TIMEOUT = 60;
    const SESSION_KEY = __FILE__;

    private $stats = null;
    private $user = null;

    abstract protected function fetchStats();

    public function __construct(UserRow $SessionUser) {
        $this->user = $SessionUser;

        // Session key based on file path
        $sessionKey = static::SESSION_KEY;

        // Check Session for cached statistics
        if(isset($_SESSION[$sessionKey]))  {
            $this->stats = $_SESSION[$sessionKey];

            // Clear cache if the timeout has been reached
            if($this->stats['timestamp'] < time() + static::TIMEOUT)
                $this->stats = null;
        }

        if(!$this->stats) {
            $this->stats = $this->fetchStats();
            $this->stats['timestamp'] = time();
            $_SESSION[$sessionKey] = $this->stats;
        }
    }

    /**
     * @return \User\Model\UserRow
     */
    function getSessionUser() {
        return $this->user;
    }

    function getStats() {
        return $this->stats;
    }


    /**
     * Render all HTML Head Assets relevant to this APP
     */
    function renderHTMLHeadContent() {
        static $render_once = false;
        if($render_once)
            return;
        $render_once = true;

        echo "\t\t<script src='app/total/assets/app-total.js'></script>\n";
        echo "\t\t<link href='app/total/assets/app-total.css' type='text/css' rel='stylesheet' />\n";
    }

}