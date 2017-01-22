<?php
/**
 * Created by PhpStormf.
 * Merchant: ari
 * Date: 8/28/2016
 * Time: 1:32 PM
 */
namespace Merchant\Model;

use Order\Forms\AbstractForm;
use Order\Forms\DefaultOrderForm;
use Order\Forms\SimpleOrderForm;
use Order\Model\OrderRow;
use System\Config\DBConfig;
use User\Model\UserRow;

class MerchantFormRow
{
    const _CLASS = __CLASS__;
    const TABLE_NAME = 'merchant_form';

    const SORT_BY_ID                = 'mf.id';
    const SORT_BY_TITLE             = 'mf.title';

    const SQL_SELECT = "
SELECT mf.*
FROM merchant_form mf
";
    const SQL_GROUP_BY = "\nGROUP BY mf.id";
    const SQL_ORDER_BY = "\nORDER BY mf.created DESC";
    const SQL_WHERE =    "\nWHERE 1";

    public static $SORT_FIELDS = array(
        self::SORT_BY_ID,
        self::SORT_BY_TITLE,
    );

    const FLAG_RECUR_ENABLED = 'recur_enabled';
    public static $AVAILABLE_FIELDS = array(
        // Built In
//        'customer_id' => 'Customer ID',
//        'username' => 'Username',
//        'invoice_number' => 'Invoice #',

        // Custom
        'case_number' => 'Case #',
        'citation_number' => 'Citation #',
        'time_pay_number' => 'Time Pay #',
        'defendant_number' => 'Defendant #',
        'docket_number' => 'Docket #',
        'social_security_number' => 'S.S.N.',

        'birth_date' => 'D.O.B.',
        'court_date' => 'Court Date',

        'notes_text' => 'Notes',
        'plea_text' => 'Plea',
    );

    public static $BUILD_IN_FIELDS = array(
        'customer_id' => 'Customer ID',
        'username' => 'Username',
        'invoice_number' => 'Invoice #',

        'payee_receipt_email' => 'Email',
        'payee_phone_number' => 'Phone',
    );

    protected $id;
    protected $uid;
    protected $merchant_id;
    protected $title;
    protected $template;
    protected $classes;
    protected $fields;
    protected $flags;
    protected $created;

    public function __construct(Array $params=array()) {
        foreach($params as $key=>$param)
            $this->$key = $param;
    }



    public function __set($key, $value) {
        error_log("Property does not exist: " . $key);
    }

    public function getID()             { return $this->id; }
    public function getUID()            { return $this->uid; }
    public function getTitle()          { return $this->title; }
    public function getFormClasses()    { return $this->classes; }
    public function getTemplatePath()   { return $this->template ?: SimpleOrderForm::_CLASS; }

    public function getMerchantID()     { return $this->merchant_id; }

    public function getFlagList() {
        if(!is_array($this->flags)) 
            $this->flags = json_decode($this->flags, true);
        return $this->flags;                
    }
    public function hasFlag($flag) {
        $list = $this->getFlagList();
        return isset($list[$flag]);
    }
    public function setFlag($flag, $value=true) {
        $list = $this->getFlagList();
        $list[$flag] = $value;
    }
    public function getFlag($flag) {
        $list = $this->getFlagList();
        return $list[$flag];
    }

    public function getFieldList() {
        if(!is_array($this->fields))
            $this->fields = json_decode($this->fields, true) ?: array();
        return $this->fields;
    }
    public function hasField($fieldName) {
        $list = $this->getFieldList();
        return isset($list[$fieldName]);
    }

    public function isFieldRequired($fieldName) {
        $list = $this->getFieldList();
        return @$list[$fieldName]['required'] ? true : false;
    }

    public function isRecurAvailable() {
        return $this->hasFlag(self::FLAG_RECUR_ENABLED);
    }

    public function getCustomFieldName($fieldName, $defaultName=null) {
        $list = $this->getFieldList();
        return @$list[$fieldName]['name'] ?: $defaultName ?: @self::$AVAILABLE_FIELDS[$fieldName] ?: self::$BUILD_IN_FIELDS[$fieldName];
    }

//    public function setMerchantID($id)  { $this->merchant_id = $id; }

    public function getAllCustomFields($including_built_in_fields=false)
    {
        $list = array_keys($this->getFieldList());
        $list = array_diff($list, array_keys(self::$BUILD_IN_FIELDS));
        return $list;
    }

    public function updateFields($post) {
        $sqlSet = "\n`title` = :title ";
        $params = array(
            ':title' => $post['title'],
        );

        // Set Flags
        $flags = '';
        foreach($post['flags'] as $value)
            $flags[]= preg_replace('/[^a-z0-9_-]+/', '', $value);
        $params[':flags'] = implode(';', $flags);
        $sqlSet .= ",\n `flags` = :flags";

        // Set Classes
        $classes = '';
        foreach($post['classes'] as $value)
            $classes[]= preg_replace('/[^a-z0-9_-]+/', '', $value);
        $params[':classes'] = implode(';', $classes);
        $sqlSet .= ",\n `classes` = :classes";


        // Set Fields

        $fields = array(); // json_decode($this->fields, true);
        foreach($post['fields'] as $field => $value) {
            $field = preg_replace('/[^a-z0-9_-]+/', '', $field);
            $fields[$field] = array();
            $defaultName = @MerchantFormRow::$AVAILABLE_FIELDS[$field] ?: @MerchantFormRow::$BUILD_IN_FIELDS[$field] ?: $field;
            if(!$defaultName)
                continue;

            if(empty($value['enabled']) && empty($value['required'])) {
                unset($fields[$field]);
                continue;
            }
            if($value['name'] !== $defaultName)     $fields[$field]['name'] = $value['name'];
            else                                    unset($fields[$field]['name']);

            if(!empty($value['required']))      $fields[$field]['required'] = 1;
            else                                unset($fields[$field]['required']);
        }
        $params[':fields'] = json_encode($fields, JSON_PRETTY_PRINT);
        $sqlSet .= ",\n `fields` = :fields";


        $sql = "UPDATE " . self::TABLE_NAME . " SET " . $sqlSet . "\nWHERE id=:id";
        $params[':id'] = $this->getID();

//        print_r($post);
//        print_r($params);
//        die($sql);

        $DB = DBConfig::getInstance();
        $EditQuery = $DB->prepare($sql);
        $EditQuery->execute($params);
        return $EditQuery->rowCount();
    }

    /**
     * @return AbstractForm
     */
    public function getOrderFormTemplate() {
        $templateClass = $this->getTemplatePath();
        $Template = new $templateClass;
        return $Template;        
    }

    public function renderHTML(MerchantRow $Merchant, Array $params) {
        $Template = $this->getOrderFormTemplate();
        $Template->renderHTML($this, $Merchant, $params);
    }

    public function renderHTMLHeadLinks() {
        $Template = $this->getOrderFormTemplate();
        $Template->renderHTMLHeadLinks();
    }


    public function processFormRequest(OrderRow $Order, Array $post) {
        $Template = $this->getOrderFormTemplate();
        $Template->processFormRequest($this, $Order, $post);
    }

    // Static

    public static function getAvailableFields($include_built_in_fields=false) {
        if($include_built_in_fields)
            return self::$AVAILABLE_FIELDS + self::$BUILD_IN_FIELDS;
        return self::$AVAILABLE_FIELDS;
    }

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
        $stmt = $DB->prepare($sql . "\norder by uid='default' desc, id desc LIMIT 1");
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
     * @return MerchantFormRow
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
     * @param $userID
     * @return MerchantFormRow[] | \PDOStatement
     * @throws \Exception
     */
    public static function queryAvailableForms($userID) {
        $sql = static::SQL_SELECT
            . "\nLEFT JOIN user_merchants um on mf.merchant_id = um.id_merchant "
            . "\nWHERE um.id_user = ? OR mf.merchant_id is NULL"
            . "\nORDER BY mf.merchant_id desc, mf.id desc";
        $DB = DBConfig::getInstance();
        $MerchantFormQuery = $DB->prepare($sql);
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $MerchantFormQuery->setFetchMode(\PDO::FETCH_CLASS, self::_CLASS);
        $MerchantFormQuery->execute(array($userID));
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
     * @return MerchantFormRow
     */
    public static function createNewMerchantForm($post) {
        $params = array();
        $params[':uid'] = self::generateGUID();
        $sqlSet = "\nSET uid = :uid";

        if(strlen($post['title']) < 5)
            throw new \InvalidArgumentException("Form Title must be at least 5 characters");
        $params[':title'] = $post['title'];
        $sqlSet .= ",title = :title";

        $MerchantRow = MerchantRow::fetchByUID($post['merchant_uid']);
        $params[':merchant_id'] = $MerchantRow->getID();
        $sqlSet .= ",merchant_id = :merchant_id";

        $sql = "INSERT INTO " . self::TABLE_NAME . $sqlSet;
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare($sql);
        $ret = $stmt->execute($params);
        if(!$ret || !$DB->lastInsertId())
            throw new \PDOException("Failed to insert new row");
        $id = $DB->lastInsertId();
        $NewForm = self::fetchByID($id);
        return $NewForm;
    }

    public static function generateGUID() {
        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }


}