<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/27/2016
 * Time: 10:47 PM
 */

// Go up 1 directory
chdir('..');
define("BASE_HREF", '../'); // Set relative path

// Enable class autoloader for this page instance
spl_autoload_extensions('.class.php');
spl_autoload_register();

// Register Exception Handler
\System\Exception\ExceptionHandler::register();

// Start or resume the session
session_start();

$View = new User\View\LoginView('logout');
$View->handleRequest();