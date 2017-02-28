<?php
use System\Exception\ExceptionHandler;
use User\Session\SessionManager;

/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/27/2016
 * Time: 10:47 PM
 */

// Go up 1 directory
define("BASE_HREF", '../../'); // Set relative path
chdir(BASE_HREF);

// Enable class autoloader for this page instance
spl_autoload_extensions('.class.php');
spl_autoload_register();

// Register Exception Handler
ExceptionHandler::register();

// Start or resume the session
session_start();

$SessionManager = new SessionManager();
//$SessionUser = $SessionManager->getSessionUser();
if(!$SessionManager->isLoggedIn()) {
    header('Location: ' . BASE_HREF . 'login.php?message=session has ended');
    die();
}

$View = new \Order\Fee\View\FeeListView();
$View->handleRequest();
