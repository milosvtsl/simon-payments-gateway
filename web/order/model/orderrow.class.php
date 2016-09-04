<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/28/2016
 * Time: 1:32 PM
 */
namespace Order\Model;

use Config\DBConfig;

class OrderRow
{
    const _CLASS = __CLASS__;

    // Table order_item
    protected $id;
    protected $uid;
    protected $amount;
    protected $date;

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
SELECT oi.*, m.short_name as merchant_short_name
FROM order_item oi
LEFT JOIN merchant m on oi.merchant_id = m.id
";
    const SQL_GROUP_BY = "\nGROUP BY oi.id";
    const SQL_ORDER_BY = "\nORDER BY oi.id DESC";

    public function getID()                 { return $this->id; }
    public function getUID()                { return $this->uid; }
    public function getAmount()             { return $this->amount; }
    public function getStatus()             { return $this->status; }
    public function getDate()               { return $this->date; }
    public function getInvoiceNumber()      { return $this->invoice_number; }
    public function getCustomerID()         { return $this->customer_id; }
    public function getUsername()           { return $this->username; }
    public function getHolderFullFullName() { return $this->customer_first_name . ' ' . $this->customer_last_name; }
    public function getMerchantID()         { return $this->merchant_id; }
    public function getMerchantShortName()  { return $this->merchant_short_name; }

    // Static

    /**
     * @param $id
     * @return OrderRow
     */
    public static function fetchByID($id) {
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare(static::SQL_SELECT . "WHERE oi.id = ?");
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $stmt->setFetchMode(\PDO::FETCH_CLASS, 'Order\Model\OrderRow');
        $stmt->execute(array($id));
        return $stmt->fetch();
    }

    /**
     * @param $uid
     * @return OrderRow
     */
    public static function fetchByUID($uid) {
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare(static::SQL_SELECT . "WHERE oi.uid = ?");
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $stmt->setFetchMode(\PDO::FETCH_CLASS, 'Order\Model\OrderRow');
        $stmt->execute(array($uid));
        return $stmt->fetch();
    }


}

