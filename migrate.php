<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/27/2016
 * Time: 10:47 PM
 */
use System\Config\DBConfig;

if(!isset($argv))
    die("Console Only");

chdir(__DIR__ . '/web');

$cwd0 = getcwd().'';
// Enable class autoloader
spl_autoload_extensions('.class.php');
spl_autoload_register();

// Register Exception Handler
//\System\Exception\ExceptionHandler::register();

// try log in
$SessionManager = new \User\Session\SessionManager();
$SessionUser = $SessionManager->getSessionUser();

echo "\nMigrating CourtPay ... ", __FILE__, PHP_EOL;


// Query Statistics
$DB = DBConfig::getInstance();

$params = array();
$sql = "SELECT * FROM court.merchants";
$StatsQuery = $DB->prepare($sql);
$StatsQuery->execute($params);

foreach($StatsQuery as $M) {
    echo "\nMigrating " . $M['name_short'];
    print_r($M);
    die();
}

echo "\nMigration successful";
