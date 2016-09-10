<?php
/**
 * Created by PhpStorm.
 * Integration: ari
 * Date: 8/28/2016
 * Time: 1:32 PM
 */
namespace Integration\Request\Model;

use Config\DBConfig;

class IntegrationRequestRow
{
    const _CLASS = __CLASS__;

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

    // Properties

    protected $id;
    protected $integration_id;
    protected $type;
    protected $type_id;
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

    // Static

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

    public static function queryAll($order = 'ir.id DESC') {
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare(static::SQL_SELECT . "\nORDER BY " . $order);
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $stmt->setFetchMode(\PDO::FETCH_CLASS, self::_CLASS);
        $stmt->execute();
        return $stmt;
    }



}

