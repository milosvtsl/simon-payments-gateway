<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/28/2016
 * Time: 1:32 PM
 */
namespace System\Model;

use System\Config\DBConfig;

class StateRow
{
    const _CLASS = __CLASS__;

    // Table transaction
    protected $id;
    protected $uid;
    protected $version;
    protected $name;
    protected $short_code;

    const SQL_SELECT = "
SELECT s.*
FROM state s
";
    const SQL_GROUP_BY = ''; //"\nGROUP BY s.id";
    const SQL_ORDER_BY = "\nORDER BY s.id DESC";

    public function getID()                 { return $this->id; }
    public function getUID()                { return $this->uid; }
    public function getVersion()            { return $this->version; }
    public function getName()               { return $this->name; }
    public function getShortCode()          { return $this->short_code; }

    // Static

    public static function queryAll() {
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare(static::SQL_SELECT);
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $stmt->setFetchMode(\PDO::FETCH_CLASS, self::_CLASS);
        $stmt->execute();
        return $stmt;
    }


}

