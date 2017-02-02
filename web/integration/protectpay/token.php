<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 2/1/2017
 * Time: 8:46 PM
 */
namespace Integration\ProtectPay;
use Integration\Model\IntegrationRow;
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
$data = $Integration->requestTempToken($MerchantIdentity, $Name);

// New PayerId created. Store in session until transaction completes
$TempToken = $data['TempToken'];
$PayerId = $data['PayerId'];
$CredentialId = $data['CredentialId'];


$string = 'Some Secret thing I want to encrypt';
$iv = '12345678';
$passphrase = '8chrsLng';

//$encryptedString = encryptString($string, $passphrase, $iv);
// Expect: 7DjnpOXG+FrUaOuc8x6vyrkk3atSiAf425ly5KpG7lOYgwouw2UATw==

$enc = mcrypt_encrypt(MCRYPT_BLOWFISH, $passphrase, $string, MCRYPT_MODE_CBC, $iv);
$encryptedString = base64_encode($enc);



$CID = $CredentialId;
$SettingsCipher = $encryptedString;


echo <<<JSON
{
    "Name": "{$Name}",
    "CID": "{$CID}",
    "SettingsCipher": "{$SettingsCipher}",
}
JSON;
