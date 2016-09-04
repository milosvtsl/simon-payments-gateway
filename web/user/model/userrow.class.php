<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/28/2016
 * Time: 1:32 PM
 */
namespace User\Model;

use Config\DBConfig;
use Merchant\Model\MerchantRow;
use User\Model\UserAuthorityRow;

class UserRow
{
    const TABLE_NAME = 'user';
    const _CLASS = __CLASS__;

    // Table user
    protected $id;
    protected $uid;
    protected $version;
    protected $email;
    protected $enabled;
    protected $fname;
    protected $lname;
    protected $password;
    protected $username;

    // Table authority
    protected $merchant_list;
    protected $authority_list;

    const SQL_SELECT = "
SELECT u.*,
 (SELECT GROUP_CONCAT(m.id SEPARATOR ';') FROM user_merchants um, merchant m WHERE m.id = um.id_merchant AND um.id_user = u.id ) as merchant_list,
 (SELECT GROUP_CONCAT(CONCAT_WS(';', a.authority, a.authority_name) SEPARATOR '\n') FROM user_authorities ua, authority a WHERE a.id = ua.id_authority AND ua.id_user = u.id ) as authority_list
FROM user u
";
    const SQL_GROUP_BY = "\nGROUP BY u.id";
    const SQL_ORDER_BY = "\nORDER BY u.id DESC";

    public function getID()         { return $this->id; }
    public function getUID()        { return $this->uid; }
    public function getUsername()   { return $this->username; }
    public function getEmail()      { return $this->email; }
    public function getFullName()   { return $this->fname . ' ' . $this->lname; }
    public function getFirstName()  { return $this->fname; }
    public function getLastName()   { return $this->lname; }

    public function getMerchantCount() { return sizeof($this->getMerchantList()); }
    public function getMerchantList() {
        if(is_array($this->merchant_list))
            return $this->merchant_list;
        $this->merchant_list = explode(';', $this->merchant_list);
        return $this->merchant_list;
    }

    public function getAuthorityList() {
        if(is_array($this->authority_list))
            return $this->authority_list;
        $list = explode("\n", $this->authority_list);
        $this->authority_list = array();
        foreach($list as $authority) {
            list($id, $name) = explode(';', $authority);
            $this->authority_list[strtoupper($id)] = $name;
        }
        return $this->authority_list;
    }

    public function hasAuthority($authority) {
        $list = $this->getAuthorityList();
        foreach(func_get_args() as $i => $arg)
            if (isset($list[strtoupper($authority)]))
                return true;
        return false;
    }

    public function validatePassword($password) {
        if(md5($password) === $this->password)
            return;

        if(sha1($password) === $this->password)
            return;

        if (password_verify($password, $this->password))
            return;

        throw new \InvalidArgumentException("Invalid Password");
    }

    public function queryUserMerchants() {
        return MerchantRow::queryByUserID($this->getID());
    }

    public function queryRoles() {
        return UserAuthorityRow::queryByUserID($this->getID());
    }

    public function changePassword($password, $password_confirm) {
        if ($password != $password_confirm)
            throw new \InvalidArgumentException("Password confirm mismatch");
        $password = crypt($password);
        $sql = "UPDATE " . self::TABLE_NAME
            . "\nSET password=:password"
            . "\nWHERE id = :id";
        $DB = DBConfig::getInstance();
        $PasswordQuery = $DB->prepare($sql);
        $PasswordQuery->execute(array(
            ':password' => $password,
            ':id' => $this->id
        ));
        return $PasswordQuery->rowCount();
    }

    public function updateFields($fname, $lname, $username, $email) {
        $sql = "
UPDATE " . self::TABLE_NAME . "
SET fname=:fname, lname=:lname, username=:username, email=:email
WHERE id = :id";
        $DB = DBConfig::getInstance();
        $EditQuery = $DB->prepare($sql);
        $EditQuery->execute(array(
            ':fname' => $fname ?: $this->fname,
            ':lname' => $lname ?: $this->lname,
            ':username' => $username ?: $this->username,
            ':email' => $email ?: $this->email,
            ':id' => $this->id
        ));
        return $EditQuery->rowCount();
    }

    // Static

    /**
     * @param $id
     * @return UserRow
     */
    public static function fetchByID($id) {
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare(static::SQL_SELECT . "WHERE u.id = ?");
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $stmt->setFetchMode(\PDO::FETCH_CLASS, 'User\Model\UserRow');
        $stmt->execute(array($id));
        return $stmt->fetch();
    }

    /**
     * @param $username
     * @return UserRow
     */
    public static function fetchByUsername($username) {
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare(static::SQL_SELECT . "WHERE u.username = ?");
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $stmt->setFetchMode(\PDO::FETCH_CLASS, 'User\Model\UserRow');
        $stmt->execute(array($username));
        return $stmt->fetch();
    }
}

