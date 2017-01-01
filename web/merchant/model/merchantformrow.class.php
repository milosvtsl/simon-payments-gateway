<?php
/**
 * Created by PhpStormf.
 * Merchant: ari
 * Date: 8/28/2016
 * Time: 1:32 PM
 */
namespace Merchant\Model;

use System\Config\DBConfig;
use User\Model\UserRow;

class MerchantFormRow
{
    const _CLASS = __CLASS__;
    const TABLE_NAME = 'merchant_form';

    const SORT_BY_ID                = 'mf.id';
    const SORT_BY_NAME              = 'mf.name';

    const SQL_SELECT = "
SELECT mf.*
FROM merchant_form mf
";
    const SQL_GROUP_BY = "\nGROUP BY mf.id";
    const SQL_ORDER_BY = "\nORDER BY mf.created DESC";
    const SQL_WHERE =    "\nWHERE 1";

    public static $SORT_FIELDS = array(
        self::SORT_BY_ID,
        self::SORT_BY_NAME,
    );

    protected $id;
    protected $uid;
    protected $merchant_id;
    protected $title;
    protected $classes;
    protected $fields;
    protected $category;
    protected $constants;
    protected $created;

    // Table merchant
    protected $merchant_name;

    public function __construct(Array $params=array()) {
        foreach($params as $key=>$param)
            $this->$key = $param;
    }


    public function __set($key, $value) {
        throw new \InvalidArgumentException("Property does not exist: " . $key);
    }

    public function getID()             { return $this->id; }
    public function getUID()            { return $this->uid; }
    public function getTitle()          { return $this->title; }
    public function getFormClasses()    { return $this->classes; }
    public function getCategory()       { return $this->category; }

    public function getMerchantID()     { return $this->merchant_id; }
    public function getMerchantName()   { return $this->merchant_name; }

    // Static

    public static function fetchByID($id) {
        return self::fetchByField('id', $id);
    }

    public static function fetchByUID($uid) {
        return self::fetchByField('uid', $uid);
    }

    /**
     * @param null $category
     * @return MerchantFormRow
     * @throws \Exception
     */
    public static function fetchGlobalForm($category=null) {
        $DB = DBConfig::getInstance();
        $sql = static::SQL_SELECT . "\nWHERE mf.merchant_id is NULL";
        $values = array();
        if($category) {
            $sql .= " AND mf.category LIKE ?";
            $values[] = $category;
        }
        $stmt = $DB->prepare($sql . "\norder by id desc LIMIT 1");
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $stmt->setFetchMode(\PDO::FETCH_CLASS, self::_CLASS);
        $stmt->execute($values);
        $Row = $stmt->fetch();
        if(!$Row)
            throw new \InvalidArgumentException("Global Form not found: " . $category ?: "Any Category");
        return $Row;
    }

    /**
     * @param $field
     * @param $value
     * @return MerchantRow
     * @throws \Exception
     */
    public static function fetchByField($field, $value) {
        $DB = DBConfig::getInstance();
        $sql = static::SQL_SELECT . "\nWHERE mf.{$field} = ?";
        $stmt = $DB->prepare($sql);
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $stmt->setFetchMode(\PDO::FETCH_CLASS, self::_CLASS);
        $stmt->execute(array($value));
        $Row = $stmt->fetch();
        if(!$Row)
            throw new \InvalidArgumentException("{$field} not found: " . $value);
        return $Row;
    }

    /**
     * @param $id
     * @return MerchantFormRow[] | \PDOStatement
     * @throws \Exception
     */
    public static function queryByUserID($id) {
        $sql = static::SQL_SELECT
            . "\nLEFT JOIN user_merchants um on mf.merchant_id = um.id_merchant "
            . "\nWHERE um.id_user = ?";
        $DB = DBConfig::getInstance();
        $MerchantFormQuery = $DB->prepare($sql);
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $MerchantFormQuery->setFetchMode(\PDO::FETCH_CLASS, self::_CLASS);
        $MerchantFormQuery->execute(array($id));
        return $MerchantFormQuery;
    }
    
    /**
     * @return MerchantFormRow[] | \PDOStatement
     * @throws \Exception
     */
    public static function queryAll() {
        $sql = static::SQL_SELECT
            . static::SQL_WHERE;
        $DB = DBConfig::getInstance();
        $MerchantFormQuery = $DB->prepare($sql);
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $MerchantFormQuery->setFetchMode(\PDO::FETCH_CLASS, self::_CLASS);
        $MerchantFormQuery->execute(array());
        return $MerchantFormQuery;
    }

    /**
     * @param $post
     * @return MerchantRow
     */
    public static function createNewMerchantForm($post) {
        if(strlen($post['name']) < 5)
            throw new \InvalidArgumentException("Merchant Name must be at least 5 characters");

        if (!filter_var($post['main_email_id'], FILTER_VALIDATE_EMAIL))
            throw new \InvalidArgumentException("Invalid Email");

        $params = array();
        $Merchant = new MerchantFormRow();
        $Merchant->uid = strtolower(self::generateGUID());
        $params[':uid'] = $Merchant->uid;

        $sqlSet = "";
        foreach(self::$UPDATE_FIELDS as $field) {
            if(!empty($post[$field])) {
                $params[':'.$field] = $post[$field];
                $sqlSet .= ($sqlSet ? ",\n" : "\nSET ") . $field . '=:' . $field;
                $Merchant->$field = $post[$field];
            }
        }
        if(!$sqlSet)
            return 0;
        $sql = "INSERT INTO " . self::TABLE_NAME . $sqlSet
            . ", uid = :uid, version = 10";
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare($sql);
        $ret = $stmt->execute($params);
        if(!$ret || !$DB->lastInsertId())
            throw new \PDOException("Failed to insert new row");
        $Merchant->id = $DB->lastInsertId();
        return $Merchant;
    }

    public static function generateGUID() {
        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }
}

