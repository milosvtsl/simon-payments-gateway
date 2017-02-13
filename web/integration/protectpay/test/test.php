<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/27/2016
 * Time: 10:47 PM
 */

namespace Integration\ProtectPay\Test;

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

$SessionUser = new SystemUser();

// Test Data
$Merchant = MerchantRow::fetchByUID('011e1bcb-9c88-4ecc-8a08-07ba5c3e005260'); // Test Merchant #27
$ProtectPayAPITest = IntegrationRow::fetchByUID('propay-staging-e50f3219-79b7-4930-800a'); // ProPay.io
//$Integration = new TestProPayIntegrationRow();

//$HealthCheckRequest = $MerchantIdentity->performHealthCheck($SessionUser, array());
//echo "\nHealth Check: ", $HealthCheckRequest->isRequestSuccessful() ? "Success" : "Fail";

//try {
//    $stats = $MerchantIdentity->performTransactionQuery($SessionUser,
//        array(
//            'status' => 'Settled',
//            'reverse' => 'True',
//            'date_start' => date('Y-m-d', time() - 24*60*60*1),
//            'date_end' => date('Y-m-d', time()),
//        ),
//        function(OrderRow $OrderRow, TransactionRow $TransactionRow, $item) {
//            echo "\n\tOrder #" . $OrderRow->getID(), ' ', $TransactionRow->getTransactionID(), ' ', $OrderRow->getStatus(), ' => ', $item['TransactionStatus'];
//            return NULL;
//        }
//    );
//    echo "\nSearch Returned: ", $stats['total'];
//} catch (\Exception $ex) {
//    echo $ex;
//}


// Test API
$MerchantIdentity = $ProtectPayAPITest->getMerchantIdentity($Merchant);
if(!$MerchantIdentity->isProvisioned())
    $MerchantIdentity->provisionRemote();



// Test Data
$data = array(
    'integration_id' => $MerchantIdentity->getIntegrationRow()->getID(),
    'merchant_id' => $MerchantIdentity->getMerchantRow()->getID(),
    'entry_mode' => 'Keyed',
//    'card_track' => '%B4895280002000006^TEST/VANTIV^251200000000000000?;4895280002000006=251200000000000?|0600|373A7D70C6253DA0E49B804D6E370A558AED065DEB86AD66717911DB4CD8157871F7C2FF782D921101B67F10B60E3846476434B040706645|E0B756965DB19E745CEFEE4E717CE7E8BE78E1EF7718B430DD6D6AF10A847F1BA7ED9CB856E27EAF||61401000|B2D67857E4111FE9FE8F0095EA66551C1ABAC9FE979D86A4825C1B648BA552F667DFACD060EACFE121CAF4F296513D80B3DCA46EEEFE6274|B2B4257022315AA|11A81A05E0E39220|9010010B2B4257000011|5C3F||1000',
    'card_track' => '%B5102650005008881^TEST/INTEGRATION^19050000000000000?;5102650005008881=19050000000000000000?|0600|F1D0148B551047ECD0CCC342EAA6230AC9B6006BA9FC1F3B5594BCB15F9C2B0A94B79B1F50D50EA7946EA60A54AE8F79B2D87EAADDAD7205|1DA6921600AFAF6FD9F5BBB07DDC5AB3DB773682D401F2AA978A7F1FCED84E5CD16747F7BE68D1F3||61403000|FB30CBB95CCAF5976B899C97F1152991005E6EF53494434E2DBD92424263CD6FC8FCF92D04C7E4C994D22AA863157925B063945C8D83D46C|B2B4257022315AA|75F24F139CA4AA0C|9010010B2B4257000002|33BB||1000',
    'amount' => '23.05',
    'payee_reciept_email' => 'ari@asu.edu',
    'payee_phone_number' => '6025617789',
    'customer_first_name' => 'Test',
    'customer_last_name' => 'Guy',
    'customer_id' => '1234',
    'invoice_number' => '4321',
    'payee_first_name' => 'Integration',
    'payee_last_name' => 'Test',
    'payee_zipcode' => '90210',
    'payee_address' => '123 s. Street st',
    'payee_address2' => '#1234',
    'card_number' => '5102650005008881',
//    'pin' => '7612', // TODO
    'card_type' => 'MasterCard',
    'card_cvv2' => '890',
    'card_exp_month' => '05',
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
//    array('amount' => '2.04', 'entry_mode' => 'keyed', 'void' => true),

    // Swiped Tests
    array('amount' => '2.04', 'entry_mode' => 'swipe', 'return' => true),

    // ACH Tests
//    array('amount' => '2.31', 'entry_mode' => 'Check', 'void' => true),
);

// Don't run long tests on anything but dev
//if(!in_array(@$_SERVER['COMPUTERNAME'], array('NOBISERV', 'KADO')))
//    $tests = array();

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

}

echo "\nProPay Integration Test finished";
chdir($cwd1);
