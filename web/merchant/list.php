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
chdir('..');

// Enable class autoloader for this page instance
spl_autoload_extensions('.class.php');
spl_autoload_register();

// Start or resume the session
session_start();

$SessionManager = new \User\Session\SessionManager();
if(!$SessionManager->isLoggedIn()) { // !$SessionUser->hasAuthority('ROLE_ADMIN')) {
    header('Location: /login.php?message=session has ended');
    die();
}

$View = new Merchant\View\MerchantListView();
$View->handleRequest();
