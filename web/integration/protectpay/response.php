<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 2/1/2017
 * Time: 8:46 PM
 */
namespace Integration\ProtectPay;
use Order\View\OrderView;

// Go to root directory
chdir('../..');

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
    header('Location: /login.php?message=session has ended');
    die();
}


$CID = $_POST['CID'];
$ResponseCipher = $_POST['ResponseCipher'];

error_log("Response Cipher: " . print_r($_POST, true));

// Process $ResponseCipher
$OrderRow = ProtectPayIntegration::processResponseCipher($CID, $ResponseCipher);

$ReceiptView = new OrderView($OrderRow->getID());
$SessionManager->setMessage(
    "<div class='info'>Success: " . $OrderRow->getStatus() . "</div>"
);
header('Location: /order/receipt.php?uid=' . $OrderRow->getUID(false));
