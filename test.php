<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/27/2016
 * Time: 10:47 PM
 */

chdir('web');
$cwd0 = getcwd().'';
// Enable class autoloader
spl_autoload_extensions('.class.php');
spl_autoload_register();

echo "Testing ... ", __FILE__, PHP_EOL;

// try log in
$SessionManager = new \User\Session\SessionManager();
$SessionUser = $SessionManager->getSessionUser();

assert(!$SessionManager->isLoggedIn(), "Guest should not be logged in");

// TODO: Create Test User

// TODO: Set Test User Password


// TODO: Validate login

$TestUser = \User\Model\UserRow::fetchByUsername('testuser');
assert($TestUser !== null);

chdir('integration/finix/test');
require ('test.php');
chdir($cwd0);


chdir('integration/element/test');
require ('test.php');
chdir($cwd0);

echo "\nAll Tests successful";
