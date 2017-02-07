<?php
/**
 * Created by PhpStorm.
 * Merchant: ari
 * Date: 8/27/2016
 * Time: 10:47 PM
 */
// Enable error reporting for this page
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Go up 1 directory
chdir('../..');

// Enable class autoloader for this page instance
spl_autoload_extensions('.class.php');
spl_autoload_register();

// Register Exception Handler
\System\Exception\ExceptionHandler::register();

// Start or resume the session
session_start();

$SessionManager = new \User\Session\SessionManager();
//$SessionUser = $SessionManager->getSessionUser();
if(!$SessionManager->isLoggedIn()) {
    header('Location: /login.php?message=session has ended');
    die();
}

if(!empty($_GET['uid'])) {
    try {
        $View = new \Support\View\SupportTicketView($_GET['uid'], @$_GET['action']);
        $View->handleRequest();

    } catch (Exception $ex) {
        $View = new \Support\View\SupportTicketListView();
        $SessionManager->setMessage($ex->getMessage());
        $View->redirectRequest();
    }
} else {
    $View = new \Support\View\SupportTicketListView();
    $View->handleRequest();
}
