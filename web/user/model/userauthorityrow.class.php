<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/28/2016
 * Time: 1:32 PM
 */
namespace User\Model;

use Config\DBConfig;
use User\Model\UserRow;

class UserAuthorityRow
{
    protected $id;
    protected $uid;
    protected $id_user;
    protected $version;
    protected $authority;
    protected $authority_name;

    public function getID()         { return $this->id; }
    public function getUserID()     { return $this->id_user; }
    public function getUID()        { return $this->uid; }
    public function getAuthority()  { return $this->authority; }
    public function getName()       { return $this->authority_name; }


    // Static

    /**
     * @param $id_user
     * @return UserRow
     */
    public static function queryByUserID($id_user) {
        $sql = "
            SELECT *
            FROM authority a, user_authorities ua
            WHERE ua.id_user = ? AND a.id = ua.id_authority";
        $DB = DBConfig::getInstance();
        $UserAuthorityQuery = $DB->prepare($sql);
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $UserAuthorityQuery->setFetchMode(\PDO::FETCH_CLASS, 'User\Model\UserAuthorityRow');
        $UserAuthorityQuery->execute(array($id_user));
        return $UserAuthorityQuery;
    }
}

