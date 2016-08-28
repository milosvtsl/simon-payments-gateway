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


    public function getID() { return $this->id; }

    public function validatePassword($password) {
        if(md5($password) === $this->password)
            return;

        if(sha1($password) === $this->password)
            return;

        if (password_verify($password, $this->password))
            return;

        throw new \InvalidArgumentException("Invalid Password");
    }

    // Static

    public static function fetchByID($id) {
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare("SELECT * FROM user where id = ?");
        $stmt->setFetchMode(\PDO::FETCH_CLASS, 'User\UserRow');
        $stmt->execute(array($id));
        return $stmt->fetch();
    }

    public static function fetchByUsername($username) {
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare("SELECT * FROM user where username = ?");
        $stmt->setFetchMode(\PDO::FETCH_CLASS, 'User\UserRow');
        $stmt->execute(array($username));
        return $stmt->fetch();
    }
}

