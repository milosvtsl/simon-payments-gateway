<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/27/2016
 * Time: 10:47 PM
 */

namespace Integration\Element\Test;

use Merchant\Test\TestMerchantRow;

echo "Testing ... ", __FILE__, PHP_EOL;

// Go to root directory
$cwd1 = getcwd();
chdir('../../..');

// Enable class autoloader
spl_autoload_extensions('.class.php');
spl_autoload_register();


// Test Data
$Merchant = new TestMerchantRow();
$ElementAPI = new TestElementIntegrationRow(); // IntegrationRow::fetchByUID('t4e82235-9756-4c61-abf2-be7f317f57fb'); // Element.io Staging
//$Integration = new TestElementIntegrationRow();


// Test Data!

$MerchantIdentity = $ElementAPI->getMerchantIdentity($Merchant);
if(!$MerchantIdentity->isProvisioned())
    $MerchantIdentity->provisionRemote();
//$ResponseData = $MerchantIdentity->getParsedResponseData();
//assert($MerchantIdentity->getRemoteID());
//assert($MerchantIdentity->getCreateDate());
// Done

echo "\nElement Integration Test successful";
chdir($cwd1);
