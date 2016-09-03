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
    protected $order_settled;
    protected $order_authorized;
    protected $order_void;

    const SQL_SELECT = "
SELECT b.*, m.short_name as merchant_short_name,
    (SELECT count(*) FROM batch_orderitems boi WHERE b.id = boi.id_batch) order_count,
    (SELECT sum(oi.amount) FROM batch_orderitems boi, order_item oi WHERE boi.id_orderitem = oi.id AND b.id = boi.id_batch AND oi.status = 'Settled') order_settled,
    (SELECT sum(oi.amount) FROM batch_orderitems boi, order_item oi WHERE boi.id_orderitem = oi.id AND b.id = boi.id_batch AND oi.status = 'Authorized') order_authorized,
    (SELECT sum(oi.amount) FROM batch_orderitems boi, order_item oi WHERE boi.id_orderitem = oi.id AND b.id = boi.id_batch AND oi.status = 'Void') order_void
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
    public function getOrderSettled()       { return $this->order_settled; }
    public function getOrderAuthorized()    { return $this->order_authorized; }
    public function getOrderVoid()          { return $this->order_void; }

    // Static

    /**
     * @param $id
     * @return BatchRow
     */
    public static function fetchByID($id) {
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

