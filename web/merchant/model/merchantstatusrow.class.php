<?php
/**
 * Created by PhpStorm.
 * Merchant: ari
 * Date: 8/28/2016
 * Time: 1:32 PM
 */
namespace Merchant\Model;

use Config\DBConfig;

class MerchantStatusRow
{
    const _CLASS = __CLASS__;
    protected $id;
    protected $uid;
    protected $version;
    protected $name;

    const SQL_SELECT = "
SELECT *
FROM merchant_status ms
";
    const SQL_GROUP_BY = "\nGROUP BY ms.id";
    const SQL_ORDER_BY = "\nORDER BY ms.id DESC";

    public function getID()             { return $this->id; }
    public function getUID()            { return $this->uid; }
    public function getName()           { return $this->name; }
    public function getVersion()        { return $this->version; }


    // Static

    /**
     * @param $id
     * @return MerchantRow
     */
    public static function fetchByID($id) {
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare(static::SQL_SELECT . "WHERE ms.id = ?");
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $stmt->setFetchMode(\PDO::FETCH_CLASS, MerchantStatusRow::_CLASS);
        $stmt->execute(array($id));
        return $stmt->fetch();
    }

    /**
     * @param $uid
     * @return MerchantRow
     */
    public static function fetchByUID($uid) {
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare(static::SQL_SELECT . "WHERE ms.uid = ?");
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $stmt->setFetchMode(\PDO::FETCH_CLASS, self::_CLASS);
        $stmt->execute(array($uid));
        return $stmt->fetch();
    }

    public static function queryAll() {
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare(static::SQL_SELECT);
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $stmt->setFetchMode(\PDO::FETCH_CLASS, self::_CLASS);
        $stmt->execute();
        return $stmt;
    }

}

