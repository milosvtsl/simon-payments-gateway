<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/28/2016
 * Time: 1:32 PM
 */
namespace Payment\Model;

use Order\Model\OrderRow;
use System\Config\DBConfig;

class PaymentRow
{
    const _CLASS = __CLASS__;

    const SORT_BY_ID                = 'pm.id';

    public static $SORT_FIELDS = array(
        self::SORT_BY_ID,
    );

    // Table payment
    protected $id;
    protected $uid;
//    protected $type;
    protected $created;
    protected $status;

    protected $payee_id;
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

    protected $card_number;
    protected $card_type;
    protected $card_exp_month;
    protected $card_exp_year;

    protected $check_account_name;
    protected $check_account_bank_name;
    protected $check_account_number;
    protected $check_account_type;
    protected $check_routing_number;
    protected $check_type;

    protected $check_number;
    protected $integration_id;

    protected $integration_remote_id;
    const SQL_SELECT = "
SELECT pm.*,
p.payee_first_name,
p.payee_last_name,
p.payee_phone_number,
p.payee_reciept_email,
p.payee_address,
p.payee_address2,
p.payee_zipcode,
p.payee_city,
p.payee_state,
st.name as payee_state_full

FROM payment pm
LEFT JOIN payee p on p.id = pm.payee_id
LEFT JOIN state st on st.short_code = p.payee_state
";
    const SQL_GROUP_BY = "\nGROUP BY pm.id";
    const SQL_ORDER_BY = "\nORDER BY pm.id DESC";


    public function getID()                 { return $this->id; }

    public function getUID(){
        return $this->uid;
    }

    public function getType() {
        if($this->card_number && $this->card_type)
            return $this->card_type;
        if($this->check_account_number)
            return 'Check';
        return 'Unknown';
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
    public function getPayeeID()            { return $this->payee_id; }


    public function getCardNumber()         { return $this->card_number; }
    public function getCardType()           { return $this->card_type; }
    public function getCardExpMonth()       { return $this->card_exp_month; }
    public function getCardExpYear()        { return $this->card_exp_year; }

    public function getCheckAccountName()   { return $this->check_account_name; }
    public function getCheckAccountNumber() { return $this->check_account_number; }
    public function getCheckAccountType()   { return $this->check_account_type; }
    public function getCheckRoutingNumber() { return $this->check_routing_number; }
    public function getCheckAccountBank()   { return $this->check_account_bank_name; }
    public function getCheckNumber()        { return $this->check_number; }

    public function getCheckType()          { return $this->check_type; }
    public function getIntegrationID()      { return $this->integration_id; }

    public function getIntegrationRemoteID(){ return $this->integration_remote_id; }

    public function setStatus($status)      { $this->status = $status; }

    public function setPayeeEmail($email)   { $this->payee_reciept_email = $email; }

    // Static

    public static function delete(PaymentRow $PaymentRow) {
        $SQL = "DELETE FROM payment WHERE id = ?";
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare($SQL);
        $ret = $stmt->execute(array($PaymentRow->getID()));
        if(!$ret)
            throw new \PDOException("Failed to delete row");
        if($stmt->rowCount() === 0)
            error_log("Failed to delete row: " . print_r($PaymentRow, true));
    }

    public static function insert(PaymentRow $PaymentRow) {
        if($PaymentRow->id)
            throw new \InvalidArgumentException("Payment Row has already been inserted");
        self::insertOrUpdate($PaymentRow);
    }


    public static function update(PaymentRow $PaymentRow) {
        if(!$PaymentRow->id)
            throw new \InvalidArgumentException("Payment Row is missing an id");
        self::insertOrUpdate($PaymentRow);
    }

    public static function insertOrUpdate(PaymentRow $PaymentRow) {
        $values = array(
            ':uid' => $PaymentRow->uid,

            ':card_exp_month' => $PaymentRow->card_exp_month,
            ':card_exp_year' => $PaymentRow->card_exp_year,
            ':card_number' => self::sanitizeNumber($PaymentRow->card_number),
            ':card_type' => $PaymentRow->card_type,

            ':check_account_name' => $PaymentRow->check_account_name,
            ':check_account_type' => $PaymentRow->check_account_type,
            ':check_account_number' => self::sanitizeNumber($PaymentRow->check_account_number),
            ':check_routing_number' => $PaymentRow->check_routing_number,
            ':check_type' => $PaymentRow->check_type,
            ':check_number' => $PaymentRow->check_number,

            ':payee_id' => $PaymentRow->payee_id,

            ':status' => $PaymentRow->status,
        );

        $SQL = '';
        foreach($values as $key=>$value)
            $SQL .= ($SQL ? ',' : '') . "\n\t`" . substr($key, 1) . "` = " . $key;

        if($PaymentRow->id) {
            $values[':id'] = $PaymentRow->id;
            $SQL = "UPDATE payment SET\n" . $SQL . "\nWHERE id = :id\nLIMIT 1";
        } else {
            $SQL = "INSERT INTO payment SET `created` = NOW(),\n" . $SQL;
        }

        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare($SQL);
        $ret = $stmt->execute($values);
        if(!$ret)
            throw new \PDOException("Failed to insert new row");
        if($DB->lastInsertId())
            $PaymentRow->id = $DB->lastInsertId();
    }

    /**
     * @param $field
     * @param $value
     * @return PaymentRow
     * @throws \Exception
     */
    public static function fetchByField($field, $value) {
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare(static::SQL_SELECT . "WHERE pm.{$field} = ?");
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
     * @return PaymentRow
     */
    public static function fetchByUID($uid) {
        return static::fetchByField('uid', $uid);
    }

    /**
     * @param $id
     * @return PaymentRow
     */
    public static function fetchByID($id) {
        return static::fetchByField('id', $id);
    }

    public static function createPaymentFromOrder(OrderRow $OrderRow, PayeeRow $PayeeRow=null) {
        $PaymentRow = new PaymentRow();
        $PaymentRow->status = "Enabled";

        if($OrderRow->getCardNumber()) {
            $PaymentRow->card_number = $OrderRow->getCardNumber();
            $PaymentRow->card_exp_month = $OrderRow->getCardExpMonth();
            $PaymentRow->card_exp_year = $OrderRow->getCardExpYear();
            $PaymentRow->card_type = $OrderRow->getCardType();

        } else if(!empty($post['check_account_number'])) {
            $PaymentRow->check_account_name = $OrderRow->getCheckAccountName();
            $PaymentRow->check_account_number = $OrderRow->getCheckAccountNumber();
            $PaymentRow->check_account_type = $OrderRow->getCheckAccountType();
            $PaymentRow->check_routing_number = $OrderRow->getCheckRoutingNumber();
            $PaymentRow->check_type = $OrderRow->getCheckType();
            $PaymentRow->check_number = $OrderRow->getCheckNumber();

        } else {
            throw new \Exception("Invalid payment information");
        }

        $PaymentRow->uid = strtoupper(self::generateGUID($PaymentRow));

        if($PayeeRow) {
            $PaymentRow->payee_first_name = $PayeeRow->getPayeeFirstName();
            $PaymentRow->payee_last_name = $PayeeRow->getPayeeLastName();
            $PaymentRow->payee_address = $PayeeRow->getPayeeAddress();
            $PaymentRow->payee_address2 = $PayeeRow->getPayeeAddress2();
            $PaymentRow->payee_city = $PayeeRow->getPayeeCity();
            $PaymentRow->payee_state = $PayeeRow->getPayeeStateShort();
            $PaymentRow->payee_zipcode = $PayeeRow->getPayeeZipCode();
            $PaymentRow->payee_phone_number = $PayeeRow->getPayeePhone();
            $PaymentRow->payee_reciept_email = $PayeeRow->getPayeeEmail();
            $PaymentRow->payee_id = $PayeeRow->getID();
        }

        return $PaymentRow;
    }

    /**
     * @param PayeeRow $Payee
     * @param array $post
     * @return PaymentRow
     * @throws \Exception
     */
    public static function createPaymentFromPost(Array $post, PayeeRow $Payee=null) {

        // TODO check for duplicate/existing

        $Payee = $Payee ?: PayeeRow::createPayerFromPost($post);
        $PaymentRow = new PaymentRow();
        $PaymentRow->status = "Enabled";

        if(!empty($post['card_number'])) {
            $PaymentRow->card_number = $post['card_number'];
            $PaymentRow->card_exp_month = $post['card_exp_month'];
            $PaymentRow->card_exp_year = $post['card_exp_year'];
            $PaymentRow->card_type = self::getCCType($post['card_number']);
//            $PaymentRow->type = ''

        } else if(!empty($post['check_account_number'])) {
            $PaymentRow->check_account_name = $post['check_account_name'];
            $PaymentRow->check_account_number = $post['check_account_number'];
            $PaymentRow->check_account_type = $post['check_account_type'];
            $PaymentRow->check_routing_number = $post['check_routing_number'];
            $PaymentRow->check_type = $post['check_type'];
            $PaymentRow->check_number = $post['check_number'];

        } else {
            throw new \Exception("Invalid payment information");
        }


        if(!empty($post['payee_full_name']))
            list($post['payee_first_name'], $post['payee_last_name']) = explode(' ', $post['payee_full_name'], 2);

        $PaymentRow->payee_first_name = $Payee->getPayeeFirstName(); // $post['payee_first_name'];
        $PaymentRow->payee_last_name = $Payee->getPayeeLastName(); // $post['payee_last_name'];
        $PaymentRow->payee_phone_number = $Payee->getPayeePhone(); // @$post['payee_phone_number'];
        $PaymentRow->payee_address = $Payee->getPayeeAddress(); // $post['payee_address'];
        $PaymentRow->payee_address2 = $Payee->getPayeeAddress2(); // $post['payee_address2'];
        $PaymentRow->payee_zipcode = $Payee->getPayeeZipCode(); // $post['payee_zipcode'];
        $PaymentRow->payee_city = $Payee->getPayeeCity(); // @$post['payee_city'];
        $PaymentRow->payee_state = $Payee->getPayeeState(); // @$post['payee_state'];
        $PaymentRow->payee_reciept_email = $Payee->getPayeeEmail(); // $post['payee_reciept_email'];

        $PaymentRow->uid = strtoupper(self::generateGUID($PaymentRow));

        if($Payee && $Payee->getID())
            $PaymentRow->payee_id = $Payee->getID();

        return $PaymentRow;
    }

    static function getCCType($cardNumber) {
        $cardNumber = preg_replace('/\D/', '', $cardNumber);

        $len = strlen($cardNumber);
        if ($len < 15 || $len > 16) {
            throw new \Exception("Invalid credit card number. Length does not match");
        } else {
            switch($cardNumber) {
                case(preg_match ('/^4/', $cardNumber) >= 1):
                    return 'Visa';
                case(preg_match ('/^[2|5][1-5]/', $cardNumber) >= 1):
                    return 'MasterCard';
                case(preg_match ('/^3[47]/', $cardNumber) >= 1):
                    return 'Amex';
                case(preg_match ('/^3(?:0[0-5]|[68])/', $cardNumber) >= 1):
                    return 'Diners Club';
                case(preg_match ('/^6(?:011|5)/', $cardNumber) >= 1):
                    return 'Discover';
                case(preg_match ('/^(?:2131|1800|35\d{3})/', $cardNumber) >= 1):
                    return 'JCB';
                default:
                    throw new \Exception("Could not determine the credit card type.");
                    break;
            }
        }
    }

    public static function generateGUID(PaymentRow $Row) {
        $type = strtoupper(substr($Row->getCardType() ?: 'C', 0, 1));
        return 'P' . $type . substr(sprintf('%04X-%04X-%04X-%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479)), 2);
    }

    public static function sanitizeNumber($number, $lastDigits=4, $char='X') {
        if(!$number)
            return $number;
        $l = strlen($number);
        return str_repeat($char, $l-$lastDigits) . substr($number, -$lastDigits);
    }

    /**
     * Unit Test
     * @throws \Exception
     */
    public static function unitTest() {
        require_once '../../system/config/dbconfig.class.php';
        require_once 'payeerow.class.php';

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
        $post_cc = array(
            'card_number' => '4111111111111111',
            'card_type' => 'Visa',
            'card_exp_month' => '12',
            'card_exp_year' => '18',
        );
        $post_check = array(
            'check_account_name' => 'test',
            'check_account_number' => '123456789',
            'check_account_type' => 'Savings',
            'check_routing_number' => '123456789',
            'check_type' => 'Personal',
            'check_number' => '123',
        );

        $TestPayeeRow = PayeeRow::createPayerFromPost($post);
        PayeeRow::insert($TestPayeeRow);
        $TestPayeeRow = PayeeRow::fetchByUID($TestPayeeRow->getUID());

        $TestPaymentRow = self::createPaymentFromPost($post_cc, $TestPayeeRow);
        self::insertOrUpdate($TestPaymentRow);
        $TestPaymentRow = PaymentRow::fetchByUID($TestPaymentRow->getUID());
        $TestPaymentRow->getType() === 'Visa' || error_log(__FUNCTION__ . ": getType failed");
        self::delete($TestPaymentRow);

        $TestPaymentRow = self::createPaymentFromPost($post_check, $TestPayeeRow);
        self::insertOrUpdate($TestPaymentRow);
        $TestPaymentRow = PaymentRow::fetchByUID($TestPaymentRow->getUID());
        $TestPaymentRow->getType() === 'Check' || error_log(__FUNCTION__ . ": getType failed");
        self::delete($TestPaymentRow);

        PayeeRow::delete($TestPayeeRow);
    }
}

if(isset($argv) && in_array(@$argv[1], array('test-payment', 'test-all')))
    PaymentRow::unitTest();

