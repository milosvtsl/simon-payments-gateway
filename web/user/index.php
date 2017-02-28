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


$SessionManager = new \User\Session\SessionManager();
$SessionUser = $SessionManager->getSessionUser();
if(!$SessionManager->isLoggedIn()) {
    header('Location: ' . BASE_HREF . 'login.php?message=session has ended');
    die();
}

// Check for User UID parameter
if(isset($_GET['uid'])) {
    if($SessionUser->hasAuthority('ADMIN', 'SUB_ADMIN')
        || $_GET['uid'] === $SessionUser->getUID()) {
        $View = new \User\View\UserView($_GET['uid']);
        $View->handleRequest();
    } else {
        $SessionManager->setMessage("Invalid authority to view user");
        $View = new \User\View\UserView($SessionUser->getUID());
        $View->handleRequest();
    }

} else {
    $View = new User\View\UserListView();
    $View->handleRequest();
}
