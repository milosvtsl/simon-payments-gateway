<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/27/2016
 * Time: 10:47 PM
 */

// Enable class autoloader
spl_autoload_extensions('.php');
spl_autoload_register();

// Render View
$View = new \View\Pages\Index();
$View->render();