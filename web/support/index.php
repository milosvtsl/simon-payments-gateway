<?php
/**
 * Created by PhpStorm.
 * Merchant: ari
 * Date: 8/27/2016
 * Time: 10:47 PM
 */

// Go up 2 directories
chdir('../..');
define("BASE_HREF", '../../'); // Set relative path

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
    header('Location: ' . BASE_HREF . 'login.php?message=session has ended');
    die();
}

if(!empty($_GET['uid'])) {
    try {
        $View = new \Support\View\SupportTicketView($_GET['uid'], @$_GET['action']);
        $View->handleRequest();

    } catch (Exception $ex) {
        $View = new \Support\View\SupportTicketListView();
        $SessionManager->setMessage("<div class='error'>" . $ex->getMessage() . "</div>");
        $View->redirectRequest();
    }
} else {
    $View = new \Support\View\SupportTicketListView();
    $View->handleRequest();
}
