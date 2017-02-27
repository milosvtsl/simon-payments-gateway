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
use User\Session\SessionManager;

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
//    protected $enabled;
    protected $fname;
    protected $lname;
    protected $password;
    protected $username;
    protected $date;
//    protected $app_config;
    protected $timezone;
    protected $admin_id;
    protected $merchant_id;
    protected $authority;

    // Table merchant
    protected $merchant_uid;
    protected $merchant_name;
    protected $merchant_logo_path;

    const SQL_SELECT = "
SELECT u.*,
 m.uid as merchant_uid,
 m.name as merchant_name,
 (SELECT m.logo_path FROM merchant m WHERE m.id = u.merchant_id AND m.logo_path IS NOT NULL LIMIT 1) as merchant_logo_path
FROM user u
LEFT JOIN merchant m on u.merchant_id = m.id
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
//    public function getAppConfig()      { return $this->app_config; }
    public function getMerchantUID()    { return $this->merchant_uid; }
    public function getMerchantName()   { return $this->merchant_name; }
    public function getMerchantLogo()   { return $this->merchant_logo_path; }

    public function getTimeZoneOffset($date='now') {
        $tz = new \DateTimeZone($this->getTimeZone());
        if(!$date instanceof \DateTime)
            $date = new \DateTime($date);
        return $tz->getOffset($date);
    }

    public function getMerchantID() {
        return $this->merchant_id;
    }

    public function getAuthorityList() {
        if(is_array($this->authority))
            return $this->authority;
        if(!$this->authority)
            return array();
        $this->authority = explode(",", strtoupper($this->authority));
        return $this->authority;
    }

    public function hasAuthority($authority) {
        $list = $this->getAuthorityList();
        foreach(func_get_args() as $i => $arg)
            if (in_array(strtoupper($arg), $list))
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

    public function isValidResetKey($key) {
        $valid = $key === crypt($this->password, $key);
        return $valid;
    }

//    public function setDefaultOrderForm(MerchantFormRow $OrderForm) {
//        if($this->merchant_form_id == $OrderForm->getID())
//            return false;
//        $this->merchant_form_id = $OrderForm->getID();
//        return static::update($this);


//    }

    public function updateFields($post, UserRow $SessionUser=null) {
        if (!filter_var($post['email'], FILTER_VALIDATE_EMAIL))
            throw new \InvalidArgumentException("Invalid User Email");

        if($SessionUser && $SessionUser->getID() !== $this->getID()
            && $SessionUser->getID() !== $this->getAdminID())
            $SessionUser->validatePassword($post['admin_password']);

        // Change Password
        if(!empty($post['password'])) {
            $password = $post['password'];
            if (strlen($password) < 5)
                throw new \InvalidArgumentException("Password must be at least 5 characters");
            if ($password != $post['password_confirm'])
                throw new \InvalidArgumentException("Password confirm mismatch");

            $this->password = crypt($password, md5(time()));
        }

        if($SessionUser && $SessionUser->hasAuthority('ADMIN', 'SUB_ADMIN')) {
            $this->authority = '';
            foreach($post['authority'] as $authority => $added) {
                if(in_array($authority, array('ADMIN', 'SUB_ADMIN'))
                    && !$SessionUser->hasAuthority("ADMIN"))
                    continue;
                if($added)
                    $this->authority .= ($this->authority ? ',' : '') . $authority;
            }
        }


        $this->fname = $post['fname'];
        $this->lname = $post['lname'];
        $this->email = $post['email'];

        if($SessionUser && $SessionUser->hasAuthority('ADMIN')) {
            if(!empty($post['merchant_id'])) $this->merchant_id = $post['merchant_id'];
            if(!empty($post['admin_id'])) $this->admin_id = $post['admin_id'];
        }

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
            throw new \InvalidArgumentException(ucfirst($field) . " not found: " . $value);
        return $Row;
    }

    /**
     * @param $name
     * @return UserRow
     */
    public static function fetchByUsernameOrEmail($name) {
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare(static::SQL_SELECT . "WHERE u.username = :name OR u.email = :name OR u.uid = :name");
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $stmt->setFetchMode(\PDO::FETCH_CLASS, 'User\Model\UserRow');
        $stmt->execute(array($name));
        $Row = $stmt->fetch();
        if(!$Row)
            throw new \InvalidArgumentException("Username or Email not found: " . $name);
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
//        $User->enabled = 1;
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
//            ':enabled' => $User->enabled,
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
     * @return int
     * @throws \Exception
     */
    public static function update(UserRow $User) {
        if(!$User->id)
            throw new \InvalidArgumentException("Invalid User ID");

        $values = array(
            ':email' => $User->email,
//            ':enabled' => $User->enabled,
            ':fname' => $User->fname,
            ':lname' => $User->lname,
            ':password' => $User->password,
            ':username' => $User->username,
            ':timezone' => $User->timezone,
            ':authority' => $User->authority,
            ':admin_id' => $User->admin_id,
            ':merchant_id' => $User->merchant_id,
//            ':merchant_form_id' => $User->merchant_form_id,
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

