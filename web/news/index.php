<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/27/2016
 * Time: 10:47 PM
 */

// Go up 1 directory
chdir('..');

// Enable class autoloader
spl_autoload_extensions('.class.php');
spl_autoload_register();

// Start or resume the session
session_start();

$SessionManager = new \User\Session\SessionManager();
if(!$SessionManager->isLoggedIn()) { // !$SessionUser->hasAuthority('ROLE_ADMIN')) {
    header('Location: /login.php?message=session has ended');
    die();
}

// Render View
$View = new \Home\View\HomeView();
$View->handleRequest();