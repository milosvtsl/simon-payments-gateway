<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/28/2016
 * Time: 1:32 PM
 */
namespace User\Model;

use System\Config\DBConfig;

class AuthorityRow
{
    const _CLASS = __CLASS__;

    protected $id;
    protected $uid;
    protected $version;
    protected $authority;
    protected $authority_name;

    public function getID()         { return $this->id; }
    public function getUID()        { return $this->uid; }
    public function getAuthority()  { return $this->authority; }
    public function getName()       { return $this->authority_name; }

    const SQL_SELECT = "SELECT * FROM authority a";

    // Static


    /**
     * @param $name
     * @return AuthorityRow
     */
    public static function fetchByName($name) {
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare(static::SQL_SELECT . "\nWHERE a.authority_name = ? OR a.authority = ?");
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $stmt->setFetchMode(\PDO::FETCH_CLASS, self::_CLASS);
        $stmt->execute(array($name,$name));
        $Row = $stmt->fetch();
        if(!$Row)
            throw new \InvalidArgumentException("Authority not found: " . $name);
        return $Row;
    }



    /**
     * @return AuthorityRow[]
     */
    public static function queryAll() {
        $sql = static::SQL_SELECT;
        $DB = DBConfig::getInstance();
        $Query = $DB->prepare($sql);
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $Query->setFetchMode(\PDO::FETCH_CLASS, static::_CLASS);
        $Query->execute();
        return $Query;
    }
}

