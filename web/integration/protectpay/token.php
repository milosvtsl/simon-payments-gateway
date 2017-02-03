<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 2/1/2017
 * Time: 8:46 PM
 */
namespace Integration\ProtectPay;
use Integration\Model\IntegrationRow;
use Merchant\Model\MerchantFormRow;
use Merchant\Model\MerchantRow;
use User\Session\SessionManager;

// Go to root directory
chdir('../..');

// Enable class autoloader
spl_autoload_extensions('.class.php');
spl_autoload_register();

$SessionManager = new SessionManager();
$SessionUser = $SessionManager->getSessionUser();

if(!$SessionManager->isLoggedIn()) {
    header('Location: /login.php?message=session has ended');
    die("Session has ended");
}

$form_uid = $_POST['form_uid'];
$OrderForm = MerchantFormRow::fetchByUID($form_uid);

$merchant_uid = $_POST['merchant_uid'];
$MerchantRow = MerchantRow::fetchByUID($merchant_uid);

if(!$SessionUser->hasAuthority('ROLE_ADMIN')) {
    if(!in_array($MerchantRow->getID(), $SessionUser->getMerchantList()))
        throw new \Exception("Invalid authorization to merchant: " . $MerchantRow->getUID());
}


$integration_uid = $_POST['integration_uid'];
$IntegrationRow = IntegrationRow::fetchByUID($integration_uid);

/** @var ProtectPayIntegration $Integration */
$Integration = $IntegrationRow->getIntegration();
if(! $Integration instanceof ProtectPayIntegration)
    throw new \Exception("Not a protectpay integration: " . $integration_uid);

/** @var ProtectPayMerchantIdentity $MerchantIdentity */
$MerchantIdentity = $Integration->getMerchantIdentity($MerchantRow, $IntegrationRow);


$Name = $_POST['payee_full_name'];
$PayerID = null;
$data = $Integration->requestTempToken($MerchantIdentity, $OrderForm, $_POST);

$CID = $data['CID'];
$SettingsCipher = $data['SettingsCipher'];


echo <<<JSON
{
    "Name": "{$Name}",
    "CID": "{$CID}",
    "SettingsCipher": "{$SettingsCipher}",
}
JSON;
