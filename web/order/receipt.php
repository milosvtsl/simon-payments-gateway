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

if(!isset($_GET['uid']))
    die("Invalid UID");

try {
    $OrderRow = \Order\Model\OrderRow::fetchByUID($_GET['uid']);
    $View = new \Order\View\OrderView($OrderRow->getID(), @$_GET['action'] ?: 'receipt');
    $View->handleRequest();

} catch (InvalidArgumentException $ex) {
//    $View = new \Order\View\OrderListView();

    $SessionManager = new \User\Session\SessionManager();
    if(!$SessionManager->isLoggedIn()) {
        header('Location: ' . BASE_HREF . 'login.php?message=' . $ex->getMessage());
        die();
    }

    throw $ex;
}
