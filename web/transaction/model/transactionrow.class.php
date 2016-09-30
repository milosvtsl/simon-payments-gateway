<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/28/2016
 * Time: 1:32 PM
 */
namespace Transaction\Model;

use Config\DBConfig;
use Integration\Model\AbstractIntegration;
use Integration\Model\AbstractMerchantIdentity;
use Integration\Model\Ex\IntegrationException;
use Integration\Request\Model\IntegrationRequestRow;
use Order\Model\OrderRow;

class TransactionRow
{
    const _CLASS = __CLASS__;
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
        $stmt->setFetchMode(\PDO::FETCH_CLASS, self::_CLASS);
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
        $stmt->setFetchMode(\PDO::FETCH_CLASS, self::_CLASS);
        $stmt->execute(array($uid));
        return $stmt->fetch();
    }


    /**
     * @param AbstractIntegration $Integration
     * @return IntegrationRequestRow|null
     */
    public function fetchAPIRequest(AbstractIntegration $Integration) {
        return IntegrationRequestRow::fetchByType(
            IntegrationRequestRow::ENUM_TYPE_TRANSACTION,
            $this->getID(),
            $Integration->getIntegrationRow()
        );
    }


    /**
     * @param AbstractMerchantIdentity $MerchantIdentity
     * @param OrderRow $OrderRow
     * @param array $post
     * @return OrderRow
     * @throws IntegrationException
     */
    public static function createTransactionFromPost(AbstractMerchantIdentity $MerchantIdentity, OrderRow $OrderRow, Array $post) {
        $TransactionRow = new TransactionRow();
        $TransactionRow->uid = uniqid();
        $TransactionRow->order_item_id = $OrderRow->getID();

//        $OrderRow->date = ;
        if (!($TransactionRow->amount = $post['amount']))                         throw new IntegrationException("Invalid Amount");
//        $TransactionRow->entry_method = $post['entry_mode'];

        $TransactionRow->username = $post['username'];
//        $TransactionRow->version;
        $TransactionRow->action = $action;
        $TransactionRow->capture_to;
        $TransactionRow->auth_code_or_batch_id;

        $TransactionRow->date = date('Y-m-d G:i:s');
        $TransactionRow->entry_method = $post['entry_method'];
        $TransactionRow->is_reviewed = 0;
        $TransactionRow->return_type = 'Both';
//        $TransactionRow->returned_amount = 0;
//        $TransactionRow->reviewed_by;
//        $TransactionRow->reviewed_date_time;
        $TransactionRow->service_fee;
        $TransactionRow->status_code;
        $TransactionRow->status_message;
        $TransactionRow->transaction_id;
        $TransactionRow->batch_item_id;
        $TransactionRow->order_item_id;

        if ($TransactionRow->payee_reciept_email && !filter_var($TransactionRow->payee_reciept_email, FILTER_VALIDATE_EMAIL))
            throw new IntegrationException("Invalid Email");

        return $TransactionRow;
    }



}

