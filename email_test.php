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

$Order = \Order\Model\OrderRow::fetchByID(8476);
$Order->setPayeeEmail('ari@govpaynetwork.com');
$Merchant = \Merchant\Model\MerchantRow::fetchByID($Order->getMerchantID());
$EmailReceipt = new \Transaction\Mail\ReceiptEmail($Order, $Merchant);
$EmailReceipt->addCC('ari@govpaynetwork.com', 'Tester');
$EmailReceipt->addCC('ari@asu.edu', 'Tester');
if(!$EmailReceipt->send())
    error_log("Test Email Failed: " . $EmailReceipt->ErrorInfo);
echo "\nEmail Tests successful";