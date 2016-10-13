<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/27/2016
 * Time: 10:47 PM
 */

namespace Integration\Element\Test;

use Integration\Model\IntegrationRow;
use Merchant\Model\MerchantRow;
use Merchant\Test\TestMerchantRow;
use Order\Model\OrderRow;
use Transaction\Model\TransactionRow;

echo "\nTesting ... ", __FILE__, PHP_EOL;

// Go to root directory
$cwd1 = getcwd();
chdir('../../..');

// Enable class autoloader
spl_autoload_extensions('.class.php');
spl_autoload_register();


// Test Data
$Merchant = MerchantRow::fetchByUID('011e1bcb-9c88-4ecc-8a08-07ba5c3e005260'); // Test Merchant #27
$ElementAPI = IntegrationRow::fetchByUID('t3caa82c-c423-428b-927b-15a796bbc0c7'); // Element.io Staging
//$Integration = new TestElementIntegrationRow();

$MerchantIdentity = $ElementAPI->getMerchantIdentity($Merchant);
if(!$MerchantIdentity->isProvisioned())
    $MerchantIdentity->provisionRemote();

$HealthCheckRequest = $MerchantIdentity->performHealthCheck(array());
echo "\nHealth Check: ", $HealthCheckRequest->isRequestSuccessful() ? "Success" : "Fail";

// Test Data
$data = array(
    'integration_id' => $MerchantIdentity->getIntegrationRow()->getID(),
    'merchant_id' => $MerchantIdentity->getMerchantRow()->getID(),
    'entry_mode' => 'keyed',
    'card_track' => '%B2223000010007612^TEST CARD/EMV BIN-2^19120000000000000000?;2223000010007612=19120000000000000000?|0600|3EC48BFC31CFE1D3224E2548FFDEA1524C4452032995077D2E37A8D78650BFBD16A775808680B158A460160CB8D97F3A8E903599C36334BDA9803575C91F915E|87FB879BFBAD0DDFBCF9DC6618F85B861EDFF43DF847DC2E03E8738BEC144884AA02FE5573D4DE38||61403000|EAA7FAE200CC593CF3145BD536B142741E13092346E874D6E5A909FBB22050E3CBAAFA92E819849CB39C9A1F36F1D7E833F18A1C161A4B70|B362DF8081616AA|23411017960749A4|9010190B362DF80000B0|4F1B||1000',
    'amount' => '23.05',
    'payee_reciept_email' => 'ari@asu.edu',
    'payee_phone_number' => '6025617789',
    'customer_id' => '1234',
    'invoice_number' => '4321',
    'payee_first_name' => 'EMV BIN-2',
    'payee_last_name' => 'TEST CARD',
    'payee_zipcode' => '',
    'card_number' => '2223000010007612',
//    'pin' => '7612', // TODO
    'card_type' => 'MasterCard',
    'card_cvv2' => '532',
    'card_exp_month' => '12',
    'card_exp_year' => '19',
);

$tests = array(
    // Keyed Tests
    array('amount' => '2.04', 'entry_mode' => 'keyed', 'void' => true),
    array('amount' => '2.05', 'entry_mode' => 'keyed', 'reverse' => true),
    array('amount' => '2.06', 'entry_mode' => 'keyed', 'return' => true),
    array('amount' => '23.05', 'entry_mode' => 'keyed'),
    array('amount' => '23.06', 'entry_mode' => 'keyed', 'card_number' => '4003000123456781', 'card_exp_month' => 12, 'card_exp_year' => 19),
    array('amount' => '3.20', 'entry_mode' => 'keyed'),

    // Swiped Tests
    array('amount' => '2.04', 'entry_mode' => 'swipe'),
    array('amount' => '2.05', 'entry_mode' => 'swipe'),
    array('amount' => '2.06', 'entry_mode' => 'swipe'),
    array('amount' => '2.07', 'entry_mode' => 'swipe'),
    array('amount' => '2.08', 'entry_mode' => 'swipe'),
    array('amount' => '2.09', 'entry_mode' => 'swipe'),
    array('amount' => '2.10', 'entry_mode' => 'swipe'),
    array('amount' => '2.11', 'entry_mode' => 'swipe'),
    array('amount' => '2.12', 'entry_mode' => 'swipe'),
    array('amount' => '23.05', 'entry_mode' => 'swipe'),
    array('amount' => '23.06', 'entry_mode' => 'swipe'),

    array('amount' => '3.20', 'entry_mode' => 'swipe'),
    array('amount' => '3.25', 'entry_mode' => 'swipe'),

);

// Don't run long tests on anything but dev
if(@$_SERVER['COMPUTERNAME'] !== 'KADO')
    $tests = array();

foreach($tests as $testData) {
    $Order = $MerchantIdentity->createOrResumeOrder($testData+$data);

    // Create transaction
    $Transaction = $MerchantIdentity->submitNewTransaction($Order, $testData+$data);
    echo "\n$" . $Transaction->getAmount(), ' ' . $Transaction->getStatusCode(), ' ' . $Transaction->getAction(), ' #' . $Transaction->getTransactionID();

    // Void transaction
    if(!empty($testData['void'])) {
        $VoidTransaction = $MerchantIdentity->voidTransaction($Order, array());
        echo "\nVoid: " . $VoidTransaction->getStatusCode(), ' ' . $VoidTransaction->getAction(), ' #' . $VoidTransaction->getTransactionID();
    }

    // Return transaction
    if(!empty($testData['return'])) {
        $ReturnTransaction = $MerchantIdentity->returnTransaction($Order, array());
        echo "\nReturn: " . $ReturnTransaction->getStatusCode(), ' ' . $ReturnTransaction->getAction(), ' #' . $ReturnTransaction->getTransactionID();
    }

    // Reverse transaction
    if(!empty($testData['reverse'])) {
        $ReverseTransaction = $MerchantIdentity->reverseTransaction($Order, array());
        echo "\nReverse: " . $ReverseTransaction->getStatusCode(), ' ' . $ReverseTransaction->getAction(), ' #' . $ReverseTransaction->getTransactionID();
    }


    // Delete tests
//    TransactionRow::delete($ReturnTransaction);
//    TransactionRow::delete($VoidTransaction);
//    TransactionRow::delete($Transaction);
//    OrderRow::delete($Order);
}

echo "\nElement Integration Test finished";
chdir($cwd1);
