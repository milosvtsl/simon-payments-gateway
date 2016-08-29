<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/27/2016
 * Time: 10:47 PM
 */

// Enable class autoloader
spl_autoload_extensions('.class.php');
spl_autoload_register();

// Start or resume the session
session_start();

// Render View
$View = new View\Login\LogoutView();
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // try log in
        $SessionManager = new User\SessionManager();
        $SessionManager->logout();

        header("Location: login.php?action=logout");

    } catch (Exception $ex) {
        // If error, render view with exception
        $View->setException($ex);
        $View->renderHTML();
    }

} else {
    $View->renderHTML();
}
