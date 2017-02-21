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

if(!empty($_GET['id'])) {
    try {
        $View = new \Integration\Request\View\IntegrationRequestView($_GET['id'], @$_GET['action']);
        $View->handleRequest();

    } catch (Exception $ex) {
        $View = new Integration\Request\View\IntegrationRequestListView();
        $SessionManager->setMessage($ex->getMessage());
        $View->redirectRequest();
    }
} else {
    $View = new Integration\Request\View\IntegrationRequestListView();
    $View->handleRequest();
}
