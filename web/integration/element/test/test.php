<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/27/2016
 * Time: 10:47 PM
 */

namespace Integration\Element\Test;

use Integration\Model\IntegrationRow;
use Merchant\Model\MerchantFormRow;
use Merchant\Model\MerchantRow;
use Order\Model\OrderRow;
use Order\Model\TransactionRow;
use Payment\Model\PaymentRow;
use User\Model\SystemUser;

if(!isset($argv))
    die("Console Only");

echo "\nTesting ... ", __FILE__, PHP_EOL;

// Go to root directory
$cwd1 = getcwd();
chdir('../../..');

// Enable class autoloader
spl_autoload_extensions('.class.php');
spl_autoload_register();

// Register Exception Handler
//\System\Exception\ExceptionHandler::register();

$SessionUser = new SystemUser();

// Test Data
$Merchant = MerchantRow::fetchByUID('011e1bcb-9c88-4ecc-8a08-07ba5c3e005260'); // Test Merchant #27
$ElementAPITest = IntegrationRow::fetchByUID('t3caa82c-c423-428b-927b-15a796bbc0c7'); // Element.io
$ElementAPI = IntegrationRow::fetchByUID('73caa82c-c423-428b-927b-15a796bbc0c7'); // Element.io
//$Integration = new TestElementIntegrationRow();

// Real API Health Check
$MerchantIdentity = $ElementAPI->getMerchantIdentity($Merchant);

$HealthCheckRequest = $MerchantIdentity->performHealthCheck($SessionUser, array());
echo "\nHealth Check: ", $HealthCheckRequest->getResult();

try {
    $stats = $MerchantIdentity->performTransactionQuery($SessionUser,
        array(
            'status' => 'Settled',
            'reverse' => 'True',
            'date_start' => date('Y-m-d', time() - 24*60*60*1),
            'date_end' => date('Y-m-d', time()),
        ),
        function(OrderRow $OrderRow, TransactionRow $TransactionRow, $item) {
            echo "\n\tOrder #" . $OrderRow->getID(), ' ', $TransactionRow->getTransactionID(), ' ', $OrderRow->getStatus(), ' => ', $item['TransactionStatus'];
            return NULL;
        }
    );
    echo "\nSearch Returned: ", $stats['total'];
} catch (\Exception $ex) {
    echo $ex;
}


// Test Provision
if(!$MerchantIdentity->isProvisioned())
    $MerchantIdentity->provisionRemote();



// Test Data
$data = array(
    'integration_id' => $MerchantIdentity->getIntegrationRow()->getID(),
    'merchant_id' => $MerchantIdentity->getMerchantRow()->getID(),
    'entry_mode' => 'Keyed',
    'card_track' => '%B2223000010007612^TEST CARD/EMV BIN-2^19120000000000000000?;2223000010007612=19120000000000000000?|0600|3EC48BFC31CFE1D3224E2548FFDEA1524C4452032995077D2E37A8D78650BFBD16A775808680B158A460160CB8D97F3A8E903599C36334BDA9803575C91F915E|87FB879BFBAD0DDFBCF9DC6618F85B861EDFF43DF847DC2E03E8738BEC144884AA02FE5573D4DE38||61403000|EAA7FAE200CC593CF3145BD536B142741E13092346E874D6E5A909FBB22050E3CBAAFA92E819849CB39C9A1F36F1D7E833F18A1C161A4B70|B362DF8081616AA|23411017960749A4|9010190B362DF80000B0|4F1B||1000',
    'amount' => '23.05',
    'payee_reciept_email' => 'ari@asu.edu',
    'payee_phone_number' => '6025617789',
    'customer_first_name' => 'Test',
    'customer_last_name' => 'Guy',
    'customer_id' => '1234',
    'invoice_number' => '4321',
    'payee_first_name' => 'EMV BIN-2',
    'payee_last_name' => 'TEST CARD',
    'payee_zipcode' => '90210',
    'payee_address' => '123 s. Street st',
    'payee_address2' => '#1234',
    'card_number' => '2223000010007612',
//    'pin' => '7612', // TODO
    'card_type' => 'MasterCard',
    'card_cvv2' => '532',
    'card_exp_month' => '12',
    'card_exp_year' => '19',

    // Check
    'check_account_name' => 'Test Checker',
    'check_account_bank_name' => 'Test Bank',
    'check_account_number' => 11111111,
    'check_routing_number' => 122187238,
    'check_account_type' => 'Checking',
    'check_type' => 'Personal',
    'check_number' => 123,
);

$tests = array(
    // Keyed Tests
    array('amount' => '2.04', 'entry_mode' => 'keyed', 'void' => true),
//    array('amount' => '2.05', 'entry_mode' => 'keyed', 'reversal' => true),
//    array('amount' => '2.06', 'entry_mode' => 'keyed'),
//    array('amount' => '23.05', 'entry_mode' => 'keyed'),
//    array('amount' => '23.06', 'entry_mode' => 'keyed', 'card_number' => '4003000123456781', 'card_exp_month' => 12, 'card_exp_year' => 19),
//    array('amount' => '3.20', 'entry_mode' => 'keyed'),

//    array('amount' => '3.20', 'entry_mode' => 'keyed', 'return' => true),
//    array('amount' => '3.25', 'entry_mode' => 'keyed', 'return' => true),

//    array('amount' => '5.09', 'entry_mode' => 'keyed', 'void' => true),
//    array('amount' => '6.12', 'entry_mode' => 'keyed', 'reversal' => true),
//    array('amount' => '6.13', 'entry_mode' => 'keyed', 'reversal' => true),


    // Swiped Tests
//    array('amount' => '2.04', 'entry_mode' => 'swipe'),
//    array('amount' => '2.05', 'entry_mode' => 'swipe'),
//    array('amount' => '2.06', 'entry_mode' => 'swipe'),
//    array('amount' => '2.07', 'entry_mode' => 'swipe'),
//    array('amount' => '2.08', 'entry_mode' => 'swipe'),
//    array('amount' => '2.09', 'entry_mode' => 'swipe'),
//    array('amount' => '2.10', 'entry_mode' => 'swipe', 'reversal' => true),
//    array('amount' => '2.11', 'entry_mode' => 'swipe'),
//    array('amount' => '2.12', 'entry_mode' => 'swipe'),
//    array('amount' => '23.05', 'entry_mode' => 'swipe'),
//    array('amount' => '23.06', 'entry_mode' => 'swipe'),
//
//    array('amount' => '3.20', 'entry_mode' => 'swipe', 'return' => true),
//    array('amount' => '3.25', 'entry_mode' => 'swipe', 'return' => true),

    // ACH Tests
//    array('amount' => '2.01', 'entry_mode' => 'Check'),
//    array('amount' => '2.02', 'entry_mode' => 'check'),
//    array('amount' => '2.03', 'entry_mode' => 'check'),
//    array('amount' => '2.04', 'entry_mode' => 'check'),
//    array('amount' => '2.05', 'entry_mode' => 'check'),
//    array('amount' => '2.06', 'entry_mode' => 'check'),
//    array('amount' => '2.07', 'entry_mode' => 'check'),
//    array('amount' => '2.09', 'entry_mode' => 'check'),
//    array('amount' => '2.10', 'entry_mode' => 'check'),
//    array('amount' => '2.11', 'entry_mode' => 'check'),
//    array('amount' => '2.12', 'entry_mode' => 'check'),
//    array('amount' => '2.13', 'entry_mode' => 'check'),

//    array('amount' => '33.39', 'entry_mode' => 'check', 'return' => true),
    array('amount' => '2.31', 'entry_mode' => 'Check', 'void' => true),
);

// Don't run long tests on anything but dev
if(!in_array(@$_SERVER['COMPUTERNAME'], array('NOBISERV', 'KADO')))
    $tests = array();

$batch_id = null;

$OrderForm = MerchantFormRow::fetchGlobalForm();

foreach($tests as $testData) {
    $PaymentInfo = PaymentRow::createPaymentFromPost($testData+$data);
    $Order = $MerchantIdentity->createNewOrder($PaymentInfo, $OrderForm, $testData+$data);

    // Create transaction
    $Transaction = $MerchantIdentity->submitNewTransaction($Order, $SessionUser, $testData+$data);
    echo "\n$" . $Transaction->getAmount(), ' ' . $Transaction->getStatusCode(), ' ' . $Transaction->getAction(), ' #' . $Transaction->getTransactionID();

    // Void transaction
    if(!empty($testData['void'])) {
        $VoidTransaction = $MerchantIdentity->voidTransaction($Order, $SessionUser, array());
        echo "\nVoided: " . $VoidTransaction->getStatusCode(), ' ' . $VoidTransaction->getAction(), ' #' . $VoidTransaction->getTransactionID();
    }

    // Return transaction
    if(!empty($testData['return'])) {
        $ReturnTransaction = $MerchantIdentity->returnTransaction($Order, $SessionUser, array());
        echo "\nReturn: " . $ReturnTransaction->getStatusCode(), ' ' . $ReturnTransaction->getAction(), ' #' . $ReturnTransaction->getTransactionID();
    }

    // Reverse transaction
    if(!empty($testData['reversal'])) {
        $ReverseTransaction = $MerchantIdentity->reverseTransaction($Order, $SessionUser, array());
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
