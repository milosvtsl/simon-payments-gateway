<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/27/2016
 * Time: 10:47 PM
 */
// Enable error reporting for this page
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Go up 1 directory
chdir('..');

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
} catch (InvalidArgumentException $ex) {
//    $View = new \Order\View\OrderListView();
    $View = new \User\View\LoginView();
    $View->setSessionMessage(
        "<span class='error'>" .
        $ex->getMessage() .
        "</span>"
    );
}
$View->handleRequest();
