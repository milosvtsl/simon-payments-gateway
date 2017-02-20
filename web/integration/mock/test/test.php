<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/27/2016
 * Time: 10:47 PM
 */

namespace Integration\Mock\Test;

use Merchant\Model\MerchantRow;

echo "\nTesting ... ", __FILE__, PHP_EOL;

// Go to root directory
$cwd1 = getcwd();
chdir('../../..');

// Enable class autoloader
spl_autoload_extensions('.class.php');
spl_autoload_register();

// Register Exception Handler
\System\Exception\ExceptionHandler::register();


// Test Data
$Merchant = MerchantRow::fetchByUID('011e1bcb-9c88-4ecc-8a08-07ba5c3e005260'); // Test Merchant #27
$MockAPI = new TestMockIntegrationRow(); // IntegrationRow::fetchByUID('t4e82235-9756-4c61-abf2-be7f317f57fb'); // Mock.io Staging
//$Integration = new TestMockIntegrationRow();


// Test Data!

$MerchantIdentity = $MockAPI->getMerchantIdentity($Merchant);
if(!$MerchantIdentity->isProvisioned())
    $MerchantIdentity->provisionRemote();
//$ResponseData = $MerchantIdentity->getParsedResponseData();
//assert($MerchantIdentity->getRemoteID());
//assert($MerchantIdentity->getCreateDate());
// Done

echo "\nMock Integration Test successful";
chdir($cwd1);
