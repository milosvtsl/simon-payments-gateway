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
    protected $merchant_count;
    protected $authority_list;

    const SQL_SELECT = "
SELECT u.*,
 (SELECT COUNT(*) FROM user_merchants um, merchant m WHERE m.id = um.id_merchant AND um.id_user = u.id ) as merchant_count,
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

    public function getMerchantCount() { return $this->merchant_count; }

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
        return isset($list[strtoupper($authority)]);
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

    public function queryMerchants() {
        $sql = MerchantRow::SQL_SELECT
            . "\nLEFT JOIN user_merchants um on m.id = um.id_merchant "
            . "\nWHERE um.id_user = ?";
        $DB = DBConfig::getInstance();
        $MerchantQuery = $DB->prepare($sql);
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $MerchantQuery->setFetchMode(\PDO::FETCH_CLASS, 'Merchant\Model\MerchantRow');
        $MerchantQuery->execute(array($this->getID()));
        return $MerchantQuery;
    }

    public function queryRoles() {
        return UserAuthorityRow::queryByUserID($this->getID());
    }

    public function updateFields(Array $post) {
        $sqlParams = array();
        $sqlFields = array();
        foreach(array('username', 'fname', 'lname', 'email') as $field) {
            if (isset($post[$field])) {
                $sqlFields[] = $field . "=?\n";
                $sqlParams[] = $post[$field];
            }
        }
        if(sizeof($sqlFields) <= 0)
            throw new \InvalidArgumentException("No fields updated");

        $sql = "UPDATE " . self::TABLE_NAME
            . "\nSET ". implode(', ', $sqlFields)
            . "\nWHERE id = ?";
        $sqlParams[] = $this->getID();
        $DB = DBConfig::getInstance();
        $EditQuery = $DB->prepare($sql);
        $EditQuery->execute($sqlParams);
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

