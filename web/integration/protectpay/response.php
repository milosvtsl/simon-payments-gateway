<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 2/1/2017
 * Time: 8:46 PM
 */
namespace Integration\ProtectPay;
use Integration\Model\Ex\IntegrationException;

// Go to root directory
chdir('../..');
define("BASE_HREF", '../../'); // Set relative path

// Enable class autoloader for this page instance
spl_autoload_extensions('.class.php');
spl_autoload_register();

// Register Exception Handler
\System\Exception\ExceptionHandler::register();

// Start or resume the session
session_start();

$SessionManager = new \User\Session\SessionManager();
$SessionUser = $SessionManager->getSessionUser();
if(!$SessionManager->isLoggedIn()) {
    header('Location: ' . BASE_HREF . 'login.php?message=session has ended');
    die();
}


$CID = $_POST['CID'];

$spi_response = $_POST['spi_response'];
$DOM = new \DOMDocument();
$DOM->loadHTML($spi_response);

$Elm = $DOM->getElementById("ResponseCipher");
if(!$Elm)
    throw new IntegrationException("Could not find ResponseCipher input field");

$ResponseCipher = $Elm->getAttribute('value');
if(!$ResponseCipher)
    throw new IntegrationException("Could not find ResponseCipher input value");


//$ResponseCipher = $_POST['ResponseCipher'];

error_log("Response Cipher: " . $spi_response);

try {
    // Process $ResponseCipher
    $OrderRow = ProtectPayIntegration::processResponseCipher($CID, $ResponseCipher);

    $SessionManager->setMessage(
        "<div class='info'>Success: " . $OrderRow->getStatus() . "</div>"
    );
    header('Location: ' . BASE_HREF . 'order/receipt.php?uid=' . $OrderRow->getUID());

} catch (\Exception $ex) {
    $SessionManager->setMessage(
        "<div class='error'>Error: " . $ex->getMessage() . "</div>"
    );
    header('Location: ' . BASE_HREF . 'order/charge.php');
}