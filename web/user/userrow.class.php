<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/28/2016
 * Time: 1:32 PM
 */
namespace User;

use Config\DBConfig;

class UserRow
{
    protected $id;
    protected $uid;
    protected $version;
    protected $email;
    protected $enabled;
    protected $fname;
    protected $lname;
    protected $password;
    protected $username;


    public function getID()         { return $this->id; }
    public function getUsername()   { return $this->username; }
    public function getEmail()      { return $this->email; }
    public function getFullName()   { return $this->fname . ' ' . $this->lname; }


    public function validatePassword($password) {
        if(md5($password) === $this->password)
            return;

        if(sha1($password) === $this->password)
            return;

        if (password_verify($password, $this->password))
            return;

        throw new \InvalidArgumentException("Invalid Password");
    }

    public function queryMerchants() {
        $sql = "
            SELECT *
            FROM merchant m, user_merchants um
            WHERE m.id = um.id_merchant AND um.id_user = ?";
        $DB = DBConfig::getInstance();
        $MerchantQuery = $DB->prepare($sql);
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $MerchantQuery->setFetchMode(\PDO::FETCH_CLASS, 'Merchant\MerchantRow');
        $MerchantQuery->execute(array($this->getID()));
        return $MerchantQuery;
    }

    // Static

    /**
     * @param $id
     * @return UserRow
     */
    public static function fetchByID($id) {
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare("SELECT * FROM user where id = ?");
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $stmt->setFetchMode(\PDO::FETCH_CLASS, 'User\UserRow');
        $stmt->execute(array($id));
        return $stmt->fetch();
    }

    /**
     * @param $username
     * @return UserRow
     */
    public static function fetchByUsername($username) {
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare("SELECT * FROM user where username = ?");
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $stmt->setFetchMode(\PDO::FETCH_CLASS, 'User\UserRow');
        $stmt->execute(array($username));
        return $stmt->fetch();
    }

}

