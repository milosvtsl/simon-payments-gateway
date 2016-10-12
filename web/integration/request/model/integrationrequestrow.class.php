<?php
/**
 * Created by PhpStorm.
 * Integration: ari
 * Date: 8/28/2016
 * Time: 1:32 PM
 */
namespace Integration\Request\Model;

use Config\DBConfig;
use Integration\Model\AbstractIntegration;
use Integration\Model\IntegrationRow;

class IntegrationRequestRow
{
    const _CLASS = __CLASS__;

    const ENUM_TYPE_MERCHANT            = 'merchant';
    const ENUM_TYPE_MERCHANT_IDENTITY   = 'merchant-identity';
    const ENUM_TYPE_MERCHANT_PROVISION  = 'merchant-provision';
    const ENUM_TYPE_MERCHANT_PAYMENT    = 'merchant-payment';
    const ENUM_TYPE_TRANSACTION         = 'transaction';
    const ENUM_TYPE_TRANSACTION_VOID    = 'transaction-void';
    const ENUM_TYPE_TRANSACTION_RETURN  = 'transaction-return';

    const ENUM_RESULT_SUCCESS           = 'success';
    const ENUM_RESULT_FAIL              = 'fail';
    const ENUM_RESULT_ERROR             = 'error';

    const SORT_BY_ID                = 'ir.id';
    const SORT_BY_INTEGRATION_ID    = 'ir.integration_id';
    const SORT_BY_TYPE              = 'ir.type';
    const SORT_BY_TYPE_ID           = 'ir.type_id';
    const SORT_BY_DATE              = 'ir.date';
    const SORT_BY_RESULT            = 'ir.result';

    public static $SORT_FIELDS = array(
        self::SORT_BY_ID,
        self::SORT_BY_INTEGRATION_ID,
        self::SORT_BY_TYPE,
        self::SORT_BY_TYPE_ID,
        self::SORT_BY_DATE,
        self::SORT_BY_RESULT,
    );


    const SQL_SELECT = "
SELECT ir.*, i.name integration_name, i.class_path integration_class_path
FROM integration_request ir
LEFT JOIN integration i ON i.id = ir.integration_id
";
    const SQL_GROUP_BY = "\nGROUP BY ir.id";
    const SQL_ORDER_BY = "\nORDER BY ir.id DESC";

    public function __set($key, $value) {
        throw new \InvalidArgumentException("Property does not exist: " . $key);
    }


    // Properties

    protected $id;
    protected $integration_id;
    protected $type;
    protected $type_id;
    protected $url;
    protected $request;
    protected $response;
    protected $result;
    protected $date;

    // Table: integration

    protected $integration_name;
    protected $integration_class_path;

    // Functions

    public function getID()                 { return $this->id; }
    public function getIntegrationID()      { return $this->integration_id; }
    public function getIntegrationName()    { return $this->integration_name; }
    public function getClassPath()          { return $this->integration_class_path; }
    public function getIntegrationType()    { return $this->type; }
    public function getIntegrationTypeID()  { return $this->type_id; }
    public function getRequest()            { return $this->request; }
    public function getResponse()           { return $this->response; }
    public function getResult()             { return $this->result; }
    public function getDate()               { return $this->date; }

    public function setRequest($request)    { $this->request = $request; }
    public function setResponse($response)  { $this->response = $response; }
    public function setResult($result)      { $this->result = $result; }
    public function setRequestURL($url)     { $this->url = $url; }

    /**
     * @return AbstractIntegration
     */
    function getIntegration() {
        $class = $this->getClassPath();
        /** @var AbstractIntegration $Integration */
        $Integration = new $class;
        return $Integration;
    }

    function execute() {
        $Integration = $this->getIntegration();
        $Integration->execute($this);
    }

    function isRequestSuccessful(&$reason=null) {
        $Integration = $this->getIntegration();
        return $Integration->isRequestSuccessful($this, $reason);
    }

    function printFormHTML() {
        $Integration = $this->getIntegration();
        $Integration->printFormHTML($this);
    }

    function parseResponseData() {
        $Integration = $this->getIntegration();
        return $Integration->parseResponseData($this);
    }

    public function getRequestURL() {
        // If URL was defined, return it
        if($this->url)
            return $this->url;

        // If URL was not defined yet, generate it now
        $Integration = $this->getIntegration();
        return $Integration->getRequestURL($this);
    }

    // Static

    public static function prepareNew($integration_class_path, $integration_id, $type, $type_id) {
        $Request = new self;
        $Request->integration_class_path = $integration_class_path;
        $Request->integration_id = $integration_id;
        $Request->type = $type;
        $Request->type_id = $type_id;
        return $Request;
    }

    /**
     * @param $id
     * @return IntegrationRequestRow
     */
    public static function fetchByID($id) {
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare(static::SQL_SELECT . "WHERE ir.id = ?");
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $stmt->setFetchMode(\PDO::FETCH_CLASS, self::_CLASS);
        $stmt->execute(array($id));
        return $stmt->fetch();
    }

    public static function fetchByType($type, $type_id, $integration_id, $result='success') {
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare(static::SQL_SELECT
            . "WHERE ir.type = :type"
            . "\n\tAND ir.type_id = :type_id"
            . "\n\tAND ir.integration_id = :integration_id"
            . "\n\tAND ir.result LIKE :result");
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $stmt->setFetchMode(\PDO::FETCH_CLASS, self::_CLASS);
        $stmt->execute(array(
            ':type' => $type,
            ':type_id' => $type_id,
            ':integration_id' => $integration_id,
            ':result' => $result,
        ));
        return $stmt->fetch();
    }


    /**
     * @param IntegrationRequestRow $NewRow
     * @throws \Exception
     */
    public static function insert(IntegrationRequestRow $NewRow) {
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare("INSERT INTO integration_request\n"
            . "SET `type` = :type,"
            . "\n\t`type_id` = :type_id,"
            . "\n\t`integration_id` = :integration_id,"
            . "\n\t`url` = :url,"
            . "\n\t`request` = :request,"
            . "\n\t`response` = :response,"
            . "\n\t`result` = :result,"
            . "\n\t`date` = NOW()");
        $ret = $stmt->execute(array(
            ':type' => $NewRow->getIntegrationType(),
            ':type_id' => $NewRow->getIntegrationTypeID(),
            ':integration_id' => $NewRow->getIntegrationID(),
            ':url' => $NewRow->getRequestURL(),
            ':request' => $NewRow->getRequest(),
            ':response' => $NewRow->getResponse(),
            ':result' => $NewRow->getResult(),
        ));
        if(!$ret || !$DB->lastInsertId())
            throw new \PDOException("Failed to insert new row");
    }


}

