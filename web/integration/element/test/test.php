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

// Test Data
$data = array(
    'integration_id' => $MerchantIdentity->getIntegrationRow()->getID(),
    'merchant_id' => $MerchantIdentity->getMerchantRow()->getID(),
    'entry_mode' => 'swipe',
    'card_track' => '%B4335000020003933^ASULIN/ARI^1611000000000000000000000000000?;4335000020003933=16110000000000000000?|0600|DAFC9D7BFBF25A1E3AF8B34ED6231BE47E8D8EAB9AA3119838C10797FD266DE5A3B8514FE7C4C47D06890059906A0CDA699A8123BEA49BBE64B5FA2CD8D28F95|938BC3728A5901AF88B6F18F9D6956A939F684A371A3C690321C76E5C04D724FC5D7FCF3B828E5EF||61403000|8A026F3EF96A65200FD27FA6C1C4B8AABF56EEC51B4F3540387F51A0E61CF9D8B1045D0BA749B107214E7501886E6ABC2F9C07673F9E7665|B362DF8081616AA|DAAA5B3E861E6B05|9010190B362DF8000061|38A7||1000',
    'amount' => '23.05',
    'customer_first_name' => 'TED',
    'customermi' => 'R',
    'customer_last_name' => 'CRUZ',
    'payee_reciept_email' => 'ari@asu.edu',
    'payee_phone_number' => '6025617789',
    'customer_id' => '1234',
    'invoice_number' => '4321',
    'payee_first_name' => 'TED',
    'payee_last_name' => 'CRUZ',
    'payee_zipcode' => '',
    'card_number' => '4335000020003393',
    'card_type' => 'Visa',
    'card_cvv2' => '',
    'card_exp_month' => '11',
    'card_exp_year' => '16',
);

$tests = array(
    array(
        'amount' => '2.04',
    ),
    array(
        'amount' => '2.05',
    ),
    array(
        'amount' => '2.06',
    ),
    array(
        'amount' => '23.05',
    ),
    array(
        'amount' => '23.06',
    ),
);

foreach($tests as $testData) {
    $Order = $MerchantIdentity->createOrResumeOrder($testData+$data);
    $Transaction = $MerchantIdentity->submitNewTransaction($Order, $testData+$data);
    echo "\n$" . $Transaction->getAmount(), ' ' . $Transaction->getStatusCode(), ' #' . $Transaction->getTransactionID();

    // Delete tests
    TransactionRow::delete($Transaction);
    OrderRow::delete($Order);
}

echo "\nElement Integration Test finished";
chdir($cwd1);
