<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/27/2016
 * Time: 10:47 PM
 */

chdir(__DIR__ . '/web');

$cwd0 = getcwd().'';
// Enable class autoloader
spl_autoload_extensions('.class.php');
spl_autoload_register();

// Register Exception Handler
//\System\Exception\ExceptionHandler::register();

echo "\nTesting ... ", __FILE__, PHP_EOL;

// try log in
$SessionManager = new \User\Session\SessionManager();
$SessionUser = $SessionManager->getSessionUser();

// Test Finix Integration
chdir('integration/finix/test');
require ('test.php');
chdir($cwd0);


// Test Element Integration
chdir('integration/element/test');
require ('test.php');
chdir($cwd0);

// Test ProPay/ProtectPay Integration
chdir('integration/propay/test');
require ('test.php');
chdir($cwd0);

echo "\nAll Tests successful";
