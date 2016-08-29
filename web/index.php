<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/27/2016
 * Time: 10:47 PM
 */
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

// Render View
$View = new View\Login\LoginView();
$View->handleRequest();