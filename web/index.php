<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/27/2016
 * Time: 10:47 PM
 */
switch(strtolower($_SERVER["HTTP_HOST"])) {
    case "1admin.paylogicnetwork.com":
        Header( "HTTP/1.1 301 Moved Permanently" );
        header("location: https://admin.paylogicnetwork.com:8443/");
        die;

    case 'access.simonpayments.com':
        if($_SERVER["HTTPS"] != "on")
        {
            header("Location: https://access.simonpayments.com");
            exit();
        }
        break;
}


error_reporting(E_ALL);
ini_set('display_errors', 1);

// Enable class autoloader
spl_autoload_extensions('.class.php');
spl_autoload_register(function ($class) {
    $path = __DIR__ . '/' . strtolower(str_replace('\\', '/', $class)) . '.class.php';
//    var_dump(getcwd());
//    var_dump(__FILE__);
//    var_dump(file_exists($path));
//    var_dump($path);
    require $path;
});

// Start or resume the session
session_start();

$SessionManager = new \User\Session\SessionManager();

// Render View
if($SessionManager->isLoggedIn()) {
//    $View = new User\View\UserView($SessionManager->getSessionUser()->getID());
    $View = new \User\View\DashboardView();
    $View->handleRequest();
} else {
    $View = new User\View\LoginView();
    $View->handleRequest();
}
