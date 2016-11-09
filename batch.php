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
\System\Exception\ExceptionHandler::register();

echo "\nBatch Updating Transactions ... ", __FILE__, PHP_EOL;

// try log in
$SessionManager = new \User\Session\SessionManager();
$SessionUser = $SessionManager->getSessionUser();

if(@chdir('integration/finix/batch')) {
    require('batch.php');
    chdir($cwd0);
}


if(chdir('integration/element/batch')) {
    require('batch.php');
    chdir($cwd0);
}

echo "\nAll Batches successful";
