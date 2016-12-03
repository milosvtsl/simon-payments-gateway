<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/27/2016
 * Time: 10:47 PM
 */

namespace Integration\Finix\Test;

use Integration\Model\IntegrationRow;
use Merchant\Model\MerchantRow;
use Merchant\Test\TestMerchantRow;

echo "\nTesting ... ", __FILE__, PHP_EOL;

// Go to root directory
$cwd1 = getcwd();
chdir('../../..');

// Enable class autoloader
spl_autoload_extensions('.class.php');
spl_autoload_register();

// Register Exception Handler
//\System\Exception\ExceptionHandler::register();


// Test Data
$Merchant = MerchantRow::fetchByUID('011e1bcb-9c88-4ecc-8a08-07ba5c3e005260'); // Test Merchant #27
$FinixAPI = IntegrationRow::fetchByUID('t4e82235-9756-4c61-abf2-be7f317f57fb'); // Finix.io Staging
//$Integration = new TestFinixIntegrationRow();


// Test Data!

$MerchantIdentit1y = $FinixAPI->getMerchantIdentity($Merchant);
//if(!$MerchantIdentity->isProvisioned())
//    $MerchantIdentity->provisionRemote();
//$ResponseData = $MerchantIdentity->getParsedResponseData();
//assert($MerchantIdentity->getRemoteID());
//assert($MerchantIdentity->getCreateDate());
// Done

echo "\nFinix Integration Test successful";
chdir($cwd1);
