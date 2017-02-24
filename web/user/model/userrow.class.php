<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/28/2016
 * Time: 1:32 PM
 */
namespace User\Model;

use Merchant\Model\MerchantFormRow;
use Merchant\Model\MerchantRow;
use System\Config\DBConfig;

class UserRow
{
    const TABLE_NAME = 'user';
    const _CLASS = __CLASS__;

    const SORT_BY_ID                = 'u.id';
    const SORT_BY_USERNAME          = 'u.username';
    const SORT_BY_FNAME             = 'u.fname';
    const SORT_BY_LNAME             = 'u.lname';
    const SORT_BY_EMAIL             = 'u.email';

    public static $SORT_FIELDS = array(
        self::SORT_BY_ID,
        self::SORT_BY_USERNAME,
        self::SORT_BY_FNAME,
        self::SORT_BY_LNAME,
        self::SORT_BY_EMAIL,
    );


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
    protected $date;
    protected $app_config;
    protected $timezone;
    protected $admin_id;
    protected $merchant_form_id;
    protected $merchant_logo_path;

    // Table authority
    protected $merchant_list;
    protected $authority_list;

    const SQL_SELECT = "
SELECT u.*,
 (SELECT GROUP_CONCAT(m.id SEPARATOR ';') FROM user_merchants um LEFT JOIN merchant m ON m.id = um.id_merchant WHERE um.id_user = u.id ) as merchant_list,
 (SELECT m.logo_path FROM user_merchants um LEFT JOIN merchant m ON m.id = um.id_merchant WHERE um.id_user = u.id AND m.logo_path IS NOT NULL LIMIT 1) as merchant_logo_path,
 (SELECT GROUP_CONCAT(CONCAT_WS(';', a.authority, a.authority_name) SEPARATOR '\n') FROM user_authorities ua, authority a WHERE a.id = ua.id_authority AND ua.id_user = u.id ) as authority_list
FROM user u
";
    const SQL_GROUP_BY = "\nGROUP BY u.id";
    const SQL_ORDER_BY = "\nORDER BY u.id DESC";



    public function getID()             { return $this->id; }
    public function getUID()            { return $this->uid; }
    public function getUsername()       { return $this->username; }
    public function getEmail()          { return $this->email; }
    public function getFullName()       { return $this->fname . ' ' . $this->lname; }
    public function getFirstName()      { return $this->fname; }
    public function getLastName()       { return $this->lname; }
    public function getPasswordHash()   { return $this->password; }
    public function getCreateDate()     { return $this->date; }
    public function getTimeZone()       { return $this->timezone ?: 'America/New_York'; }
    public function getAdminID()        { return $this->admin_id; }
    public function getAppConfig()      { return $this->app_config; }
    public function getMerchantFormID() { return $this->merchant_form_id; }
    public function getMerchantLogo()   { return $this->merchant_logo_path; }

    public function getTimeZoneOffset($date='now') {
        $tz = new \DateTimeZone($this->getTimeZone());
        if(!$date instanceof \DateTime)
            $date = new \DateTime($date);
        return $tz->getOffset($date);
    }

    public function getMerchantCount() {
        return sizeof($this->getMerchantList());
    }

    /**
     * @return array
     * @deprecated 
     */
    public function getMerchantList() {
        if(is_array($this->merchant_list))
            return $this->merchant_list;
        if(!$this->merchant_list)
            return $this->merchant_list = array();
        $this->merchant_list = explode(';', $this->merchant_list);
        return $this->merchant_list;
    }

    public function hasMerchantID($merchant_id) {
        return in_array($merchant_id, $this->getMerchantList());
    }

    public function getAuthorityList() {
        if(is_array($this->authority_list))
            return $this->authority_list;
        if(!$this->authority_list)
            return array();
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
            if (isset($list[strtoupper($arg)]))
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

        throw new \InvalidArgumentException("Invalid Password. Please try again");
    }

    public function queryUserMerchants() {
        return MerchantRow::queryByUserID($this->getID());
    }

    public function isValidResetKey($key) {
        $valid = $key === crypt($this->password, $key);
        return $valid;
    }

    public function changePassword($password, $password_confirm) {
        if (strlen($password) < 5)
            throw new \InvalidArgumentException("Password must be at least 5 characters");
        if ($password != $password_confirm)
            throw new \InvalidArgumentException("Password confirm mismatch");
//        $password = crypt($password);
        $password = md5($password);
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

    public function updateAdminID($admin_id) {
        if($admin_id == $this->admin_id)
            return false;
        $this->admin_id = $admin_id;
        return static::update($this);
    }

    public function setDefaultOrderForm(MerchantFormRow $OrderForm) {
        if($this->merchant_form_id == $OrderForm->getID())
            return false;
        $this->merchant_form_id = $OrderForm->getID();
        return static::update($this);
    }


    public function updateFields($post) {
        if (!filter_var($post['email'], FILTER_VALIDATE_EMAIL))
            throw new \InvalidArgumentException("Invalid User Email");

        $this->fname = $post['fname'];
        $this->lname = $post['lname'];
        $this->email = $post['email'];

        $time = new \DateTimeZone($post['timezone']);
        $this->timezone = $time->getName();
        return static::update($this);
    }

    public function addAuthority($authority, $ignore_duplicate=true) {
        $Authority = AuthorityRow::fetchByName($authority);

        $sql_ignore = $ignore_duplicate ? "IGNORE " : "";
        $SQL = <<<SQL
INSERT {$sql_ignore}INTO user_authorities
SET
  id_user = :id_user,
  id_authority = :id_authority
SQL;
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare($SQL);
        $ret = $stmt->execute(array(
            ':id_user' => $this->getID(),
            ':id_authority' => $Authority->getID(),
        ));
        if(!$ret)
            throw new \PDOException("Failed to insert new row");
        return $stmt->rowCount() >= 1;
    }

    public function removeAuthority($authority) {
        $Authority = AuthorityRow::fetchByName($authority);

        $SQL = <<<SQL
DELETE FROM user_authorities
WHERE
  id_user = :id_user
  AND id_authority = :id_authority
SQL;
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare($SQL);
        $ret = $stmt->execute(array(
            ':id_user' => $this->getID(),
            ':id_authority' => $Authority->getID(),
        ));
        if(!$ret)
            throw new \PDOException("Failed to remove row");
        return $stmt->rowCount() >= 1;
    }

    public function addMerchantID($merchant_id, $ignore_duplicate=true) {
        $sql_ignore = $ignore_duplicate ? "IGNORE " : "";
        $SQL = <<<SQL
INSERT {$sql_ignore}INTO user_merchants
SET
  id_user = :id_user,
  id_merchant = :id_merchant
SQL;
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare($SQL);
        $ret = $stmt->execute(array(
            ':id_user' => $this->getID(),
            ':id_merchant' => $merchant_id,
        ));
        if(!$ret)
            throw new \PDOException("Failed to insert new row");
        return $stmt->rowCount() >= 1;
    }

    public function removeMerchantID($merchant_id) {
        $SQL = <<<SQL
DELETE FROM user_merchants
WHERE
  id_user = :id_user
  AND id_merchant = :id_merchant
SQL;
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare($SQL);
        $ret = $stmt->execute(array(
            ':id_user' => $this->getID(),
            ':id_merchant' => $merchant_id,
        ));
        if(!$ret)
            throw new \PDOException("Failed to remove row");
        return $stmt->rowCount() >= 1;
    }



    // Static
    public static function fetchByUID($uid)
    {
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare(static::SQL_SELECT . "WHERE u.uid = ?");
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $stmt->setFetchMode(\PDO::FETCH_CLASS, 'User\Model\UserRow');
        $stmt->execute(array($uid));
        $Row = $stmt->fetch();
        if(!$Row)
            throw new \InvalidArgumentException("User UID not found: " . $uid);
        return $Row;
    }

    /**
     * @param $id
     * @return UserRow
     */
    public static function fetchByID($id) {
        if(!is_numeric($id))
            throw new \InvalidArgumentException("ID is not numeric: " . $id);

        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare(static::SQL_SELECT . "WHERE u.id = ?");
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $stmt->setFetchMode(\PDO::FETCH_CLASS, 'User\Model\UserRow');
        $stmt->execute(array($id));
        $Row = $stmt->fetch();
        if(!$Row)
            throw new \InvalidArgumentException("User ID not found: " . $id);
        return $Row;
    }

    /**
     * @param $field
     * @param $value
     * @return UserRow
     * @throws \Exception
     */
    public static function fetchByField($field, $value) {
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare(static::SQL_SELECT . "WHERE u.{$field} = ?");
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $stmt->setFetchMode(\PDO::FETCH_CLASS, 'User\Model\UserRow');
        $stmt->execute(array($value));
        $Row = $stmt->fetch();
        if(!$Row)
            throw new \InvalidArgumentException("{$field} not found: " . $value);
        return $Row;
    }

    /**
     * @param $username
     * @return UserRow
     */
    public static function fetchByUsername($username) {
        return static::fetchByField('username', $username);
    }



    public static function fetchByEmail($email) {
        return static::fetchByField('email', $email);
    }

    /**
     * @param $post
     * @return UserRow
     */
    public static function createNewUser($post, UserRow $AdminUserRow=null) {
        if(!preg_match('/^[a-zA-Z0-9_-]+$/', $post['username']))
            throw new \InvalidArgumentException("Username may only contain alphanumeric and underscore characters");

        if(strlen($post['username']) < 4)
            throw new \InvalidArgumentException("Username must be at least 5 characters");

        if($post['password'] !== $post['password_confirm'])
            throw new \InvalidArgumentException("Password Mismatch");

        if (!filter_var($post['email'], FILTER_VALIDATE_EMAIL))
            throw new \InvalidArgumentException("Invalid User Email Format");

        $password = md5($post['password']);

        $User = new UserRow();
        $User->uid = strtoupper(self::generateGUID());
        $User->email = $post['email'];
        $User->enabled = 1;
        $User->fname = $post['fname'];
        $User->lname = $post['lname'];
        $User->password = $password;
        $User->username = $post['username'];
        $User->timezone = $post['timezone'];

        if($AdminUserRow)
            $User->admin_id = $AdminUserRow->getID();

        UserRow::insert($User);

        return $User;
    }

    /**
     * @param UserRow $User
     * @throws \Exception
     */
    public static function delete(UserRow $User) {
        $DB = DBConfig::getInstance();

        $SQL = "DELETE FROM user_authorities \nWHERE id_user=?";
        $stmt = $DB->prepare($SQL);
        if(!$stmt->execute(array($User->getID())))
            throw new \PDOException("Failed to insert new row");

        $SQL = "DELETE FROM user_merchants \nWHERE id_user=?";
        $stmt = $DB->prepare($SQL);
        if(!$stmt->execute(array($User->getID())))
            throw new \PDOException("Failed to insert new row");

        $SQL = "DELETE FROM user\nWHERE id=?";
        $stmt = $DB->prepare($SQL);
        if(!$stmt->execute(array($User->getID())))
            throw new \PDOException("Failed to insert new row");
        if($stmt->rowCount() === 0)
            error_log("Failed to delete row: " . print_r($User, true));
    }

    /**
     * @param UserRow $User
     * @return UserRow
     * @throws \Exception
     */
    public static function insert(UserRow $User) {
        $values = array(
            ':uid' => $User->uid,
            ':email' => $User->email,
            ':enabled' => $User->enabled,
            ':fname' => $User->fname,
            ':lname' => $User->lname,
            ':password' => $User->password,
            ':username' => $User->username,
            ':timezone' => $User->timezone,
            ':admin_id' => $User->admin_id,
        );
        $SQL = '';
        foreach($values as $key=>$value)
            $SQL .= ($SQL?",\n":"") . "\n\t`" . substr($key, 1) . "` = " . $key;
        $SQL .= ",\n\t`date` = UTC_TIMESTAMP()";
        $User->date = date('Y-m-d G:i:s');

        $SQL = "INSERT INTO user\nSET" . $SQL;

        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare($SQL);
        $ret = $stmt->execute($values);
        if(!$ret)
            throw new \PDOException("Failed to insert new row");

        $User->id = $DB->lastInsertId();

        return $User;
    }


    /**
     * @param UserRow $User
     * @return UserRow
     * @throws \Exception
     */
    public static function update(UserRow $User) {
        if(!$User->id)
            throw new \InvalidArgumentException("Invalid User ID");

        $values = array(
            ':email' => $User->email,
            ':enabled' => $User->enabled,
            ':fname' => $User->fname,
            ':lname' => $User->lname,
            ':password' => $User->password,
            ':username' => $User->username,
            ':timezone' => $User->timezone,
            ':admin_id' => $User->admin_id,
            ':merchant_form_id' => $User->merchant_form_id,
        );
        $SQL = '';
        foreach($values as $key=>$value)
            $SQL .= ($SQL?",\n":"") . "\n\t`" . substr($key, 1) . "` = " . $key;

        $SQL = "UPDATE user\nSET" . $SQL . "\nWHERE id = " . intval($User->id);

        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare($SQL);
        $ret = $stmt->execute($values);
        if(!$ret)
            throw new \PDOException("Failed to insert new row");

        return $stmt->rowCount();
    }

    public static function generateGUID() {
        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }


}

