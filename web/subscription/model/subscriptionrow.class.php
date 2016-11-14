<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/28/2016
 * Time: 1:32 PM
 */
namespace Subscription\Model;

use System\Config\DBConfig;
use Integration\Model\AbstractMerchantIdentity;
use Integration\Model\Ex\IntegrationException;
use Order\Model\OrderRow;

class SubscriptionRow
{
    const _CLASS = __CLASS__;
    const TABLE = 'subscription';

    const ENUM_STATUS_ACTIVE        = 'Active';
    const ENUM_STATUS_INACTIVE      = 'InActive';


    const SORT_BY_ID                = 's.id';
    const SORT_BY_DATE              = 's.date';
    const SORT_BY_ORDER_ITEM        = 's.order_item_id';

    const SORT_BY_BATCH_ITEM        = 's.batch_item_id';
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

    // Table subscription
    protected $id;
    protected $order_item_id;
    protected $uid;
    protected $status;
    protected $status_message;
    protected $recur_amount;
    protected $recur_count;
    protected $recur_next_date;
    protected $recur_frequency;
    protected $recur_cancel_date;

    // Table order_item
    protected $order_invoice_number;
    protected $order_customer_id;
    protected $order_payee_receipt_email;
    protected $order_amount;
    protected $order_status;
    protected $customer_first_name;
    protected $customer_last_name;

    // Table merchant
    protected $merchant_short_name;
    protected $merchant_id;

    // Table Integration
    protected $integration_name;
    protected $integration_id;

    const SQL_SELECT = "
SELECT s.*,
oi.date as order_date,
oi.status as order_status,
oi.customer_first_name as customer_first_name,
oi.customer_last_name as customer_last_name,
oi.merchant_id as merchant_id,
oi.amount as order_amount,
oi.invoice_number as order_invoice_number,
oi.customer_id as order_customer_id,
oi.payee_reciept_email as order_payee_receipt_email,
m.short_name as merchant_short_name,
i.id integration_id,
i.name integration_name
FROM subscription s
LEFT JOIN order_item oi on s.order_item_id = oi.id
LEFT JOIN merchant m on oi.merchant_id = m.id
LEFT JOIN integration i on oi.integration_id = i.id
";
    const SQL_GROUP_BY = ""; // "\nGROUP BY s.id";
    const SQL_ORDER_BY = "\nORDER BY s.id DESC";

    public function getID()                 { return $this->id; }
    public function getOrderID()            { return $this->order_item_id; }
    public function getUID()                { return $this->uid; }
    public function getStatus()             { return $this->status; }
    public function getStatusMessage()      { return $this->status_message; }

    public function getRecurAmount()        { return $this->recur_amount; }
    public function getRecurCount()         { return $this->recur_count; }
    public function getRecurFrequency()     { return $this->recur_frequency; }
    public function getRecurNextDate()      { return $this->recur_next_date; }
    public function getRecurCancelDate()    { return $this->recur_cancel_date; }

    public function getInvoiceNumber()      { return $this->order_invoice_number; }
    public function getCustomerID()         { return $this->order_customer_id; }
    public function getPayeeEmail()         { return $this->order_payee_receipt_email; }
    public function getOrderAmount()        { return $this->order_amount; }
    public function getOrderStatus()        { return $this->order_status; }
    public function getMerchantShortName()  { return $this->merchant_short_name; }
    public function getMerchantID()         { return $this->merchant_id; }
    public function getIntegrationID()      { return $this->integration_id; }
    public function getIntegrationName()    { return $this->integration_name; }

    public function getCustomerFullName()     { return $this->customer_first_name . ' ' . $this->customer_last_name; }
    public function setStatus($status, $message) {
        $this->status = $status;
        $this->status_message = $message;
    }

    /**
     * Cancel an active subscription
     * @param $message
     */
    public function cancel($message) {
        if($this->status !== self::ENUM_STATUS_ACTIVE)
            throw new \InvalidArgumentException("Cancel failed: Subscription is not active.");

        $this->status = self::ENUM_STATUS_INACTIVE;
        $this->status_message = $message;

        $values = array(
            ':id' => $this->id,
            ':status' => $this->status,
            ':status_message' => $this->status_message,
        );

        $SQL = '';
        foreach($values as $key=>$value)
            $SQL .= ($SQL ? ',' : '') . "\n\t`" . substr($key, 1) . "` = " . $key;
        $SQL = "UPDATE subscription\nSET recur_cancel_date = NOW(), "
            . $SQL
            . "\nWHERE id = :id LIMIT 1";

        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare($SQL);
        $ret = $stmt->execute($values);
        if(!$ret)
            throw new \PDOException("Failed to insert new row");
    }

    // Static

    public static function delete(SubscriptionRow $SubscriptionRow) {
        $SQL = "DELETE FROM subscription WHERE id = ?";
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare($SQL);
        $ret = $stmt->execute(array($SubscriptionRow->getID()));
        if(!$ret)
            throw new \PDOException("Failed to delete row");
    }

    public static function insert(SubscriptionRow $SubscriptionRow) {
        if(!$SubscriptionRow->uid)
            throw new \InvalidArgumentException("Invalid UID");
        if(!$SubscriptionRow->order_item_id)
            throw new \InvalidArgumentException("Invalid Order Item ID");
        $values = array(
            ':uid' => $SubscriptionRow->uid,
            ':order_item_id' => $SubscriptionRow->order_item_id,
            ':status' => $SubscriptionRow->status,
            ':status_message' => $SubscriptionRow->status_message,

            ':recur_amount' => $SubscriptionRow->recur_amount,
            ':recur_count' => $SubscriptionRow->recur_count,
            ':recur_next_date' => $SubscriptionRow->recur_next_date,
            ':recur_frequency' => $SubscriptionRow->recur_frequency,
        );
        if($SubscriptionRow->recur_cancel_date)
            $value[':recur_cancel_date'] = $SubscriptionRow->recur_cancel_date;

        $SQL = '';
        foreach($values as $key=>$value)
            $SQL .= ($SQL ? ',' : '') . "\n\t`" . substr($key, 1) . "` = " . $key;
        $SQL = "INSERT INTO subscription\nSET" . $SQL;

        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare($SQL);
        $ret = $stmt->execute($values);
        if(!$ret || !$DB->lastInsertId())
            throw new \PDOException("Failed to insert new row");

        $SubscriptionRow->id = $DB->lastInsertId();
    }

    /**
     * @param $field
     * @param $value
     * @return SubscriptionRow
     * @throws \Exception
     */
    public static function fetchByField($field, $value) {
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare(static::SQL_SELECT . "WHERE s.{$field} = ?");
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
     * @return SubscriptionRow
     */
    public static function fetchByUID($uid) {
        return static::fetchByField('uid', $uid);
    }

    /**
     * @param $id
     * @return SubscriptionRow
     */
    public static function fetchByID($id) {
        return static::fetchByField('id', $id);
    }

    /**
     * @param $id
     * @return SubscriptionRow
     */
    public static function fetchBySubscriptionID($id) {
        return static::fetchByField('subscription_id', $id);
    }



    /**
     * @param AbstractMerchantIdentity $MerchantIdentity
     * @param OrderRow $OrderRow
     * @param array $post
     * @return SubscriptionRow
     * @throws IntegrationException
     */
    public static function createSubscriptionFromPost(AbstractMerchantIdentity $MerchantIdentity, OrderRow $OrderRow, Array $post) {
        if(empty($post['amount']))
            throw new IntegrationException("Invalid Amount");
        if(!$OrderRow->getID())
            throw new IntegrationException("Order was not entered into database");

        $SubscriptionRow = new SubscriptionRow();
        $SubscriptionRow->uid = strtolower(self::generateGUID());
        $SubscriptionRow->order_item_id = $OrderRow->getID();
        $SubscriptionRow->status = self::ENUM_STATUS_ACTIVE;

        $SubscriptionRow->recur_amount = $post['recur_amount'];
        $SubscriptionRow->recur_count = $post['recur_count'];
        $SubscriptionRow->recur_frequency = $post['recur_frequency'];
        $SubscriptionRow->recur_next_date = $post['recur_next_date'];

        return $SubscriptionRow;
    }


    public static function generateGUID() {
        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }
    public static function generateReferenceNumber() {
        return sprintf('%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535));
    }



}

