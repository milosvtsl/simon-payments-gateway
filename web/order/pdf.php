<?php
use Merchant\Model\MerchantRow;
use Order\Model\OrderRow;

/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/27/2016
 * Time: 10:47 PM
 */

// Go up 1 directory
chdir('..');
define("BASE_HREF", '../'); // Set relative path

// Enable class autoloader for this page instance
spl_autoload_extensions('.php,.class.php');
spl_autoload_register();

// Register Exception Handler
\System\Exception\ExceptionHandler::register();

// Start or resume the session
session_start();

if(!isset($_GET['uid']))
    die("Invalid UID");

$OrderRow = OrderRow::fetchByUID($_GET['uid']);
$Merchant = MerchantRow::fetchByID($OrderRow->getMerchantID());
$PDF = new Order\PDF\ReceiptPDF($OrderRow, $Merchant);

//$PDF->render();

$filename = $Merchant->getShortName() . '-' . date("Y-m-d") . '-' . $OrderRow->getID();
$filename = str_replace(' ', '_', $filename);
$PDF->render('I', $filename);
