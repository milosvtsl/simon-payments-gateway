<?php
namespace System\Config;


class DBConfig
{
    static $DB_USERNAME = 'root';
    static $DB_PASSWORD = null;
    static $DB_NAME = 'spg';
    static $DB_HOST = 'localhost';
    static $DB_PORT = null;

    private static $_dbInstance = null;

    public static function getInstance($options = null) {
        if(static::$_dbInstance)
            return static::$_dbInstance;

        $host     = static::$DB_HOST;
        $dbname   = static::$DB_NAME;
        $port     = static::$DB_PORT;

        try {
            $PDO = new \PDO("mysql:host={$host};port={$port};dbname={$dbname}",
                static::$DB_USERNAME,
                static::$DB_PASSWORD,
                $options);

        } catch (\PDOException $ex) {
            throw new \Exception($ex->getMessage());
        }
        $PDO->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $PDO->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        static::$_dbInstance = $PDO;
        return $PDO;
    }


}

require_once 'siteconfig.class.php';
include_once __DIR__ .'/../../../config.php';