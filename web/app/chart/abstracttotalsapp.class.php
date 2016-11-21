<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/16/2016
 * Time: 8:19 PM
 */
namespace App\Chart;

use App\AbstractApp;
use User\Model\UserRow;

abstract class AbstractTotalsApp extends AbstractApp
{
    const TIMEOUT = 60;
    const SESSION_KEY = __FILE__;

    private $stats = null;
    private $user = null;

    /**
     * Fetch statistics from the database
     * @return mixed
     */
    abstract protected function fetchStats();

    /**
     * Generate a string representing the user configuration for this app
     * @return mixed
     */
    abstract protected function getConfig();

    public function __construct(UserRow $SessionUser) {
        $this->user = $SessionUser;
    }

    function getStats() {
        if($this->stats)
            return $this->stats;

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

        return $this->stats;
    }

    /**
     * @return \User\Model\UserRow
     */
    function getSessionUser() {
        return $this->user;
    }


    /**
     * Render all HTML Head Assets relevant to this APP
     */
    function renderHTMLHeadContent() {
        if(self::$render_once)
            return;
        self::$render_once = true;

        echo "\t\t<script src='app/chart/assets/app-chart.js'></script>\n";
        echo "\t\t<link href='app/chart/assets/app-chart.css' type='text/css' rel='stylesheet' />\n";
    }
    private static $render_once = false;
}