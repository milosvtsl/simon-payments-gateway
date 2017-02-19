<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/27/2016
 * Time: 10:47 PM
 */
use Order\Model\OrderRow;
use Order\Model\TransactionRow;
use System\Config\DBConfig;

if(!isset($argv))
    die("Console Only");

chdir(__DIR__ . '/web');

$cwd0 = getcwd().'';
// Enable class autoloader
spl_autoload_extensions('.class.php');
spl_autoload_register();

// Register Exception Handler
//\System\Exception\ExceptionHandler::register();

// try log in
$SessionManager = new \User\Session\SessionManager();
$SessionUser = $SessionManager->getSessionUser();

$limit = 1000000000;

// Query Statistics
$DB = DBConfig::getInstance();

foreach(array('court' => 'courtpay', 'utility_live' => 'utilitypay') as $old_schema => $schema) {
    echo "\nMigrating {$old_schema} ... ", __FILE__, PHP_EOL;

    $params = array();
    $sql = "SELECT * FROM {$old_schema}.merchants";
    $StatsQuery = $DB->prepare($sql);
    $StatsQuery->execute($params);

    while ($M = $StatsQuery->fetch(PDO::FETCH_ASSOC)) {
        insertMerchant($M, $schema);
    }

    $params = array();
    $sql = "SELECT * FROM {$old_schema}.orders ORDER BY ID DESC LIMIT {$limit}";
    $StatsQuery = $DB->prepare($sql);
    $StatsQuery->execute($params);

    while ($O = $StatsQuery->fetch(PDO::FETCH_ASSOC)) try {
        insertOrder($O, $schema);
    } catch (Exception $ex) { echo "\n", $ex->getMessage(); }

    $params = array();
    $sql = "SELECT * FROM {$old_schema}.transactions ORDER BY ID DESC LIMIT {$limit}";
    $StatsQuery = $DB->prepare($sql);
    $StatsQuery->execute($params);

    while ($T = $StatsQuery->fetch(PDO::FETCH_ASSOC)) try {
        insertTransaction($T, $schema);
    } catch (Exception $ex) { echo "\n", $ex->getMessage(); }

}
echo "\nMigration successful";









function insertMerchant(Array $M, $schema) {

    echo "\nMigrating ", $M['name_full'], '...';

    $params = array(
        ':id' => $M['id'],
        ':short_name' => $M['name_short'],
        ':name' => $M['name_full'],
        ':telephone' => $M['phone_num'],
        ':main_email_id' => $M['email'],
        ':state_id' => $M['state'],
//        'name' => $M['id_gateway'],
//        'name' => $M['gateway_token'],
//        'name' => $M['conv_fee_acct'],

        ':branch' => $M['branch'],
        ':merchant_description' => $M['type'],
        ':default_contact_label' => $M['contact_lbl'],
        ':default_item_label' => $M['item_lbl'],
//        'timepayment' => 'FALSE',
//        'status' => '0'
    );

    $SQL = '';
    foreach($params as $key=>$value)
        $SQL .= ($SQL ? ',' : '') . "\n\t`" . substr($key, 1) . "` = " . $key;

    $SQL = "INSERT IGNORE INTO {$schema}.merchant\nSET " . $SQL;

    $DB = DBConfig::getInstance();
    $stmt = $DB->prepare($SQL);
    $ret = $stmt->execute($params);

    echo $DB->lastInsertId() ?: "Skipped";
}

function insertOrder(Array $O, $schema) {

    echo "\nInserting Order ", $O['id'], '...';

    $params = array(
        ':id' => $O['id'],
        ':uid' => OrderRow::generateGUID(),
        ':merchant_id' => $O['id_merchant'],
        ':integration_id' => 99,
        ':amount' => -1,
        ':date' => '0000/00/00 00:00:00',
        ':entry_mode' => 'None',
        ':customer_first_name' => $O['first_name'],
        ':customer_last_name' => $O['last_name'],
        ':payee_phone_number' => $O['phone'],
        ':payee_reciept_email' => $O['email'],
        ':payee_address' => $O['address_1'],
        ':payee_address2' => $O['address_2'],
        ':payee_city' => $O['city'],
        ':payee_state' => $O['state'],
    );
    $SQL = '';
    foreach($params as $key=>$value)
        $SQL .= ($SQL ? ',' : '') . "\n\t`" . substr($key, 1) . "` = " . $key;

    $SQL = "REPLACE INTO {$schema}.order_item\nSET " . $SQL;

    $DB = DBConfig::getInstance();
    $stmt = $DB->prepare($SQL);
    $ret = $stmt->execute($params);

    echo $DB->lastInsertId() ?: "Skipped";
}

function insertTransaction(Array $T, $schema) {

    echo "\nInserting Transaction ", $T['id'], '...';

    $params = array(
        ':id' => $T['id'],
        ':uid' => TransactionRow::generateGUID(),
        ':order_item_id' => $T['id_order'],
        ':amount' => $T['amount'],
        ':action' => $T['status'],
        ':transaction_id' => $T['id'],
        ':status_code' => '00',
        ':status_message' => '*',
        ':type' => $T['pay_type'] == '1' ? 'convenience-fee' : '',
        ':date' => date('Y-m-d H:i:s', $T['datetime']),
    );

    $SQL = '';
    foreach($params as $key=>$value)
        $SQL .= ($SQL ? ',' : '') . "\n\t`" . substr($key, 1) . "` = " . $key;

    $SQL = "REPLACE INTO {$schema}.transaction\nSET " . $SQL;

    $DB = DBConfig::getInstance();
    $stmt = $DB->prepare($SQL);
    $ret = $stmt->execute($params);

    echo $DB->lastInsertId() ?: "Skipped";

    list($card_exp_month, $card_exp_year) = explode('/', $T['credit_card_expiration']?:'/', 2);
    $params = array(
        ':id' => $T['id_order'],
        ':amount' => $T['charge_total'],
        ':card_exp_month' => $card_exp_month,
        ':card_exp_year' => $card_exp_year,
        ':card_number' => $T['credit_card_masked'],
        ':card_type' => OrderRow::getCCType(str_replace('*', '0', $T['credit_card_masked']), false),
    );
    if($T['pay_type'] == '1')
        $params['convenience_fee'] = $T['amount'];
    $SQL = '';
    foreach($params as $key=>$value)
        $SQL .= ($SQL ? ',' : '') . "\n\t`" . substr($key, 1) . "` = " . $key;

    $SQL = "UPDATE {$schema}.order_item SET $SQL WHERE id = " . $T['id'];

    $stmt = $DB->prepare($SQL);
    $ret = $stmt->execute($params);

    echo $ret ? "..cc updated!" : "..failed to update cc";
}