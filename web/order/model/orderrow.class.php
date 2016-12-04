<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/28/2016
 * Time: 1:32 PM
 */
namespace Order\Model;

use System\Config\DBConfig;
use System\Config\SiteConfig;
use Integration\Model\AbstractMerchantIdentity;
use Integration\Model\Ex\IntegrationException;
use Order\Model\TransactionRow;
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
    protected $payee_address;
    protected $payee_address2;
    protected $payee_zipcode;
    protected $payee_city;
    protected $payee_state;

    protected $status;
    protected $total_returned_amount;
    protected $total_returned_service_fee;
    protected $convenience_fee;
    protected $username;
    protected $merchant_id;
    protected $integration_id;
    protected $subscription_id;
    protected $batch_id;

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
i.name integration_name
FROM order_item oi
LEFT JOIN subscription s on oi.id = s.order_item_id
LEFT JOIN merchant m on oi.merchant_id = m.id
LEFT JOIN integration i on oi.integration_id = i.id
";
    const SQL_GROUP_BY = "\nGROUP BY oi.id";
    const SQL_ORDER_BY = "\nORDER BY oi.id DESC";


    public function getID()                 { return $this->id; }

    public function getUID($truncated=false){
        if(!$truncated)
            return $this->uid;
        return '...' . strrchr($this->uid, '-');
    }
    public function getAmount()             { return $this->amount; }
    public function getStatus()             { return $this->status; }
    public function getDate()               { return $this->date; }
    public function getInvoiceNumber()      { return $this->invoice_number; }
    public function getCustomerID()         { return $this->customer_id; }
    public function getCustomerFirstName()  { return $this->customer_first_name; }
    public function getCustomerLastName()   { return $this->customer_last_name; }
    public function getCustomerFullName()   { return $this->customer_first_name . ' ' . $this->customer_last_name; }
    public function getPayeeAddress()       { return $this->payee_address; }
    public function getPayeeAddress2()      { return $this->payee_address2; }
    public function getPayeeZipCode()       { return $this->payee_zipcode; }
    public function getPayeeCity()          { return $this->payee_city; }
    public function getPayeeState()         { return $this->payee_state; }
    public function getPayeeEmail()         { return $this->payee_reciept_email; }
    public function getPayeePhone()         { return $this->payee_phone_number; }
    public function getUsername()           { return $this->username; }
    public function getCardHolderFullName() { return $this->customer_first_name . ' ' . $this->customer_last_name; }
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

    public function getMerchantID()         { return $this->merchant_id; }
    public function getIntegrationID()      { return $this->integration_id; }
    public function getIntegrationName()    { return $this->integration_name; }
    public function getOrderItemID()        { return $this->order_item_id; }

    public function getConvenienceFee()     { return $this->convenience_fee; }
    public function getEntryMode()          { return $this->entry_mode; }

    public function setStatus($status)      { $this->status = $status; }

    public function setPayeeEmail($email)   { $this->payee_reciept_email = $email; }

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

    public function setSubscriptionID($order_item_id) {
        $this->subscription_id = $order_item_id;
    }

    public function getBatchID()            { return $this->batch_id; }
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

    // Static


    const STAT_AMOUNT_TOTAL = 'amount_total';
    const STAT_DAILY = 'daily';
    const STAT_WEEK_TO_DATE = 'wtd';
    const STAT_WEEKLY = 'weekly';
    const STAT_MONTH_TO_DATE = 'mtd';
    const STAT_MONTHLY = 'monthly';

    public static function queryMerchantStats(UserRow $SessionUser=null, $offset=null) {

        $year_to_date = date('Y-01-01');
        $yearly  = date('Y-m-d', time() - 24*60*60*365 + $offset);

        $month_to_date = date('Y-m-01');
        $monthly  = date('Y-m-d', time() - 24*60*60*30 + $offset);

        $week_to_date = date('Y-m-d', time() - 24*60*60*date('w') + $offset);
        $weekly  = date('Y-m-d', time() - 24*60*60*7 + $offset);

        $today = date('Y-m-d', time() + $offset);

        $SQL = <<<SQL
SELECT
	SUM(amount) as amount_total,

	SUM(CASE WHEN date>='{$year_to_date}' THEN amount ELSE 0 END) as year_to_date,
	SUM(CASE WHEN date>='{$year_to_date}' THEN 1 ELSE 0 END) as year_to_date_count,
	SUM(CASE WHEN date>='{$yearly}' THEN amount ELSE 0 END) as yearly,
    SUM(CASE WHEN date>='{$yearly}' THEN 1 ELSE 0 END) as yearly_count,

	SUM(CASE WHEN date>='{$month_to_date}' THEN amount ELSE 0 END) as month_to_date,
	SUM(CASE WHEN date>='{$month_to_date}' THEN 1 ELSE 0 END) as month_to_date_count,
	SUM(CASE WHEN date>='{$monthly}' THEN amount ELSE 0 END) as monthly,
	SUM(CASE WHEN date>='{$monthly}' THEN 1 ELSE 0 END) as monthly_count,

	SUM(CASE WHEN date>='{$week_to_date}' THEN amount ELSE 0 END) as week_to_date,
	SUM(CASE WHEN date>='{$week_to_date}' THEN 1 ELSE 0 END) as week_to_date_count,
	SUM(CASE WHEN date>='{$weekly}' THEN amount ELSE 0 END) as weekly,
	SUM(CASE WHEN date>='{$weekly}' THEN 1 ELSE 0 END) as weekly_count,

	SUM(CASE WHEN date>='{$today}' THEN amount ELSE 0 END) as today,
	SUM(CASE WHEN date>='{$today}' THEN 1 ELSE 0 END) as today_count,

    SUM(convenience_fee) as fee_total

 FROM order_item oi

 WHERE status in ('Settled', 'Authorized')
SQL;

        if($SessionUser) {
            $ids = $SessionUser->getMerchantList() ?: array(-1);
            $SQL .= "\nAND oi.merchant_id IN (" . implode(', ', $ids) . ")";
//            $SQL .= "\nAND oi.merchant_id = (SELECT um.id_merchant FROM user_merchants um WHERE um.id_user = " . intval($userID) . " AND um.id_merchant = oi.merchant_id)";
        }

        $duration = -microtime(true);
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare($SQL);
        $stmt->execute();
        $stats = $stmt->fetch();
        $duration += microtime(true);
        $stats['duration'] = $duration;

        return $stats;
    }

    public static function delete(OrderRow $OrderRow) {
        $SQL = "DELETE FROM order_item WHERE id = ?";
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare($SQL);
        $ret = $stmt->execute(array($OrderRow->getID()));
        if(!$ret)
            throw new \PDOException("Failed to delete row");
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
            ':subscription_id' => $OrderRow->subscription_id,
            ':batch_id' => $OrderRow->batch_id,
            ':version' => $OrderRow->version,
            ':amount' => $OrderRow->amount,
            ':card_exp_month' => $OrderRow->card_exp_month,
            ':card_exp_year' => $OrderRow->card_exp_year,
            ':card_number' => self::sanitizeNumber($OrderRow->card_number),
            ':card_type' => $OrderRow->card_type,
            ':check_account_name' => $OrderRow->check_account_name,
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
            ':order_item_id' => $OrderRow->order_item_id,
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
            $SQL = "INSERT INTO order_item\nSET `date` = NOW(), " . $SQL;
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
     * @param AbstractMerchantIdentity $MerchantIdentity
     * @param array $post
     * @return OrderRow
     * @throws IntegrationException
     */
    public static function createOrderFromPost(AbstractMerchantIdentity $MerchantIdentity, Array $post) {
        $Merchant = $MerchantIdentity->getMerchantRow();

        $OrderRow = new OrderRow();
        if(!empty($post['order_id'])) {
            $OrderRow = $OrderRow::fetchByID($post['order_id']);
        } else {
            $OrderRow->uid = strtolower(self::generateGUID());
            $OrderRow->version = 10;
            $OrderRow->status = "Pending";
        }
        $OrderRow->merchant_id = $Merchant->getID();
        $OrderRow->integration_id = $MerchantIdentity->getIntegrationRow()->getID();
        if($post['merchant_id'] !== $Merchant->getID())
            throw new IntegrationException("Merchant id mismatch");

        if(empty($post['amount']))
            throw new IntegrationException("Invalid Amount");
        if(!is_numeric($post['amount']))
            throw new IntegrationException("Invalid Numeric Amount");
        if($post['amount'] > SiteConfig::$MAX_TRANSACTION_AMOUNT)
            throw new IntegrationException("Invalid Max Transaction Amount");

//        $OrderRow->date = ;
        $OrderRow->entry_mode = $post['entry_mode'];
        $OrderRow->amount = $post['amount'];
        $OrderRow->convenience_fee = $MerchantIdentity->calculateConvenienceFee($OrderRow);
        $OrderRow->order_item_id = rand(1999,9999); // TODO: fix?

        if(in_array(strtolower($post['entry_mode']), array('keyed', 'swipe'))) {
            $OrderRow->card_track = trim($post['card_track']);
            $OrderRow->card_exp_month = $post['card_exp_month'];
            $OrderRow->card_exp_year = $post['card_exp_year'];
            $OrderRow->card_type = self::getCCType($post['card_number']);
            $OrderRow->card_number = $post['card_number'];

        } else if(strtolower($post['entry_mode']) === 'check') {
            $OrderRow->check_account_name = $post['check_account_name'];
            $OrderRow->check_account_number = $post['check_account_number'];
            $OrderRow->check_account_type = $post['check_account_type'];
            $OrderRow->check_routing_number = $post['check_routing_number'];
            $OrderRow->check_type = $post['check_type'];
            $OrderRow->check_number = $post['check_number'];

        } else {
            throw new IntegrationException("Invalid entry_mode");
        }

        $OrderRow->customer_first_name = @$post['customer_first_name'];
        $OrderRow->customer_last_name = @$post['customer_last_name'];
        $OrderRow->customermi = @$post['customermi'];
        $OrderRow->customer_id = $post['customer_id'];

        $OrderRow->invoice_number = $post['invoice_number'];

        $OrderRow->payee_first_name = $post['payee_first_name'];
        $OrderRow->payee_last_name = $post['payee_last_name'];
        $OrderRow->payee_phone_number = $post['payee_phone_number'];
        $OrderRow->payee_address = $post['payee_address'];
        $OrderRow->payee_address2 = $post['payee_address2'];

        $OrderRow->payee_zipcode = $post['payee_zipcode'];
        $OrderRow->payee_city = @$post['payee_city'];
        $OrderRow->payee_state = @$post['payee_state'];

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
                    throw new IntegrationException("Could not determine the credit card type.");
                    break;
            }
        }
    }

    public static function generateGUID() {
        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }

    public static function sanitizeNumber($number, $lastDigits=4, $char='X') {
        if(!$number)
            return $number;
        $l = strlen($number);
        return str_repeat($char, $l-$lastDigits) . substr($number, -$lastDigits);
    }



}

