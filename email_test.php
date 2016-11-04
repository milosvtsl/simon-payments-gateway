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

echo "\nTesting Email ... ", __FILE__, PHP_EOL;

// try log in
$SessionManager = new \User\Session\SessionManager();
$SessionUser = $SessionManager->getSessionUser();

$Order = \Order\Model\OrderRow::fetchByID(8603);
$Order->setPayeeEmail('ari@govpaynetwork.com');
$Merchant = \Merchant\Model\MerchantRow::fetchByID($Order->getMerchantID());


$Email = new \Transaction\Mail\ReceiptEmail($Order, $Merchant);
$Email->addCC('ari@govpaynetwork.com', 'Tester');
$Email->addCC('ari@asu.edu', 'Tester');
echo "\nSending Receipt Email...";
if(!$Email->send())
    error_log("Test Receipt Email Failed: " . $Email->ErrorInfo);



$Email = new \Subscription\Mail\CancelEmail($Order, $Merchant);
$Email->addCC('ari@govpaynetwork.com', 'Tester');
$Email->addCC('ari@asu.edu', 'Tester');
echo "\nSending Cancel Email...";
if(!$Email->send())
    error_log("Test Cancel Email Failed: " . $Email->ErrorInfo);

echo "\nEmail Tests successful";
