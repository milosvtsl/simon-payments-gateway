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
use Order\View\OrderView;
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

$CID = $_POST['CID'];
$ResponseCipher = $_POST['ResponseCipher'];

error_log("Response Cipher: " . print_r($_POST, true));

// Process $ResponseCipher
$OrderRow = ProtectPayIntegration::processResponseCipher($CID, $ResponseCipher);

$ReceiptView = new OrderView($OrderRow->getID());
$ReceiptView->setSessionMessage(
    "<div class='info'>Success: " . $OrderRow->getStatus() . "</div>"
);
header('Location: /order/receipt.php?uid=' . $OrderRow->getUID(false));
