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

// Render View
$View = new View\Login\Auth();
if($_POST) {
    // try log in

    // If error, render view with exception
    $View->setException(new Exception('omfg'));
}
$View->renderHTML();