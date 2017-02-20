<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/28/2016
 * Time: 1:32 PM
 */
namespace Payment\Model;

use System\Config\DBConfig;

class PayeeRow
{
    const _CLASS = __CLASS__;

    const SORT_BY_ID                = 'p.id';

    public static $SORT_FIELDS = array(
        self::SORT_BY_ID,
    );

    // Table payment
    protected $id;
    protected $uid;
//    protected $type;
    protected $created;
    protected $status;

    protected $payee_first_name;
    protected $payee_last_name;
    protected $payee_phone_number;
    protected $payee_reciept_email;
    protected $payee_address;
    protected $payee_address2;
    protected $payee_zipcode;
    protected $payee_city;
    protected $payee_state;
    protected $payee_state_full;

    const SQL_SELECT = "
SELECT p.*,
st.name as payee_state_full

FROM payee p
LEFT JOIN state st on st.short_code = p.payee_state
";
    const SQL_GROUP_BY = "\nGROUP BY p.id";
    const SQL_ORDER_BY = "\nORDER BY p.id DESC";


    public function getID()                 { return $this->id; }

    public function getUID(){
        return $this->uid;
    }

    public function getStatus()             { return $this->status; }
    public function getCreateDate()         { return $this->created; }

    public function getPayeeFirstName()     { return $this->payee_first_name; }
    public function getPayeeLastName()      { return $this->payee_last_name; }
    public function getPayeeAddress()       { return $this->payee_address; }
    public function getPayeeAddress2()      { return $this->payee_address2; }
    public function getPayeeZipCode()       { return $this->payee_zipcode; }
    public function getPayeeCity()          { return $this->payee_city; }
    public function getPayeeStateShort()    { return $this->payee_state; }
    public function getPayeeState()         { return $this->payee_state_full; }
    public function getPayeeEmail()         { return $this->payee_reciept_email; }
    public function getPayeePhone()         { return $this->payee_phone_number; }

    public function setStatus($status)      { $this->status = $status; }

    public function setPayeeEmail($email)   { $this->payee_reciept_email = $email; }

    // Static

    public static function delete(PayeeRow $PayerRow) {
        $SQL = "DELETE FROM payee WHERE id = ?";
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare($SQL);
        $ret = $stmt->execute(array($PayerRow->getID()));
        if(!$ret)
            throw new \PDOException("Failed to delete row");
        if($stmt->rowCount() === 0)
            error_log("Failed to delete row: " . print_r($PayerRow, true));
    }


    public static function insert(PayeeRow $PayerRow) {
        if($PayerRow->id)
            throw new \InvalidArgumentException("Payer Row has already been inserted");
        self::insertOrUpdate($PayerRow);
    }

    public static function update(PayeeRow $PayerRow) {
        if(!$PayerRow->id)
            throw new \InvalidArgumentException("Payer Row is missing an id");
        self::insertOrUpdate($PayerRow);
    }

    public static function insertOrUpdate(PayeeRow $PayerRow) {
        $values = array(
            ':uid' => $PayerRow->uid,

            ':payee_first_name' => $PayerRow->payee_first_name,
            ':payee_last_name' => $PayerRow->payee_last_name,
            ':payee_phone_number' => $PayerRow->payee_phone_number,
            ':payee_reciept_email' => $PayerRow->payee_reciept_email,
            ':payee_address' => $PayerRow->payee_address,
            ':payee_address2' => $PayerRow->payee_address2,

            ':payee_zipcode' => $PayerRow->payee_zipcode,
            ':payee_city' => $PayerRow->payee_city,
            ':payee_state' => $PayerRow->payee_state,

            ':status' => $PayerRow->status,
        );

        $SQL = '';
        foreach($values as $key=>$value)
            $SQL .= ($SQL ? ',' : '') . "\n\t`" . substr($key, 1) . "` = " . $key;

        if($PayerRow->id) {
            $values[':id'] = $PayerRow->id;
            $SQL = "UPDATE payee SET\n" . $SQL . "\nWHERE id = :id\nLIMIT 1";
        } else {
            $SQL = "INSERT INTO payee SET `created` = NOW(),\n" . $SQL;
        }

        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare($SQL);
        $ret = $stmt->execute($values);
        if(!$ret)
            throw new \PDOException("Failed to insert new row");
        if($DB->lastInsertId())
            $PayerRow->id = $DB->lastInsertId();
    }


    /**
     * @param $field
     * @param $value
     * @return PayeeRow
     * @throws \Exception
     */
    public static function fetchByField($field, $value) {
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare(static::SQL_SELECT . "WHERE p.{$field} = ?");
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $stmt->setFetchMode(\PDO::FETCH_CLASS, self::_CLASS);
        $stmt->execute(array($value));
        $Row = $stmt->fetch();
        if(!$Row)
            throw new \InvalidArgumentException("{$field} not found: " . $value);
        return $Row;
    }

    /**
     * @param $uid
     * @return PayeeRow
     */
    public static function fetchByUID($uid) {
        return static::fetchByField('uid', $uid);
    }

    /**
     * @param $id
     * @return PayeeRow
     */
    public static function fetchByID($id) {
        return static::fetchByField('id', $id);
    }


    /**
     * @param array $post
     * @return PayeeRow
     * @throws \Exception
     */
    public static function createPayerFromPost(Array $post) {
        // TODO check for duplicate/existing

        $PayeeRow = new PayeeRow();
        $PayeeRow->status = "Enabled";

        if(!empty($post['payee_full_name']))
            list($post['payee_first_name'], $post['payee_last_name']) = explode(' ', $post['payee_full_name'], 2);

        $PayeeRow->payee_first_name = $post['payee_first_name'];
        $PayeeRow->payee_last_name = $post['payee_last_name'];
        $PayeeRow->payee_phone_number = @$post['payee_phone_number'];
        $PayeeRow->payee_address = $post['payee_address'];
        $PayeeRow->payee_address2 = $post['payee_address2'];

        $PayeeRow->payee_zipcode = $post['payee_zipcode'];
        $PayeeRow->payee_city = @$post['payee_city'];
        $PayeeRow->payee_state = @$post['payee_state'];

        if(isset($post['payee_reciept_email'])) {
            $PayeeRow->payee_reciept_email = $post['payee_reciept_email'];
            if (!filter_var($PayeeRow->payee_reciept_email, FILTER_VALIDATE_EMAIL))
                throw new \Exception("Invalid Email");
        }

        $PayeeRow->uid = strtoupper(self::generateGUID($PayeeRow));
        return $PayeeRow;
    }

    public static function generateGUID(PayeeRow $Row) {
        return 'P0' . substr(sprintf('%04X-%04X-%04X-%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479)), 2);
    }

    /**
     * Unit Test
     * @throws \Exception
     */
    public static function unitTest() {
        require_once __DIR__ . '/../../system/config/dbconfig.class.php';

        $post = array(
            'payee_first_name' => 'test',
            'payee_last_name' => 'test',
            'payee_phone_number' => '1234321',
            'payee_reciept_email' => 'test@test.com',
            'payee_address' => 'test 123',
            'payee_address2' => '',
            'payee_zipcode' => '21234',
            'payee_city' => 'test',
            'payee_state' => 'FL',
        );

        $NewRow = self::createPayerFromPost($post);
        self::insertOrUpdate($NewRow);
        self::delete($NewRow);
    }
}

if(isset($argv) && in_array(@$argv[1], array('test-payee', 'test-all')))
    PayeeRow::unitTest();

