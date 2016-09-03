<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/28/2016
 * Time: 1:32 PM
 */
namespace Transaction\Model;

use Config\DBConfig;

class TransactionRow
{
    const _CLASS = __CLASS__;

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

    // Table merchant
    protected $merchant_short_name;

    const SQL_SELECT = "
SELECT oi.*, t.*, t.date as transaction_date, oi.date as order_date, m.short_name as merchant_short_name
FROM transaction t
LEFT JOIN order_item oi on t.order_item_id = oi.id
LEFT JOIN merchant m on oi.merchant_id = m.id
";
    const SQL_GROUP_BY = "\nGROUP BY t.id";
    const SQL_ORDER_BY = "\nORDER BY t.id DESC";

    public function getID()                 { return $this->id; }
    public function getUID()                { return $this->uid; }
    public function getAmount()             { return $this->amount; }
    public function getStatus()             { return $this->status; }
    public function getDate()               { return $this->date; }
    public function getTransactionDate()    { return $this->transaction_date; }
    public function getOrderDate()          { return $this->order_date; }
    public function getInvoiceNumber()      { return $this->invoice_number; }
    public function getCustomerID()         { return $this->customer_id; }
    public function getUsername()           { return $this->username; }
    public function getOrderID()            { return $this->order_item_id; }
    public function getBatchID()            { return $this->batch_item_id; }
    public function getTransactionID()      { return $this->transaction_id; }
    public function getHolderFullFullName() { return $this->customer_first_name . ' ' . $this->customer_last_name; }
    public function getMerchantID()         { return $this->merchant_id; }
    public function getMerchantShortName()  { return $this->merchant_short_name; }

    // Static

    /**
     * @param $id
     * @return TransactionRow
     */
    public static function fetchByID($id) {
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare(static::SQL_SELECT . "WHERE t.id = ?");
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $stmt->setFetchMode(\PDO::FETCH_CLASS, 'Transaction\Model\TransactionRow');
        $stmt->execute(array($id));
        return $stmt->fetch();
    }

    /**
     * @param $uid
     * @return TransactionRow
     */
    public static function fetchByUID($uid) {
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare(static::SQL_SELECT . "WHERE t.uid = ?");
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $stmt->setFetchMode(\PDO::FETCH_CLASS, 'Transaction\Model\TransactionRow');
        $stmt->execute(array($uid));
        return $stmt->fetch();
    }


}

