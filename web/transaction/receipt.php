<?php
/**
 * Created by PhpStorm.
 * User: ari
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

if(!isset($_GET['uid']))
    die("Invalid UID");
$OrderRow = \Order\Model\OrderRow::fetchByID($_GET['uid']);
$View = new \Order\View\OrderView($OrderRow->getID(), @$_GET['action'] ?: 'receipt');
$View->handleRequest();

