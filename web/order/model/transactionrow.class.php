<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/28/2016
 * Time: 1:32 PM
 */
namespace Order\Model;

use System\Config\DBConfig;
use Integration\Model\AbstractIntegration;
use Integration\Model\AbstractMerchantIdentity;
use Integration\Model\Ex\IntegrationException;
use Integration\Request\Model\IntegrationRequestRow;
use Order\Model\OrderRow;

class TransactionRow
{
    const _CLASS = __CLASS__;
    const TABLE = 'transaction';

    const SORT_BY_ID                = 't.id';
    const SORT_BY_DATE              = 't.date';
    const SORT_BY_ORDER_ITEM        = 't.order_item_id';

    const SORT_BY_BATCH_ITEM        = 't.batch_item_id';
    const SORT_BY_STATUS            = 'oi.status';
    const SORT_BY_MERCHANT_ID       = 'oi.merchant_id';
    const SORT_BY_USERNAME          = 'oi.username';
    const SORT_BY_INVOICE_NUMBER    = 'oi.invoice_number';

    public static $SORT_FIELDS = array(
        self::SORT_BY_ID,
        self::SORT_BY_DATE,
        self::SORT_BY_ORDER_ITEM,
        self::SORT_BY_BATCH_ITEM,

        self::SORT_BY_STATUS,
        self::SORT_BY_MERCHANT_ID,
        self::SORT_BY_USERNAME,
        self::SORT_BY_INVOICE_NUMBER,
    );

    // Table transaction
    protected $id;
    protected $uid;
    protected $version;
    protected $action;
    protected $amount;
    protected $auth_code_or_batch_id;
    protected $capture_to;
    protected $date;
    protected $transaction_date;
    protected $order_date;
    protected $entry_method;
    protected $is_reviewed;
    protected $return_type;
    protected $returned_amount;
    protected $reviewed_by;
    protected $reviewed_date_time;
    protected $service_fee;
    protected $status_code;
    protected $status_message;
    protected $transaction_id;
    protected $batch_item_id;
    protected $order_item_id;

    // Table order_item
    protected $card_exp_month;
    protected $card_exp_year;
    protected $card_number;
    protected $card_type;
    protected $customer_first_name;
    protected $customer_id;
    protected $customer_last_name;
    protected $customermi;
    protected $entry_mode;
    protected $invoice_number;
    protected $payee_first_name;
    protected $payee_last_name;
    protected $payee_phone_number;
    protected $payee_reciept_email;
    protected $payee_zipcode;
    protected $status;
    protected $total_returned_amount;
    protected $total_returned_service_fee;
    protected $username;
    protected $merchant_id;

    protected $order_status;

    // Table merchant
    protected $merchant_short_name;

    // Table Integration
    protected $integration_name;
    protected $integration_id;

    const SQL_SELECT = "
SELECT oi.*, t.*,
t.date as transaction_date,
oi.date as order_date,
oi.status as order_status,
m.short_name as merchant_short_name,
i.id integration_id,
i.name integration_name
FROM transaction t
LEFT JOIN order_item oi on t.order_item_id = oi.id
LEFT JOIN merchant m on oi.merchant_id = m.id
LEFT JOIN integration i on oi.integration_id = i.id
";
    const SQL_GROUP_BY = ""; // "\nGROUP BY t.id";
    const SQL_ORDER_BY = "\nORDER BY t.id DESC";

    public function getID()                 { return $this->id; }
    public function getOrderID()            { return $this->order_item_id; }
    public function getBatchID()            { return $this->batch_item_id; }
    public function getUID()                { return $this->uid; }
    public function getAmount()             { return $this->amount; }
    public function getServiceFee()         { return $this->service_fee; }
    public function getAction()             { return $this->action; }
    public function getStatusCode()         { return $this->status_code; }
    public function getStatusMessage()      { return $this->status_message; }
    public function getDate()               { return $this->date; }
    public function getTransactionDate()    { return $this->transaction_date; }
    public function getOrderDate()          { return $this->order_date; }
    public function getOrderStatus()        { return $this->order_status; }
    public function getInvoiceNumber()      { return $this->invoice_number; }
    public function getCustomerID()         { return $this->customer_id; }
    public function getUsername()           { return $this->username; }
    public function getTransactionID()      { return $this->transaction_id; }
    public function getAuthCodeOrBatchID()  { return $this->auth_code_or_batch_id; }
    public function getMerchantID()         { return $this->merchant_id; }
    public function getMerchantShortName()  { return $this->merchant_short_name; }
    public function getIntegrationID()      { return $this->integration_id; }
    public function getIntegrationName()    { return $this->integration_name; }

    public function getHolderFullName()     { return $this->customer_first_name . ' ' . $this->customer_last_name; }

    public function setAction($action) {
        $this->action = $action;
    }
    public function setStatus($code, $message) {
        $this->status_code = $code;
        $this->status_message = $message;
    }
    public function setAuthCodeOrBatchID($id) {
        $this->auth_code_or_batch_id = $id;
    }
    public function setTransactionID($id) {
        $this->transaction_id = $id;
    }
    public function setTransactionDate($date) {
        $this->transaction_date = $date;
    }

    public function getReferenceNumber() {
        return strtoupper($this->uid);
    }

    /**
     * Create a Void Transaction
     * @return TransactionRow
     */
    public function createVoidTransaction() {
        $VoidTransaction = clone $this;
        $VoidTransaction->id = null;
        $VoidTransaction->uid = strtolower(self::generateGUID());
        $VoidTransaction->date = date('Y-m-d G:i:s');
        $VoidTransaction->is_reviewed = 0;
        return $VoidTransaction;
    }

    /**
     * Create a Void Transaction
     * @return TransactionRow
     */
    public function createReturnTransaction() {
        $ReturnTransaction = clone $this;
        $ReturnTransaction->id = null;
        $ReturnTransaction->uid = strtolower(self::generateGUID());
        $ReturnTransaction->date = date('Y-m-d G:i:s');
        $ReturnTransaction->is_reviewed = 0;
        return $ReturnTransaction;
    }


    /**
     * Create a Settled Transaction
     * @return TransactionRow
     */
    public function createSettledTransaction() {
        $SettledTransaction = clone $this;
        $SettledTransaction->id = null;
        $SettledTransaction->uid = strtolower(self::generateGUID());
        $SettledTransaction->date = date('Y-m-d G:i:s');
        $SettledTransaction->is_reviewed = 0;
        return $SettledTransaction;
    }

    // Static

    public static function delete(TransactionRow $TransactionRow) {
        $SQL = "DELETE FROM transaction WHERE id = ?";
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare($SQL);
        $ret = $stmt->execute(array($TransactionRow->getID()));
        if(!$ret)
            throw new \PDOException("Failed to delete row");
    }

    public static function insert(TransactionRow $TransactionRow) {
        if(!$TransactionRow->uid)
            throw new \InvalidArgumentException("Invalid UID");
        if(!$TransactionRow->order_item_id)
            throw new \InvalidArgumentException("Invalid Order Item ID");
        $values = array(
            ':uid' => $TransactionRow->uid,
            ':version' => $TransactionRow->version,
            ':action' => $TransactionRow->action,
            ':amount' => $TransactionRow->amount,
            ':auth_code_or_batch_id' => $TransactionRow->auth_code_or_batch_id,
            ':capture_to' => $TransactionRow->capture_to ?: 0,
            ':entry_method' => $TransactionRow->entry_method ?: 0,
            ':is_reviewed' => $TransactionRow->is_reviewed ?: 0,
            ':return_type' => $TransactionRow->return_type ?: 0,
            ':returned_amount' => $TransactionRow->returned_amount ?: 0,
//            ':reviewed_by' => $TransactionRow->reviewed_by,
//            ':reviewed_date_time' => $TransactionRow->reviewed_date_time,
            ':service_fee' => $TransactionRow->service_fee ?: 0,
            ':status_code' => $TransactionRow->status_code ?: 0,
            ':status_message' => $TransactionRow->status_message ?: '',
//            ':transaction_date' => $TransactionRow->transaction_date ?: '',
            ':transaction_id' => $TransactionRow->transaction_id,
            ':batch_item_id' => $TransactionRow->batch_item_id,
            ':order_item_id' => $TransactionRow->order_item_id,
        );
        $SQL = "INSERT INTO transaction SET";
        foreach($values as $key=>$value)
            $SQL .= "\n\t`" . substr($key, 1) . "` = " . $key . ',';
        $SQL .= "\n\t`date` = NOW()";
        $TransactionRow->date = date('Y-m-d G:i:s');

        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare($SQL);
        $ret = $stmt->execute($values);
        if(!$ret || !$DB->lastInsertId())
            throw new \PDOException("Failed to insert new row");

        $TransactionRow->id = $DB->lastInsertId();
    }

    /**
     * @param $field
     * @param $value
     * @return TransactionRow
     * @throws \Exception
     */
    public static function fetchByField($field, $value) {
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare(static::SQL_SELECT . "WHERE t.{$field} = ?");
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $stmt->setFetchMode(\PDO::FETCH_CLASS, self::_CLASS);
        $stmt->execute(array($value));
        $Row = $stmt->fetch();
        if(!$Row)
            throw new \InvalidArgumentException("{$field} not found: " . $value);
        return $Row;
    }

    /**
     * @param $uid
     * @return TransactionRow
     */
    public static function fetchByUID($uid) {
        return static::fetchByField('uid', $uid);
    }

    /**
     * @param $id
     * @return TransactionRow
     */
    public static function fetchByID($id) {
        return static::fetchByField('id', $id);
    }

    /**
     * @param $id
     * @return TransactionRow
     */
    public static function fetchByTransactionID($id) {
        return static::fetchByField('transaction_id', $id);
    }



    /**
     * @param AbstractMerchantIdentity $MerchantIdentity
     * @param OrderRow $OrderRow
     * @param array $post
     * @return TransactionRow
     * @throws IntegrationException
     */
    public static function createTransactionFromPost(AbstractMerchantIdentity $MerchantIdentity, OrderRow $OrderRow, Array $post) {
        if(empty($post['amount']))
            throw new IntegrationException("Invalid Amount");

        $TransactionRow = new TransactionRow();
//        $TransactionRow->transaction_id = !empty($post['transaction_id'])
//            ? $post['transaction_id'] : strtoupper(self::generateTransactionID());
        $TransactionRow->uid = strtolower(self::generateGUID());
        $TransactionRow->date = date('Y-m-d G:i:s');

        $TransactionRow->order_item_id = $OrderRow->getID();
//        $TransactionRow->batch_item_id;
        $TransactionRow->amount = $post['amount'];
        $TransactionRow->version = 10;
        $TransactionRow->entry_method = @$post['entry_method'] ?: "Default";
        $TransactionRow->is_reviewed = 0;
        $TransactionRow->return_type = 'Both';
        $TransactionRow->service_fee = $MerchantIdentity->calculateConvenienceFee($OrderRow);

        if(!empty($post['username']))
            $TransactionRow->username = $post['username'];

//        $TransactionRow->action = $action;
//        $TransactionRow->capture_to;
//        $TransactionRow->auth_code_or_batch_id;

//        $TransactionRow->returned_amount = 0;
//        $TransactionRow->reviewed_by;
//        $TransactionRow->reviewed_date_time;
//        $TransactionRow->status_code;
//        $TransactionRow->status_message;
//        $TransactionRow->transaction_id;

        if(!$TransactionRow->order_item_id)
            throw new IntegrationException("Order Item ID was not set");

        return $TransactionRow;
    }


    public static function generateGUID() {
        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }
    public static function generateReferenceNumber() {
        return sprintf('%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535));
    }



}

