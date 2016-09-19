<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/27/2016
 * Time: 10:47 PM
 */

namespace Integration\Finix\Test;

use Integration\Model\IntegrationRow;
use Merchant\Test\TestMerchantRow;

echo "Testing ... ", __FILE__, PHP_EOL;

// Go to root directory
$cwd = getcwd();
chdir('../../..');

// Enable class autoloader
spl_autoload_extensions('.class.php');
spl_autoload_register();


// Test Data
$Merchant = new TestMerchantRow();
$FinixAPI = IntegrationRow::fetchByUID('t4e82235-9756-4c61-abf2-be7f317f57fb'); // Finix.io Staging
//$Integration = new TestFinixIntegrationRow();


// Test Data!

$MerchantIdentity = $FinixAPI->getMerchantIdentity($Merchant);
if(!$MerchantIdentity->isProvisioned())
    $MerchantIdentity->provisionRemote();
//$ResponseData = $MerchantIdentity->getParsedResponseData();
//assert($MerchantIdentity->getRemoteID());
//assert($MerchantIdentity->getCreateDate());
// Done

echo "\nFinix Integration Test successful";
chdir($cwd);
