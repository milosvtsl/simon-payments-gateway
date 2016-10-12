<?php
/**
 * Created by PhpStorm.
 * Integration: ari
 * Date: 8/28/2016
 * Time: 1:32 PM
 */
namespace Integration\Model;

use Config\DBConfig;
use Merchant\Model\MerchantRow;
use Order\Model\OrderRow;
use Transaction\Model\TransactionRow;

class IntegrationRow
{
    const TABLE_NAME = 'integration';
    const _CLASS = __CLASS__;

    const ENUM_API_TYPE_PRODUCTION  = 'production';
    const ENUM_API_TYPE_TESTING     = 'testing';
    const ENUM_API_TYPE_DISABLED    = 'disabled';

    const SORT_BY_ID                = 'i.id';
    const SORT_BY_NAME              = 'i.name';

    public static $SORT_FIELDS = array(
        self::SORT_BY_ID,
        self::SORT_BY_NAME,
    );
    public static $UPDATE_FIELDS = array(
        'name',
        'class_path',
        'api_url_base',
        'api_password',
        'api_app_id',
        'notes'
    );


    const SQL_SELECT = "
SELECT i.*,
  (SELECT count(*) FROM integration_request ir WHERE i.id = ir.integration_id AND ir.result='success') as request_success,
  (SELECT count(*) FROM integration_request ir WHERE i.id = ir.integration_id AND ir.result='fail') as request_fail,
  (SELECT count(*) FROM integration_request ir WHERE i.id = ir.integration_id) as request_total
FROM integration i
";
    const SQL_GROUP_BY = "\nGROUP BY i.id";
    const SQL_ORDER_BY = "\nORDER BY i.api_type='production' DESC";


    public function __construct(Array $params=array()) {
        foreach($params as $key=>$param)
            $this->$key = $param;
    }
    public function __set($key, $value) {
        throw new \InvalidArgumentException("Property does not exist: " . $key);
    }

    // Properties

    protected $id;
    protected $uid;
    protected $name;
    protected $class_path;
    protected $api_url_base;
    protected $api_username;
    protected $api_password;
    protected $api_app_id;
    protected $api_type;
    protected $notes;

    // Calculated

    protected $request_success;
    protected $request_fail;
    protected $request_total;

    // Functions

    public function getID()             { return $this->id; }
    public function getUID()            { return $this->uid; }
    public function getName()           { return $this->name; }
    public function getClassPath()      { return $this->class_path; }
    public function getAPIURLBase()     { return $this->api_url_base; }
    public function getAPIUsername()    { return $this->api_username; }
    public function getAPIPassword()    { return $this->api_password; }
    public function getAPIAppID()       { return $this->api_app_id; }
    public function getAPIType()        { return $this->api_type; }
    public function getNotes()          { return $this->notes; }

    public function getSuccessCount()   { return $this->request_success; }
    public function getFailCount()      { return $this->request_fail; }
    public function getTotalCount()     { return $this->request_total; }

    public function updateFields($post) {
        $sqlSet = "";
        $params = array();
        foreach(self::$UPDATE_FIELDS as $field) {
            if(isset($post[$field])) {
                $params[':'.$field] = $post[$field];
                $sqlSet .= ($sqlSet ? ",\n" : "\nSET ") . $field . '=:' . $field;
                $this->$field = $post[$field];
            }
        }
        if(!$sqlSet)
            return 0;
        $sql = "UPDATE " . self::TABLE_NAME . $sqlSet . "\nWHERE id=:id";
        $params[':id'] = $this->getID();
        $DB = DBConfig::getInstance();
        $EditQuery = $DB->prepare($sql);
        $EditQuery->execute($params);
        return $EditQuery->rowCount();
    }

    /**
     * Get or create a Merchant Identity
     * @param MerchantRow $Merchant
     * @return AbstractMerchantIdentity
     */
    public function getMerchantIdentity(MerchantRow $Merchant) {
        $Integration = $this->getIntegration();
        return $Integration->getMerchantIdentity($Merchant, $this);
    }

    /**
     * @return AbstractIntegration
     */
    public function getIntegration() {
        $class = $this->getClassPath();
        $Integration = new $class($this);
        return $Integration;
    }

    // Static

    /**
     * @param $id
     * @return IntegrationRow
     */
    public static function fetchByID($id) {
        if(!$id)
            throw new \InvalidArgumentException("Invalid Integration ID");
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare(static::SQL_SELECT . "WHERE i.id = ?");
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $stmt->setFetchMode(\PDO::FETCH_CLASS, self::_CLASS);
        $stmt->execute(array($id));
        $Integration = $stmt->fetch();
        if(!$Integration)
            throw new \InvalidArgumentException("Integration not found: " . $id);
        return $Integration;
    }

    /**
     * @param $uid
     * @return IntegrationRow
     */
    public static function fetchByUID($uid) {
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare(static::SQL_SELECT . "WHERE i.uid = ?");
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $stmt->setFetchMode(\PDO::FETCH_CLASS, self::_CLASS);
        $stmt->execute(array($uid));
        return $stmt->fetch();
    }

    public static function queryAll($order = 'i.id DESC') {
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare(static::SQL_SELECT . "\nORDER BY " . $order);
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $stmt->setFetchMode(\PDO::FETCH_CLASS, self::_CLASS);
        $stmt->execute();
        return $stmt;
    }



}

