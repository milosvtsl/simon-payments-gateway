<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/28/2016
 * Time: 1:32 PM
 */
namespace Order\Model;

use Integration\Mock\MockMerchantIdentity;
use Integration\Model\AbstractMerchantIdentity;
use Integration\Model\Ex\FraudException;
use Merchant\Model\MerchantFormRow;
use Payment\Model\PaymentRow;
use System\Config\DBConfig;
use System\Config\SiteConfig;
use User\Model\UserRow;

class OrderRow
{
    const _CLASS = __CLASS__;

    const ENUM_ENTRY_MODE_KEYED = "Keyed";
    const ENUM_ENTRY_MODE_SWIPE = "Swipe";
    const ENUM_ENTRY_MODE_CHECK = "Check";


    const ENUM_RUN_FREQUENCY_ONETIMEFUTURE = "OneTimeFuture";
    const ENUM_RUN_FREQUENCY_DAILY = "Daily";
    const ENUM_RUN_FREQUENCY_WEEKLY = "Weekly";
    const ENUM_RUN_FREQUENCY_BIWEEKLY = "BiWeekly";
    const ENUM_RUN_FREQUENCY_MONTHLY = "Monthly";
    const ENUM_RUN_FREQUENCY_BIMONTHLY = "BiMonthly";
    const ENUM_RUN_FREQUENCY_QUARTERLY = "Quarterly";
    const ENUM_RUN_FREQUENCY_SEMIANNUALLY = "SemiAnnually";
    const ENUM_RUN_FREQUENCY_YEARLY = "Yearly";

    public static $ENUM_RUN_FREQUENCY = array(
        self::ENUM_RUN_FREQUENCY_ONETIMEFUTURE  => "Once",
//        self::ENUM_RUN_FREQUENCY_DAILY          => "Daily",
        self::ENUM_RUN_FREQUENCY_WEEKLY         => "Weekly",
        self::ENUM_RUN_FREQUENCY_BIWEEKLY       => "Bi-Weekly",
        self::ENUM_RUN_FREQUENCY_MONTHLY        => "Monthly",
        self::ENUM_RUN_FREQUENCY_BIMONTHLY      => "Bi-monthly",
        self::ENUM_RUN_FREQUENCY_QUARTERLY      => "Quarterly",
        self::ENUM_RUN_FREQUENCY_SEMIANNUALLY   => "Semi-Annually",
        self::ENUM_RUN_FREQUENCY_YEARLY         => "Yearly",
    );

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
    protected $check_account_bank_name;
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
//    protected $order_item_id;

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

    /** @var PaymentRow */
    protected $payment;
    protected $payment_id;


    protected $status;
    protected $total_returned_amount;
    protected $total_returned_service_fee;
    protected $convenience_fee;
    protected $username;
    protected $merchant_id;
    protected $integration_id;
    protected $integration_remote_id;
    protected $subscription_id;
    protected $batch_id;
    protected $form_id;

    // Table order_field
    protected $order_fields;

    // Table integration
    protected $integration_name;

    // Table merchant
    protected $merchant_short_name;

    // Table subscription
    protected $subscription_uid;
    protected $subscription_status;
    protected $subscription_status_message;
    protected $subscription_recur_amount;
    protected $subscription_recur_count;
    protected $subscription_recur_next_date;
    protected $subscription_recur_cancel_date;

    protected $subscription_recur_frequency;
    const SQL_SELECT = "
SELECT oi.*,
s.id as subscription_id,
s.uid as subscription_uid,
s.status as subscription_status,
s.status_message as subscription_status_message,
s.recur_amount as subscription_recur_amount,
s.recur_count as subscription_recur_count,
s.recur_next_date as subscription_recur_next_date,
s.recur_frequency as subscription_recur_frequency,

m.short_name as merchant_short_name,
i.name integration_name,
(SELECT GROUP_CONCAT(
  CONCAT_WS(':', of.field_name, of.field_value) SEPARATOR '|')
  FROM order_field of
  WHERE oi.id = of.order_id
) as order_fields,
st.name as payee_state_full


FROM order_item oi
LEFT JOIN subscription s on oi.id = s.order_item_id
LEFT JOIN merchant m on oi.merchant_id = m.id
LEFT JOIN integration i on oi.integration_id = i.id
LEFT JOIN state st on st.short_code = oi.payee_state
";
    const SQL_GROUP_BY = "\nGROUP BY oi.id";
    const SQL_WHERE = "\nWHERE 1";
    const SQL_ORDER_BY = "\nORDER BY oi.id ASC";

    public function getID()                 { return $this->id; }


    public function getUID(){
        return $this->uid;
    }

    public function getAmount()             { return $this->amount; }
    public function getTotalReturnedAmount()    { return $this->total_returned_amount; }

    /** @return \DateTime */
    public function getDate($timezone=null) {
        $dt = new \DateTime($this->date, new \DateTimeZone('UTC'));
        if($timezone) {
            $tz = new \DateTimeZone($timezone);
            $dt->setTimezone($tz);
        }
        return $dt;
    }

    public function getStatus()             { return $this->status; }
    public function getInvoiceNumber()      { return $this->invoice_number; }
    public function getCustomerID()         { return $this->customer_id; }
    public function getCustomerFirstName()  { return $this->customer_first_name; }
    public function getCustomerLastName()   { return $this->customer_last_name; }
    public function getCustomerFullName()   { return trim($this->customer_first_name . ' ' . $this->customer_last_name); }

    public function getPayeeFirstName()     { return $this->payee_first_name; }
    public function getPayeeLastName()      { return $this->payee_last_name; }
    public function getPayeeFullName()      { return trim($this->payee_first_name . ' '. $this->payee_last_name); }

    public function getPayeeAddress()       { return $this->payee_address; }
    public function getPayeeAddress2()      { return $this->payee_address2; }
    public function getPayeeZipCode()       { return $this->payee_zipcode; }
    public function getPayeeCity()          { return $this->payee_city; }
    public function getPayeeState()         { return $this->payee_state; }
    public function getPayeeCountry()       { return "USA"; }
    public function getPayeeEmail()         { return $this->payee_reciept_email; }
    public function getPayeePhone()         { return $this->payee_phone_number; }
    public function getUsername()           { return $this->username; }
    public function getCardHolderFullName() { return $this->getPayeeFullName() ?: $this->getCustomerFullName(); }
    public function getMerchantShortName()  { return $this->merchant_short_name; }

    public function getCardExpMonth()       { return $this->card_exp_month; }
    public function getCardExpYear()        { return $this->card_exp_year; }
    public function getCardType()           { return $this->card_type; }
    public function getCardNumber()         { return $this->card_number; }
    public function getCardNumberTruncated(){ return substr($this->card_number, -4); }

    public function getCardTrack()          { return $this->card_track; }
    public function getCheckAccountName()   { return $this->check_account_name; }
    public function getCheckAccountNumber() { return $this->check_account_number; }
    public function getCheckAccountBank()   { return $this->check_account_bank_name; }
    public function getCheckAccountType()   { return $this->check_account_type; }
    public function getCheckRoutingNumber() { return $this->check_routing_number; }
    public function getCheckNumber()        { return $this->check_number; }

    public function getCheckType()          { return $this->check_type; }
    public function getMerchantID()         { return $this->merchant_id; }
    public function getIntegrationID()      { return $this->integration_id; }
    public function getIntegrationName()    { return $this->integration_name; }
    public function getFormID()             { return $this->form_id; }

//    public function getOrderItemID()        { return $this->order_item_id; }
    public function getConvenienceFee()     { return $this->convenience_fee; }

    public function getEntryMode()          { return $this->entry_mode; }

    public function setStatus($status)      { $this->status = $status; }

    public function setPayeeEmail($email)   { $this->payee_reciept_email = $email; }

    public function setTotalReturnedAmount($total_returned_amount) {
        $this->total_returned_amount = $total_returned_amount;
    }
    public function getReferenceNumber() {
        return strtoupper($this->uid);
    }
    public function getSubscriptionID()         { return $this->subscription_id; }
    public function getSubscriptionUID()        { return $this->subscription_uid; }
    public function getSubscriptionStatus()     { return $this->subscription_status; }
    public function getSubscriptionMessage()    { return $this->subscription_status_message; }
    public function getSubscriptionAmount()     { return $this->subscription_recur_amount; }
    public function getSubscriptionCount()      { return $this->subscription_recur_count; }
    public function getSubscriptionNextDate()   { return $this->subscription_recur_next_date; }

    public function getSubscriptionCancelDate() { return $this->subscription_recur_cancel_date; }

    public function getSubscriptionFrequency()  { return $this->subscription_recur_frequency; }
    public function setSubscriptionID($subscription_id) {
        $this->subscription_id = $subscription_id;
    }

    public function setIntegrationRemoteID($remoteID) {
        $this->integration_remote_id = $remoteID;
    }

    public function getIntegrationRemoteID() {
        return $this->integration_remote_id;
    }

    public function getBatchID()            { return $this->batch_id; }

    public function getCustomFieldValues() {
        if(!is_array($this->order_fields)) {
            $arr = array();
            $split = explode('|', $this->order_fields);
            foreach($split as $pair) {
                $pair = explode(':', $pair);
                if(sizeof($pair) < 2)
                    continue;
                $arr[$pair[0]] = $pair[1];
            }
            $this->order_fields = $arr;
        }
        return $this->order_fields;
    }


    public function setBatchID($batch_id)   { $this->batch_id = $batch_id; }

    public function calculateCurrentBatchID($time=null) {
        $DB = DBConfig::getInstance();

        $batch_date = date('Y-m-d', $time);

        $sql = <<<SQL
SELECT MAX(oi.batch_id)
FROM paylogic.order_item oi
WHERE oi.date > ?
AND oi.merchant_id = ?
SQL;
        $stmt = $DB->prepare($sql);
        $stmt->execute(array($batch_date, $this->merchant_id));
        $batch_id = $stmt->fetchColumn(0);

        if(!$batch_id) {
            $sql = <<<SQL
SELECT MAX(oi.batch_id)
FROM paylogic.order_item oi
WHERE oi.merchant_id = ?
SQL;
            $stmt = $DB->prepare($sql);
            $stmt->execute(array($this->merchant_id));
            $batch_id = $stmt->fetchColumn(0);
            $batch_id += 1; // Increase batch id for new day
        }

        return $batch_id;
    }

    /**
     * Return the first authorized transaction for this order
     * @return TransactionRow
     * @throws \Exception
     */
    public function fetchAuthorizedTransaction() {
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare(TransactionRow::SQL_SELECT . "WHERE oi.id = ? AND action = ?");
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $stmt->setFetchMode(\PDO::FETCH_CLASS, TransactionRow::_CLASS);
        $stmt->execute(array($this->getID(), 'Authorized'));
        $AuthorizedTransaction = $stmt->fetch();
        return $AuthorizedTransaction;
    }
    public function performFraudScrubbing(AbstractMerchantIdentity $MerchantIdentity, UserRow $SessionUser, Array $post)
    {
        $Merchant = $MerchantIdentity->getMerchantRow();
        $amount = $this->getAmount();

        $max = $Merchant->getFraudHighLimit();
        if($max !== null)
            if($amount > floatval($max))
                throw new FraudException("Order is above High Limit ($amount > $max)");

        $min = $Merchant->getFraudLowLimit();
        if($min !== null && floatval($min) >= 0.01)
            if($amount < floatval($min))
                throw new FraudException("Order is below Low Limit ($amount < $min)");

    }


    public function insertCustomField($field, $value) {
        OrderFieldRow::insertOrUpdate($this, $field, $value);
    }

    /**
     * @return PaymentRow
     */
    public function getPaymentInformation() {
        if($this->payment)
            return $this->payment;

        if($this->payment_id)
            return $this->payment = PaymentRow::fetchByID($this->payment_id);

        $post = array();
        foreach($this as $key => $val)
            $post[$key] = $val;
        $this->payment = PaymentRow::createPaymentFromPost($post);
        return $this->payment;
    }

    // Static

    const STAT_AMOUNT_TOTAL = 'amount_total';

    const STAT_DAILY = 'daily';

    const STAT_WEEK_TO_DATE = 'wtd';

    const STAT_WEEKLY = 'weekly';

    const STAT_MONTH_TO_DATE = 'mtd';

    const STAT_MONTHLY = 'monthly';

    public static function delete(OrderRow $OrderRow) {
        $SQL = "DELETE FROM order_item WHERE id = ?";
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare($SQL);
        $ret = $stmt->execute(array($OrderRow->getID()));
        if(!$ret)
            throw new \PDOException("Failed to delete row");
        if($stmt->rowCount() === 0)
            error_log("Failed to delete row: " . print_r($OrderRow, true));
    }


    public static function insert(OrderRow $OrderRow) {
        if($OrderRow->id)
            throw new \InvalidArgumentException("Order Row has already been inserted");
        self::insertOrUpdate($OrderRow);
    }


    public static function update(OrderRow $OrderRow) {
        if(!$OrderRow->id)
            throw new \InvalidArgumentException("Order Row is missing an id");
        self::insertOrUpdate($OrderRow);
    }

    public static function insertOrUpdate(OrderRow $OrderRow) {
        $values = array(
            ':uid' => $OrderRow->uid,
            ':merchant_id' => $OrderRow->merchant_id,
            ':integration_id' => $OrderRow->integration_id,
            ':integration_remote_id' => $OrderRow->integration_remote_id,
            ':subscription_id' => $OrderRow->subscription_id,
            ':batch_id' => $OrderRow->batch_id,
            ':form_id' => $OrderRow->form_id,
//            ':version' => $OrderRow->version,
            ':amount' => $OrderRow->amount,
            ':card_exp_month' => $OrderRow->card_exp_month,
            ':card_exp_year' => $OrderRow->card_exp_year,
            ':card_number' => self::sanitizeNumber($OrderRow->card_number),
            ':card_type' => $OrderRow->card_type,
            ':check_account_name' => $OrderRow->check_account_name,
            ':check_account_bank_name' => $OrderRow->check_account_bank_name,
            ':check_account_type' => $OrderRow->check_account_type,
            ':check_account_number' => self::sanitizeNumber($OrderRow->check_account_number),
            ':check_routing_number' => $OrderRow->check_routing_number,
            ':check_type' => $OrderRow->check_type,
            ':check_number' => $OrderRow->check_number,
            ':customer_first_name' => $OrderRow->customer_first_name,
            ':customer_last_name' => $OrderRow->customer_last_name,
            ':customermi' => $OrderRow->customermi,
            ':entry_mode' => $OrderRow->entry_mode,
            ':invoice_number' => $OrderRow->invoice_number,
//            ':order_item_id' => $OrderRow->order_item_id,
            ':payee_first_name' => $OrderRow->payee_first_name,
            ':payee_last_name' => $OrderRow->payee_last_name,
            ':payee_phone_number' => $OrderRow->payee_phone_number,
            ':payee_reciept_email' => $OrderRow->payee_reciept_email,
            ':payee_address' => $OrderRow->payee_address,
            ':payee_address2' => $OrderRow->payee_address2,

            ':payee_zipcode' => $OrderRow->payee_zipcode,
            ':payee_city' => $OrderRow->payee_city,
            ':payee_state' => $OrderRow->payee_state,

            ':status' => $OrderRow->status,
            ':convenience_fee' => $OrderRow->convenience_fee ?: 0,
            ':total_returned_amount' => $OrderRow->total_returned_amount ?: 0,
            ':total_returned_service_fee' => $OrderRow->total_returned_service_fee ?: 0,
            ':username' => $OrderRow->username ?: '',
        );
        $SQL = ''; // "INSERT INTO order_item\nSET";
        foreach($values as $key=>$value)
            $SQL .= ($SQL ? ',' : '') . "\n\t`" . substr($key, 1) . "` = " . $key;
//        $SQL .= "\n\t`date` = NOW()";
//        $OrderRow->date = date('Y-m-d G:i:s');

        if($OrderRow->id) {
            $SQL = "UPDATE order_item\nSET" . $SQL . "\nWHERE id = " . $OrderRow->id . "\nLIMIT 1";
        } else {
            $SQL = "INSERT INTO order_item\nSET `date` = UTC_TIMESTAMP(), " . $SQL;
        }

        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare($SQL);
        $ret = $stmt->execute($values);
        if(!$ret)
            throw new \PDOException("Failed to insert new row");
        if($DB->lastInsertId())
            $OrderRow->id = $DB->lastInsertId();
    }

    /**
     * @param $field
     * @param $value
     * @return OrderRow
     * @throws \Exception
     */
    public static function fetchByField($field, $value) {
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare(static::SQL_SELECT . "WHERE oi.{$field} = ?");
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
     * @return OrderRow
     */
    public static function fetchByUID($uid) {
        return static::fetchByField('uid', $uid);
    }

    /**
     * @param $id
     * @return OrderRow
     */
    public static function fetchByID($id) {
        return static::fetchByField('id', $id);
    }

    /**
     * Create a new order, optionally set up a new payment entry with the remote integration
     * @param AbstractMerchantIdentity $MerchantIdentity
     * @param PaymentRow $PaymentInfo
     * @param MerchantFormRow $OrderForm
     * @param array $post Order Information
     * @return OrderRow
     */
    static function createNewOrder(AbstractMerchantIdentity $MerchantIdentity, PaymentRow $PaymentInfo, MerchantFormRow $OrderForm, Array $post) {

        $OrderRow = new OrderRow();
//        $OrderRow->version = 10;
        $OrderRow->status = "Pending";

        if($PaymentInfo->getID())
            $OrderRow->payment_id = $PaymentInfo;

        $OrderRow->form_id = $OrderForm->getID();
        $OrderRow->merchant_id = $MerchantIdentity->getMerchantRow()->getID();
        $OrderRow->integration_id = $MerchantIdentity->getIntegrationRow()->getID();

        if(empty($post['amount']))
            throw new \InvalidArgumentException("Invalid Amount");
        if(!is_numeric($post['amount']))
            throw new \InvalidArgumentException("Invalid Numeric Amount");
        if(SiteConfig::$SITE_MAX_TRANSACTION_AMOUNT>100 && $post['amount'] > SiteConfig::$SITE_MAX_TRANSACTION_AMOUNT)
            throw new \InvalidArgumentException("Invalid Max Transaction Amount: " . SiteConfig::$SITE_MAX_TRANSACTION_AMOUNT);

        $OrderRow->entry_mode = $post['entry_mode'];
        $OrderRow->amount = $post['amount'];
        $OrderRow->convenience_fee = $MerchantIdentity->calculateConvenienceFee($OrderRow);
//        $OrderRow->order_item_id = rand(1999,9999); // TODO: fix?

        if(in_array(strtolower($post['entry_mode']), array('keyed', 'swipe'))) {
            $OrderRow->card_track = trim(@$post['card_track']);
            $OrderRow->card_exp_month = $post['card_exp_month'] ?: $PaymentInfo->getCardExpMonth();
            $OrderRow->card_exp_year = $post['card_exp_year'] ?: $PaymentInfo->getCardExpYear();
            $OrderRow->card_number = $post['card_number'] ?: $PaymentInfo->getCardNumber();
            $OrderRow->card_type = self::getCCType($OrderRow->card_number);

        } else if(strtolower($post['entry_mode']) === 'check') {
            $OrderRow->check_account_name = $post['check_account_name']  ?: $PaymentInfo->getCheckAccountName();
            $OrderRow->check_account_bank_name = @$post['check_account_bank_name']  ?: $PaymentInfo->getCheckAccountBank();
            $OrderRow->check_account_number = $post['check_account_number'] ?: $PaymentInfo->getCheckAccountNumber();
            $OrderRow->check_account_type = $post['check_account_type'] ?: $PaymentInfo->getCheckAccountType();
            $OrderRow->check_routing_number = $post['check_routing_number'] ?: $PaymentInfo->getCheckRoutingNumber();
            $OrderRow->check_type = $post['check_type'] ?: $PaymentInfo->getCheckType();
            $OrderRow->check_number = $post['check_number'] ?: $PaymentInfo->getCheckNumber();

        } else {
            throw new \InvalidArgumentException("Invalid entry_mode");
        }

        if(!empty($post['payee_full_name']))
            list($post['payee_first_name'], $post['payee_last_name']) = explode(' ', $post['payee_full_name'], 2);

        $OrderRow->payee_first_name = $post['payee_first_name'] ?: $PaymentInfo->getPayeeFirstName();
        $OrderRow->payee_last_name = $post['payee_last_name'] ?: $PaymentInfo->getPayeeLastName();
        $OrderRow->payee_phone_number = @$post['payee_phone_number'] ?: $PaymentInfo->getPayeePhone();
        $OrderRow->payee_address = $post['payee_address'] ?: $PaymentInfo->getPayeeAddress();
        $OrderRow->payee_address2 = $post['payee_address2'] ?: $PaymentInfo->getPayeeAddress2();

        $OrderRow->payee_zipcode = $post['payee_zipcode'] ?: $PaymentInfo->getPayeeZipCode();
        $OrderRow->payee_city = @$post['payee_city'] ?: $PaymentInfo->getPayeeCity();
        $OrderRow->payee_state = @$post['payee_state'] ?: $PaymentInfo->getPayeeState();


        $OrderRow->customer_first_name = @$post['customer_first_name'] ?: $OrderRow->payee_first_name;
        $OrderRow->customer_last_name = @$post['customer_last_name'] ?: $OrderRow->payee_last_name;
        $OrderRow->customermi = @$post['customermi'];
        $OrderRow->customer_id = @$post['customer_id'];

        $OrderRow->invoice_number = @$post['invoice_number'];

        if(isset($post['payee_reciept_email']))
            $OrderRow->payee_reciept_email = $post['payee_reciept_email'];
        if(isset($post['username']))
            $OrderRow->username = $post['username'];

        if ($OrderRow->payee_reciept_email && !filter_var($OrderRow->payee_reciept_email, FILTER_VALIDATE_EMAIL))
            throw new \InvalidArgumentException("Invalid Payee Email Format");


        if(!$OrderRow->uid)
            $OrderRow->uid = strtoupper(self::generateGUID($OrderRow));

        return $OrderRow;
    }

    static function getCCType($cardNumber, $throwException=true) {
        $cardNumber = preg_replace('/\D/', '', $cardNumber);

        $len = strlen($cardNumber);
        if ($len < 15 || $len > 16) {
            if($throwException)
                throw new \InvalidArgumentException("Invalid credit card number. Length does not match");
            return null;
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
                    if($throwException)
                        throw new \InvalidArgumentException("Could not determine the credit card type.");
                    return null;
            }
        }
    }


    public static function generateGUID(OrderRow $Row=null) {
        $site_type = SiteConfig::$SITE_UID_PREFIX;
        $type = $Row ? strtoupper(substr($Row->getEntryMode(), 0, 1)) : 'E';
        return $site_type . 'O' . $type . '-' . sprintf('%04X-%04X-%04X-%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479));
    }

    public static function sanitizeNumber($number, $lastDigits=4, $char='X') {
        if(!$number)
            return $number;
        $l = strlen($number);
        return str_repeat($char, $l-$lastDigits) . substr($number, -$lastDigits);
    }

    /**
     * Unit Test
     * @throws \InvalidArgumentException
     */
    public static function unitTest() {
        // Go up 2 directories
        $cwd = getcwd();
        chdir(__DIR__ . '/../..');

        // Enable class autoloader for this page instance
        spl_autoload_extensions('.class.php');
        spl_autoload_register();

        $MockMerchantIdentity = new MockMerchantIdentity();

        $post = array(
            'amount' => 0.01,

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
            'entry_mode' => 'Keyed',
            'card_number' => '4111111111111111',
            'card_type' => 'Visa',
            'card_exp_month' => '12',
            'card_exp_year' => '18',
        );
        $post_check = array(
            'entry_mode' => 'Check',
            'check_account_name' => 'test',
            'check_account_number' => '123456789',
            'check_account_type' => 'Savings',
            'check_routing_number' => '123456789',
            'check_type' => 'Personal',
            'check_number' => '123',
        );

        $TestPaymentInfo = PaymentRow::createPaymentFromPost($post + $post_cc);

        $TestOrderRow = OrderRow::createNewOrder($MockMerchantIdentity, $TestPaymentInfo, $TestForm, $post + $post_cc);
        self::insert($TestOrderRow);
        $TestOrderRow = OrderRow::fetchByUID($TestOrderRow->getUID());
        OrderRow::delete($TestOrderRow);

        $TestPaymentInfo = PaymentRow::createPaymentFromPost($post + $post_check);

        $TestOrderRow = OrderRow::createNewOrder($MockMerchantIdentity, $TestPaymentInfo, $TestForm, $post + $post_check);
        self::insert($TestOrderRow);
        $TestOrderRow = OrderRow::fetchByUID($TestOrderRow->getUID());
        OrderRow::delete($TestOrderRow);

        $TestPaymentInfo = $TestOrderRow->getPaymentInformation();
        PaymentRow::insertOrUpdate($TestPaymentInfo);
        $TestOrderRow->getPaymentInformation()->getID() > 0 || error_log(__FUNCTION__ . ": getPaymentInformation()->getID() failed");

//        print_r($TestPaymentInfo);

        chdir($cwd);
    }
}

//if(isset($argv) && in_array(@$argv[1], array('test-order', 'test-all')))
//    OrderRow::unitTest();

