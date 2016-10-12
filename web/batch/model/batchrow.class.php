<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/28/2016
 * Time: 1:32 PM
 */
namespace Batch\Model;

use Config\DBConfig;

class BatchRow
{
    const _CLASS = __CLASS__;

    const SORT_BY_ID                = 'b.id';
    const SORT_BY_BATCH_ID          = 'b.batch_id';
    const SORT_BY_DATE              = 'b.date';
    const SORT_BY_BATCH_STATUS      = 'b.batch_status';
    const SORT_BY_MERCHANT_ID       = 'b.merchant_id';

    public static $SORT_FIELDS = array(
        self::SORT_BY_ID,
        self::SORT_BY_BATCH_ID,
        self::SORT_BY_DATE,
        self::SORT_BY_BATCH_STATUS,
        self::SORT_BY_MERCHANT_ID,
    );

    // Table batch
    protected $id;
    protected $uid;
    protected $version;
    protected $batch_id;
    protected $batch_status;
    protected $date;
    protected $merchant_id;

    // Table merchant
    protected $merchant_short_name;

    // Table order
    protected $order_count;
    protected $order_amount;
    protected $order_fees;

    const SQL_SELECT = "
SELECT b.*, m.short_name as merchant_short_name,
    (SELECT count(*) FROM batch_orderitems boi WHERE b.id = boi.id_batch) order_count,
    (SELECT sum(oi.amount) FROM batch_orderitems boi, order_item oi WHERE boi.id_orderitem = oi.id AND b.id = boi.id_batch) order_amount,
    (SELECT sum(t.service_fee) FROM batch_orderitems boi, order_item oi, transaction t WHERE boi.id_orderitem = oi.id AND b.id = boi.id_batch AND t.order_item_id = oi.id) order_fees
FROM batch b
LEFT JOIN merchant m on b.merchant_id = m.id
";
    const SQL_GROUP_BY = "\nGROUP BY b.id";
    const SQL_ORDER_BY = "\nORDER BY b.id DESC";

    public function getID()                 { return $this->id; }
    public function getUID()                { return $this->uid; }
    public function getBatchID()            { return $this->batch_id; }
    public function getBatchStatus()        { return $this->batch_status; }
    public function getDate()               { return $this->date; }
    public function getMerchantID()         { return $this->merchant_id; }
    public function getMerchantShortName()  { return $this->merchant_short_name; }

    public function getOrderCount()         { return $this->order_count; }
    public function getOrderAmount()        { return $this->order_amount; }
    public function getOrderFees()          { return $this->order_fees; }
    public function getOrderTotal()         { return $this->order_amount + $this->order_fees; }

    // Static

    /**
     * @param $id
     * @return BatchRow
     */
    public static function fetchByID($id) {
        if(!$id)
            throw new \InvalidArgumentException("Invalid Integration ID");
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare(static::SQL_SELECT . "WHERE b.id = ?");
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $stmt->setFetchMode(\PDO::FETCH_CLASS, 'Batch\Model\BatchRow');
        $stmt->execute(array($id));
        return $stmt->fetch();
    }

    /**
     * @param $uid
     * @return BatchRow
     */
    public static function fetchByUID($uid) {
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare(static::SQL_SELECT . "WHERE b.uid = ?");
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $stmt->setFetchMode(\PDO::FETCH_CLASS, 'Batch\Model\BatchRow');
        $stmt->execute(array($uid));
        return $stmt->fetch();
    }



}

