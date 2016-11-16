<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/28/2016
 * Time: 1:32 PM
 */
namespace User\Model;

use App\AbstractApp;
use System\Config\DBConfig;
use User\Model\UserRow;

class UserAppRow
{
    const ALL_USERS_ID = -1;
    const _CLASS = __CLASS__;

    protected $id;
    protected $id_user;
    protected $app_name;
    protected $status;
    protected $position;
    protected $cache;


    // Getters

    public function getID()         { return $this->id; }
    public function getUserID()     { return $this->id_user; }
    public function getAppName()    { return $this->app_name; }
    public function getStatus()     { return $this->status; }
    public function getPosition()   { return $this->position; }
    public function getCache()      { return $this->cache; }

    /**
     * Return an app instance
     * @return AbstractApp
     */
    public function getAppInstance() {
        $class = 'App\\' . str_replace(' ', '\\', ucwords(str_replace('_', ' ', $this->app_name)));
        /** @var AbstractApp $class */
        $App = new $class($this);
        return $App;
    }


    // Static

    public static function queryUserApps($id_user) {
        $sql = "
            SELECT *
            FROM user_app ua
            WHERE ua.id_user IN (?, ?)
            ORDER BY ua.position, ua.app_name";
        $DB = DBConfig::getInstance();
        $UserAppQuery = $DB->prepare($sql);
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $UserAppQuery->setFetchMode(\PDO::FETCH_CLASS, self::_CLASS);
        $UserAppQuery->execute(array($id_user, self::ALL_USERS_ID));
        return $UserAppQuery;
    }

}

