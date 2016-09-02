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

    const SQL_SELECT = "
SELECT b.*, m.short_name as merchant_short_name
FROM batch b
LEFT JOIN merchant m on b.merchant_id = m.id
";

    public function getID()                 { return $this->id; }
    public function getUID()                { return $this->uid; }
    public function getBatchID()            { return $this->batch_id; }
    public function getBatchStatus()        { return $this->batch_status; }
    public function getDate()               { return $this->date; }
    public function getMerchantID()         { return $this->merchant_id; }
    public function getMerchantShortName()  { return $this->merchant_short_name; }

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

