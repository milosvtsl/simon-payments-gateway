<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/28/2016
 * Time: 1:32 PM
 */
namespace Order\Model;

use Config\DBConfig;
use Integration\Model\AbstractMerchantIdentity;
use Integration\Model\Ex\IntegrationException;
use Transaction\Model\TransactionRow;

class OrderRow
{
    const _CLASS = __CLASS__;
    const SORT_BY_ID                = 'oi.id';
    const SORT_BY_DATE              = 'oi.date';
    const SORT_BY_STATUS            = 'oi.status';
    const SORT_BY_MERCHANT_ID       = 'oi.merchant_id';
    const SORT_BY_USERNAME          = 'oi.username';
    const SORT_BY_INVOICE_NUMBER    = 'oi.invoice_number';

    public static $SORT_FIELDS = array(
        self::SORT_BY_ID,
        self::SORT_BY_DATE,
        self::SORT_BY_STATUS,
        self::SORT_BY_MERCHANT_ID,
        self::SORT_BY_USERNAME,
        self::SORT_BY_INVOICE_NUMBER,
    );

    // Table order_item
    protected $id;
    protected $uid;
    protected $amount;
    protected $date;
    protected $version;

    protected $card_exp_month;
    protected $card_exp_year;
    protected $card_number;
    protected $card_type;
    protected $card_track;

    protected $check_account_name;
    protected $check_account_number;
    protected $check_account_type;
    protected $check_routing_number;
    protected $check_type;
    protected $check_number;

    protected $customer_first_name;
    protected $customer_id;
    protected $customer_last_name;
    protected $customermi;

    protected $entry_mode;

    protected $invoice_number;
    protected $order_item_id;

    protected $payee_first_name;
    protected $payee_last_name;
    protected $payee_phone_number;
    protected $payee_reciept_email;
    protected $payee_zipcode;
    protected $status;
    protected $total_returned_amount;
    protected $total_returned_service_fee;
    protected $username;
    protected $merchant_id;

    // Table merchant
    protected $merchant_short_name;

    const SQL_SELECT = "
SELECT oi.*, m.short_name as merchant_short_name
FROM order_item oi
LEFT JOIN merchant m on oi.merchant_id = m.id
";
    const SQL_GROUP_BY = "\nGROUP BY oi.id";
    const SQL_ORDER_BY = "\nORDER BY oi.id DESC";

    public function getID()                 { return $this->id; }

    public function getUID()                { return $this->uid; }
    public function getAmount()             { return $this->amount; }
    public function getStatus()             { return $this->status; }
    public function getDate()               { return $this->date; }
    public function getInvoiceNumber()      { return $this->invoice_number; }
    public function getCustomerID()         { return $this->customer_id; }
    public function getCustomerFirstName()  { return $this->customer_first_name; }
    public function getCustomerLastName()   { return $this->customer_last_name; }
    public function getPayeeZipCode()       { return $this->payee_zipcode; }
    public function getPayeeEmail()         { return $this->payee_reciept_email; }
    public function getPayeePhone()         { return $this->payee_phone_number; }
    public function getUsername()           { return $this->username; }
    public function getCardHolderFullName()     { return $this->customer_first_name . ' ' . $this->customer_last_name; }
    public function getMerchantID()         { return $this->merchant_id; }
    public function getMerchantShortName()  { return $this->merchant_short_name; }
    public function getCardExpMonth()       { return $this->card_exp_month; }

    public function getCardExpYear()        { return $this->card_exp_year; }
    public function getCardType()           { return $this->card_type; }
    public function getCardNumber()         { return $this->card_number; }
    public function getCardTrack()          { return $this->card_track; }
    public function getCheckAccountName()   { return $this->check_account_name; }

    public function getCheckAccountNumber() { return $this->check_account_number; }
    public function getCheckAccountType()   { return $this->check_account_type; }
    public function getCheckRoutingNumber() { return $this->check_routing_number; }
    public function getCheckNumber()        { return $this->check_number; }
    public function getCheckType()          { return $this->check_type; }


    // Static


    public static function insert(OrderRow $OrderRow) {
        $values = array(
            ':uid' => $OrderRow->uid,
            ':merchant_id' => $OrderRow->merchant_id,
            ':version' => $OrderRow->version,
            ':amount' => $OrderRow->amount,
            ':card_exp_month' => $OrderRow->card_exp_month,
            ':card_exp_year' => $OrderRow->card_exp_year,
            ':card_number' => $OrderRow->card_number,
            ':card_type' => $OrderRow->card_type,
            ':check_account_name' => $OrderRow->check_account_name,
            ':check_account_type' => $OrderRow->check_account_type,
            ':check_account_number' => $OrderRow->check_account_number,
            ':check_routing_number' => $OrderRow->check_routing_number,
            ':check_type' => $OrderRow->check_type,
            ':check_number' => $OrderRow->check_number,
            ':customer_first_name' => $OrderRow->customer_first_name,
            ':customer_last_name' => $OrderRow->customer_last_name,
            ':customermi' => $OrderRow->customermi,
            ':entry_mode' => $OrderRow->entry_mode,
            ':invoice_number' => $OrderRow->invoice_number,
            ':order_item_id' => $OrderRow->order_item_id,
            ':payee_first_name' => $OrderRow->payee_first_name,
            ':payee_last_name' => $OrderRow->payee_last_name,
            ':payee_phone_number' => $OrderRow->payee_phone_number,
            ':payee_reciept_email' => $OrderRow->payee_reciept_email,
            ':payee_zipcode' => $OrderRow->payee_zipcode,
            ':status' => $OrderRow->status,
            ':total_returned_amount' => $OrderRow->total_returned_amount ?: 0,
            ':total_returned_service_fee' => $OrderRow->total_returned_service_fee ?: 0,
            ':username' => $OrderRow->username ?: '',
        );
        $SQL = "INSERT INTO order_item\nSET";
        foreach($values as $key=>$value)
            $SQL .= "\n\t`" . substr($key, 1) . "` = " . $key . ',';
        $SQL .= "\n\t`date` = NOW()";

        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare($SQL);
        $ret = $stmt->execute($values);
        if(!$ret || !$DB->lastInsertId())
            throw new \PDOException("Failed to insert new row");
        $OrderRow->id = $DB->lastInsertId();
    }

    /**
     * @param $id
     * @return OrderRow
     */
    public static function fetchByID($id) {
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare(static::SQL_SELECT . "WHERE oi.id = ?");
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $stmt->setFetchMode(\PDO::FETCH_CLASS, 'Order\Model\OrderRow');
        $stmt->execute(array($id));
        return $stmt->fetch();
    }

    /**
     * @param $uid
     * @return OrderRow
     */
    public static function fetchByUID($uid) {
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare(static::SQL_SELECT . "WHERE oi.uid = ?");
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $stmt->setFetchMode(\PDO::FETCH_CLASS, 'Order\Model\OrderRow');
        $stmt->execute(array($uid));
        return $stmt->fetch();
    }


    /**
     * @param AbstractMerchantIdentity $MerchantIdentity
     * @param array $post
     * @return OrderRow
     * @throws IntegrationException
     */
    public static function createOrderFromPost(AbstractMerchantIdentity $MerchantIdentity, Array $post) {
        $OrderRow = new OrderRow();
        $OrderRow->uid = uniqid();
        $Merchant = $MerchantIdentity->getMerchantRow();
        $OrderRow->merchant_id = $Merchant->getID();
        if($post['merchant_id'] !== $Merchant->getID())
            throw new IntegrationException("Merchant id mismatch");

        if(empty($post['amount']))
            throw new IntegrationException("Invalid Amount");

//        $OrderRow->date = ;
        $OrderRow->entry_mode = $post['entry_mode'];
        $OrderRow->amount = $post['amount'];
        $OrderRow->order_item_id = 0;

        if(in_array(strtolower($post['entry_mode']), array('keyed', 'swipe'))) {
            $OrderRow->card_track = $post['card_track'];
            $OrderRow->card_exp_month = $post['card_exp_month'];
            $OrderRow->card_exp_year = $post['card_exp_year'];
            $OrderRow->card_type = self::getCCType($post['card_number']);
            $OrderRow->card_number = $post['card_number'];

        } else if($post['entry_mode'] === 'check') {
            $OrderRow->check_account_name = $post['check_account_name'];
            $OrderRow->check_account_number = $post['check_account_number'];
            $OrderRow->check_routing_number = $post['check_routing_number'];
            $OrderRow->check_type = $post['check_type'];
            $OrderRow->check_number = $post['check_number'];

        } else {
            throw new IntegrationException("Invalid entry_mode");
        }

        $OrderRow->customer_first_name = $post['customer_first_name'];
        $OrderRow->customer_last_name = $post['customer_last_name'];
        $OrderRow->customermi = $post['customermi'];
        $OrderRow->customer_id = $post['customer_id'];

        $OrderRow->invoice_number = $post['invoice_number'];

        $OrderRow->payee_first_name = $post['payee_first_name'];
        $OrderRow->payee_last_name = $post['payee_last_name'];
        $OrderRow->payee_phone_number = $post['payee_phone_number'];
        $OrderRow->payee_zipcode = $post['payee_zipcode'];

        if(isset($post['payee_reciept_email']))
            $OrderRow->payee_reciept_email = $post['payee_reciept_email'];
        if(isset($post['username']))
            $OrderRow->username = $post['username'];

        if ($OrderRow->payee_reciept_email && !filter_var($OrderRow->payee_reciept_email, FILTER_VALIDATE_EMAIL))
            throw new IntegrationException("Invalid Email");

        return $OrderRow;
    }


    static function getCCType($cardNumber) {
        $cardNumber = preg_replace('/\D/', '', $cardNumber);

        $len = strlen($cardNumber);
        if ($len < 15 || $len > 16) {
            throw new IntegrationException("Invalid credit card number. Length does not match");
        }else{
            switch($cardNumber) {
                case(preg_match ('/^4/', $cardNumber) >= 1):
                    return 'Visa';
                case(preg_match ('/^5[1-5]/', $cardNumber) >= 1):
                    return 'Mastercard';
                case(preg_match ('/^3[47]/', $cardNumber) >= 1):
                    return 'Amex';
                case(preg_match ('/^3(?:0[0-5]|[68])/', $cardNumber) >= 1):
                    return 'Diners Club';
                case(preg_match ('/^6(?:011|5)/', $cardNumber) >= 1):
                    return 'Discover';
                case(preg_match ('/^(?:2131|1800|35\d{3})/', $cardNumber) >= 1):
                    return 'JCB';
                default:
                    throw new IntegrationException("Could not determine the credit card type.");
                    break;
            }
        }
    }
}

