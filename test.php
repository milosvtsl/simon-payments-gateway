<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/27/2016
 * Time: 10:47 PM
 */

chdir('web');
// Enable class autoloader
spl_autoload_extensions('.class.php');
spl_autoload_register();

// try log in
$SessionManager = new \User\Session\SessionManager();
$SessionUser = $SessionManager->getSessionUser();

assert(!$SessionManager->isLoggedIn(), "Guest should not be logged in");

// Create Test User


// Set Test User Password


// Validate login

$TestUser = \User\Model\UserRow::fetchByUsername('testuser');
assert($TestUser !== null);

echo 'Test successful';
chdir('..');