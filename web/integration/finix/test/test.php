<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/27/2016
 * Time: 10:47 PM
 */

namespace Integration\Finix\Test;

use Integration\Finix\FinixIdentityRequestParser;
use Integration\Model\IntegrationRow;
use Merchant\Test\TestMerchantRow;

// Go to root directory
$cwd = getcwd();
chdir('../../..');

// Enable class autoloader
spl_autoload_extensions('.class.php');
spl_autoload_register();


// Test Data
$Merchant = new TestMerchantRow();
$Integration = IntegrationRow::fetchByUID('t4e82235-9756-4c61-abf2-be7f317f57fb'); // Finix.io Staging
//$Integration = new TestFinixIntegrationRow();


// Test Data!

$MerchantIdentity = $Integration->createMerchantIdentity($Merchant);
if(!$MerchantIdentity->requestIsSuccessful())
    throw new \Exception("Request was not successful");
//$ResponseData = $MerchantIdentity->getParsedResponseData();
print_r($MerchantIdentity->parseRemoteID());
// Done

echo "\nFinix Integration Test successful";
chdir($cwd);
