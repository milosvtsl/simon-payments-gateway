<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/27/2016
 * Time: 10:47 PM
 */

namespace Integration\Element\Test;

use Config\DBConfig;
use Integration\Model\IntegrationRow;
use Merchant\Model\MerchantRow;
use Merchant\Test\TestMerchantRow;
use Order\Model\OrderRow;
use Transaction\Model\TransactionRow;

echo "\nBatch ... ", __FILE__, PHP_EOL;


// Go to root directory
$cwd1 = getcwd();
chdir('../../..');

// Enable class autoloader
spl_autoload_extensions('.class.php');
spl_autoload_register();


$ElementAPI = IntegrationRow::fetchByUID('73caa82c-c423-428b-927b-15a796bbc0c7'); // Element.io Staging


$MerchantQuery = MerchantRow::queryAll();
foreach($MerchantQuery as $Merchant) {
    /** @var MerchantRow $Merchant */
    $MerchantIdentity = $ElementAPI->getMerchantIdentity($Merchant);
    if(!$MerchantIdentity->isProvisioned())
        continue;

    echo "\n\nMerchant: ", $Merchant->getName(), " MID=", $MerchantIdentity->getRemoteID();

    $stats = $MerchantIdentity->performTransactionQuery(array('status' => 'Settled'),
        function(OrderRow $OrderRow, TransactionRow $TransactionRow, $item) {
            echo "\n\tOrder #" . $OrderRow->getID(), ' ', $TransactionRow->getTransactionID(), ' ', $TransactionRow->getAction(), ' => ', $item['TransactionStatus'];
            return true;
        }
    );

    echo "\nTotal Returned: ", $stats['total'];
    echo "\nFound Locally: ", $stats['found'];
    if($stats['not_found'] > 0)
        echo "\n!! Not Found Locally: ", $stats['not_found'], '!!';
    if($stats['updated'] > 0)
       echo "\n!! Transactions Updated: ", $stats['updated'], '!!';
}

//// Don't run long tests on anything but dev
//if(@$_SERVER['COMPUTERNAME'] !== 'KADO')
//    $tests = array();

echo "\nElement Batching finished";
chdir($cwd1);