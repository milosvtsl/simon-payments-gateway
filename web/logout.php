<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/27/2016
 * Time: 10:47 PM
 */

// Enable class autoloader
spl_autoload_extensions('.class.php');
spl_autoload_register();

// Start or resume the session
session_start();

// Render View
$View = new \View\Index();
$View->renderHTML();