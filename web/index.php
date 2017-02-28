<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/27/2016
 * Time: 10:47 PM
 */
use System\Exception\ExceptionHandler;
use User\Session\SessionManager;
use User\View\DashboardView;
use User\View\LoginView;


// Enable class autoloader
spl_autoload_extensions('.class.php');
spl_autoload_register(function ($class) {
    $path = __DIR__ . '/' . strtolower(str_replace('\\', '/', $class)) . '.class.php';
    require $path;
});


// Register Exception Handler
ExceptionHandler::register();

// Start or resume the session
session_start();

$SessionManager = new SessionManager();

// Render View
if($SessionManager->isLoggedIn()) {
    $View = new DashboardView();
    $View->handleRequest();

} else {
    $View = new LoginView();
    $View->handleRequest();

}
