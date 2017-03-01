<?php
/**
 * Created by PhpStorm.
 * Merchant: ari
 * Date: 8/28/2016
 * Time: 1:32 PM
 */
namespace Merchant\Model;

use Integration\Model\AbstractMerchantIdentity;
use Integration\Model\IntegrationRow;
use Integration\Request\Model\IntegrationRequestRow;
use System\Config\DBConfig;
use System\Config\SiteConfig;

class MerchantRow
{
    const _CLASS = __CLASS__;
    const TABLE_NAME = 'merchant';

    const FRAUD_FLAG_DUPLICATE_CARD_DAILY = 0x01;
    const FRAUD_FLAG_DUPLICATE_CARD_10MINUTE = 0x02;
    const FRAUD_FLAG_DUPLICATE_DECLINE_CARD_DAILY = 0x04;
    const FRAUD_FLAG_DUPLICATE_DECLINE_CARD_10MINUTE = 0x08;

    const SORT_BY_ID = 'm.id';
    const LOGO_PATH = 'upload/logo/';

    public static $FRAUD_FLAG_DESCRIPTIONS = array(
        self::FRAUD_FLAG_DUPLICATE_CARD_DAILY => "Duplicate Approves (Same Day)",
        self::FRAUD_FLAG_DUPLICATE_CARD_10MINUTE => "Duplicate Approves (10 minutes)",
        self::FRAUD_FLAG_DUPLICATE_DECLINE_CARD_DAILY => "Duplicate Declines (Same Day)",
        self::FRAUD_FLAG_DUPLICATE_DECLINE_CARD_10MINUTE => "Duplicate Declines (10 minutes)",
    );

    const SORT_BY_NAME = 'm.name';
    const SORT_BY_MAIN_EMAIL_ID = 'm.main_email_id';

    const SQL_SELECT = "
SELECT m.*,
  (SELECT ms.name FROM merchant_status ms WHERE ms.id = m.status_id) as status_name,
  (SELECT GROUP_CONCAT(u.merchant_id SEPARATOR ';') FROM user u WHERE u.merchant_id = m.id) as user_list,
  (SELECT GROUP_CONCAT(mi.integration_id SEPARATOR ';') FROM merchant_integration mi WHERE mi.merchant_id = m.id) as integration_provisioned_ids,
  s.name as state_name, s.short_code as state_short_code
FROM merchant m
LEFT JOIN state s on m.state_id = s.id
";
    const SQL_GROUP_BY = "\nGROUP BY m.id";
    const SQL_ORDER_BY = "\nORDER BY m.id DESC";
    const SQL_WHERE = "\nWHERE m.status_id != 4";

    public static $ENUM_BUSINESS_TYPE = array(
        'INDIVIDUAL_SOLE_PROPRIETORSHIP' => "Individual Sole Proprietorship",
        'CORPORATION' => "Corporation",
        'LIMITED_LIABILITY_COMPANY' => "Limited Liability Company",
        'PARTNERSHIP' => "Partnership",
        'ASSOCIATION_ESTATE_TRUST' => "Association Estate Trust",
        'TAX_EXEMPT_ORGANIZATION' => "Tax Exempt Organization",
        'INTERNATIONAL_ORGANIZATION' => "International Organization",
        'GOVERNMENT_AGENCY' => "Government Agency",
    );

    const ENUM_STATUS_LIVE = 1;

    public static $ENUM_STATUS = array(
        1 => "Live",
        2 => "In Progress",
        3 => "Canceled",
        4 => "Hidden",
    );
    public static $ENUM_PAYOUT_TYPES = array(
        'BANK_ACCOUNT' => 'Bank Account',
    );
    public static $ENUM_PAYOUT_ACCOUNT_TYPES = array(
        'CHECKING' => 'Checking',
        'SAVINGS' => 'Savings',
    );

    public static $SORT_FIELDS = array(
        self::SORT_BY_ID,
        self::SORT_BY_NAME,
        self::SORT_BY_MAIN_EMAIL_ID,
    );
    public static $UPDATE_FIELDS = array(
        'name',
        'short_name',
        'main_email_id',
        //        'merchant_id',
        'sic',
        'convenience_fee_flat',
        'convenience_fee_limit',
        'convenience_fee_variable_rate',
//        'batch_capture_time',
//        'batch_capture_time_zone',
        'open_date',
        'status_id',
        'store_id',

        'url',
        'logo_path',

        'discover_external',
        'amex_external',
        'agent_chain',
        'main_contact',
        'telephone',
        'address1',
        'address2',
        'city',
        'state_id',
        'zipcode',
        'sale_rep',

        'title',
        'dob',
        'country',
        'tax_id',
        'business_tax_id',
        'business_type',

        'payout_type',
        'payout_account_name',
        'payout_account_type',
        'payout_account_number',
        'payout_bank_code',

        'fraud_high_limit',
        'fraud_low_limit',
        'fraud_high_monthly_limit',
        'fraud_flags',

        'notes',
    );


    protected $id;
    protected $uid;
    protected $version;
    protected $address1;
    protected $address2;
    protected $agent_chain;
    protected $batch_capture_time;
    protected $batch_capture_time_zone;
    protected $city;
    protected $convenience_fee_flat;
    protected $convenience_fee_limit;
    protected $convenience_fee_variable_rate;
    protected $convenience_fee_merchant_id;
    protected $amex_external;
    protected $discover_external;
//    protected $gateway_id;
//    protected $gateway_token;
    protected $main_contact;
    protected $main_email_id;
    protected $name;
    protected $title;
    protected $dob;
    protected $notes;
    protected $open_date;
//    protected $profile_id;
    protected $sale_rep;
    protected $short_name;
    protected $sic;
    protected $mcc;
    protected $store_id;
    protected $telephone;
    protected $zipcode;
    protected $country;
    protected $url;
    protected $logo_path;
//    protected $charge_form_classes;

    protected $state_id;

    protected $status_id;
    protected $tax_id;
    protected $business_tax_id;
    protected $business_type;


    protected $payout_type;
    protected $payout_account_name;
    protected $payout_account_type;
    protected $payout_account_number;
    protected $payout_bank_code;

    protected $fraud_high_limit;
    protected $fraud_low_limit;
    protected $fraud_high_monthly_limit;
    protected $fraud_flags;
    protected $integration_default_id;
    protected $integration_provisioned_ids;

    // Table status
    protected $status_name;

    // Table state
    protected $state_short_code;
    protected $state_name;

    // Table users
    protected $user_list;

    public function __construct(Array $params = array()) {
        foreach ($params as $key => $param)
            $this->$key = $param;
    }

    public function __set($key, $value) {
        error_log("Property does not exist: " . $key);
    }

    public function getID() {
        return $this->id;
    }

    public function getUID() {
        return $this->uid;
    }

    public function getName() {
        return $this->name;
    }

    public function getShortName() {
        return $this->short_name ?: $this->name;
    }

    public function hasLogoPath() {
        return $this->logo_path ? true : false;
    }
    public function getLogoImageURL() {
        if(!$this->hasLogoPath())
            throw new \InvalidArgumentException("Merchant has not uploaded a logo yet");
        return $this->logo_path;
    }

    public function updateLogo($file) {
        $tmp_name = $file['tmp_name'];

        $maxsize = SiteConfig::$MAX_UPLOAD_SIZE;
        if($file['size'] >= $maxsize)
            throw new \InvalidArgumentException('File is too large. File must be less than ' . SiteConfig::$MAX_UPLOAD_SIZE/1024 . ' kb');

        $image_info = getimagesize($tmp_name);
        $image_width = $image_info[0];
        $image_height = $image_info[1];
        if($image_width > SiteConfig::$SITE_MAX_LOGO_WIDTH)
            throw new \InvalidArgumentException('Logo width is too wide. Image width must be less than ' . SiteConfig::$SITE_MAX_LOGO_WIDTH . ' pixels');
        if($image_height > SiteConfig::$SITE_MAX_LOGO_HEIGHT)
            throw new \InvalidArgumentException('Logo height is too wide. Image height must be less than ' . SiteConfig::$SITE_MAX_LOGO_HEIGHT . ' pixels');


        $acceptable = array('image/png');
        if(!in_array(strtolower($file['type']), $acceptable))
            throw new \InvalidArgumentException('Invalid file type: ' . $file['type'] . '. Only PNG types are accepted.');

        $this->logo_path = self::LOGO_PATH . strtoupper($this->getUID()) . '.png';
        if(!move_uploaded_file($tmp_name, dirname(dirname(__DIR__)) . '/' . $this->logo_path))
            throw new \Exception("Upload failed: " . print_r(error_get_last(), true));

        $sql = "UPDATE " . self::TABLE_NAME
            . "\nSET logo_path = :logo_path"
            . "\nWHERE id=:id";
        $params[':id'] = $this->getID();
        $params[':logo_path'] = $this->logo_path;

        $DB = DBConfig::getInstance();
        $LogoQuery = $DB->prepare($sql);
        $LogoQuery->execute($params);
    }

//    public function getMerchantID()     { return $this->merchant_id; }

    public function getMerchantSIC() {
        return $this->sic;
    }

    public function getMerchantMCC() {
        return $this->mcc;
    }

    public function getConvenienceFeeLimit() {
        return floatval($this->convenience_fee_limit);
    }

    public function getConvenienceFeeFlat() {
        return floatval($this->convenience_fee_flat);
    }

    public function getConvenienceFeeVariable() {
        return floatval($this->convenience_fee_variable_rate);
    }

    public function getConvenienceFeeMerchantID() {
        return $this->convenience_fee_merchant_id;
    }

    public function getBatchTime() {
        return $this->batch_capture_time;
    }

    public function getBatchTimeZone() {
        return $this->batch_capture_time_zone;
    }

    public function getOpenDate() {
        return $this->open_date;
    }

    public function getStatusID() {
        return $this->status_id;
    }

    public function getStatusName() {
        return $this->status_name;
    }

    public function getStoreID() {
        return $this->store_id;
    }

    public function getCountryCode() {
        return $this->country;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getTaxID() {
        return $this->tax_id;
    }

    public function getBusinessTaxID() {
        return $this->business_tax_id;
    }

    public function getBusinessType() {
        return $this->business_type;
    }

    public function getDOB() {
        return $this->dob;
    }

    public function getDiscoverExt() {
        return $this->discover_external;
    }

    public function getAmexExt() {
        return $this->amex_external;
    }

    public function getAgentChain() {
        return $this->agent_chain;
    }

    public function getMainContact() {
        return $this->main_contact;
    }

    public function getTelephone($format='($1) $2-$3') {
        $number = $this->telephone;
        if($format)
            $number = preg_replace('~.*(\d{3})[^\d]{0,7}(\d{3})[^\d]{0,7}(\d{4}).*~', $format, $number);
        return $number;
    }

    public function getAddress() {
        return $this->address1;
    }

    public function getAddress2() {
        return $this->address2;
    }

    public function getCity() {
        return $this->city;
    }

    public function getState() {
        return $this->state_name;
    }

    public function getRegionCode() {
        return $this->state_short_code;
    }

    public function getZipCode() {
        return $this->zipcode;
    }

    public function getMainEmailID() {
        return $this->main_email_id;
    }

    public function getSaleRep() {
        return $this->sale_rep;
    }

    public function getPayoutType() {
        return $this->payout_type;
    }

    public function getPayoutAccountName() {
        return $this->payout_account_name;
    }

    public function getPayoutAccountType() {
        return $this->payout_account_type;
    }

    public function getPayoutAccountNumber() {
        return $this->payout_account_number;
    }

    public function getPayoutRoutingNumber() {
        return $this->payout_bank_code;
    }

    public function getFraudHighLimit() {
        return $this->fraud_high_limit;
    }

    public function getFraudLowLimit() {
        return $this->fraud_low_limit;
    }

    public function getFraudHighMonthlyLimit() {
        return $this->fraud_high_monthly_limit;
    }

    public function getFraudFlags() {
        return $this->fraud_flags;
    }

    public function hasFlag($type) {
        return (intval($type) & intval($this->fraud_flags));
    }

    public function getNotes() {
        return $this->notes;
    }


    public function getURL() {
        return $this->url;
    }

    public function getUserList() {
        if (is_array($this->user_list))
            return $this->user_list;
        if (!$this->user_list)
            return $this->user_list = array();
        $this->user_list = explode(';', $this->user_list);
        return $this->user_list;
    }

    public function getUserCount() {
        return count($this->getUserList());
    }


    public function getCheckFormClasses() {
        return 'default';
    }


    public function isConvenienceFeeEnabled() {
        return
            $this->convenience_fee_flat || $this->convenience_fee_limit || $this->convenience_fee_variable_rate;
    }

    public function getMainContactFirstName() {
        list($first, $last) = explode(" ", $this->getMainContact(), 2);
        return $first;
    }

    public function getMainContactLastName() {
        list($first, $last) = explode(" ", $this->getMainContact(), 2);
        return $last;
    }


    public function updateFields($post) {
        $flags = 0;
        foreach ($post['fraud_flags'] as $type => $value)
            if ($value)
                $flags |= intval($type);
        $post['fraud_flags'] = $flags;

        $sqlSet = "";
        $params = array();
        foreach (self::$UPDATE_FIELDS as $field) {
            if (!empty($post[$field])) {
                $params[':' . $field] = $post[$field];
                $sqlSet .= ($sqlSet ? ",\n" : "\nSET ") . $field . '=:' . $field;
                $this->$field = $post[$field];
            }
        }
        if (!$sqlSet)
            return 0;
        $sql = "UPDATE " . self::TABLE_NAME . $sqlSet . "\nWHERE id=:id";
        $params[':id'] = $this->getID();
        $DB = DBConfig::getInstance();
        $EditQuery = $DB->prepare($sql);
        $EditQuery->execute($params);
        return $EditQuery->rowCount();
    }

    public function getProvisionRequest(IntegrationRow $IntegrationRow) {
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare(IntegrationRequestRow::SQL_SELECT
            . "WHERE ir.type LIKE :type"
            . "\n\tAND ir.type_id = :type_id"
            . "\n\tAND ir.integration_id = :integration_id"
            . "\n\tSORT BY ir.result='success' DESC");
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $stmt->setFetchMode(\PDO::FETCH_CLASS, IntegrationRequestRow::_CLASS);
        $stmt->execute(array(
            ':type' => "merchant%",
            ':type_id' => $this->getID(),
            ':integration_id' => $IntegrationRow->getID(),
        ));
    }

    /**
     * @return AbstractMerchantIdentity[]
     * @throws \Exception
     */
    public function getMerchantIdentities() {
        $DB = DBConfig::getInstance();
        $IntegrationQuery = $DB->prepare(
            IntegrationRow::SQL_SELECT
            . IntegrationRow::SQL_WHERE
            . IntegrationRow::SQL_ORDER_BY);
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $IntegrationQuery->setFetchMode(\PDO::FETCH_CLASS, IntegrationRow::_CLASS);
        $IntegrationQuery->execute();

        $Identities = array();
        foreach ($IntegrationQuery as $IntegrationRow) {
            /** @var IntegrationRow $IntegrationRow */
            $Identity = $IntegrationRow->getMerchantIdentity($this);
            $Identities[] = $Identity;
        }

        return $Identities;
    }


    public function getProvisionedIntegrationIDs() {
        return explode(";", $this->integration_provisioned_ids);
    }

    public function getDefaultIntegrationID() {
        if ($this->integration_default_id)
            return $this->integration_default_id;
        $ids = $this->getProvisionedIntegrationIDs();
        return $ids[0];
    }

    // Static

    /**
     * @param $id
     * @return MerchantRow
     */
    public static function fetchByID($id) {
        if (!is_numeric($id))
            throw new \InvalidArgumentException("ID is not numeric: " . $id);
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare(static::SQL_SELECT . "WHERE m.id = ?");
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $stmt->setFetchMode(\PDO::FETCH_CLASS, self::_CLASS);
        $stmt->execute(array($id));
        return $stmt->fetch();
    }

    /**
     * @param $field
     * @param $value
     * @return MerchantRow
     * @throws \Exception
     */
    public static function fetchByField($field, $value) {
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare(static::SQL_SELECT . "WHERE m.{$field} = ?");
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $stmt->setFetchMode(\PDO::FETCH_CLASS, self::_CLASS);
        $stmt->execute(array($value));
        $Row = $stmt->fetch();
        if (!$Row)
            throw new \InvalidArgumentException("{$field} not found: " . $value);
        return $Row;
    }

    public static function fetchByEmail($email) {
        return static::fetchByField('main_email_id', $email);
    }

    /**
     * @param $uid
     * @return MerchantRow
     */
    public static function fetchByUID($uid) {
        return static::fetchByField('uid', $uid);
    }

    public static function queryAll($order = 'm.id DESC') {
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare(static::SQL_SELECT . static::SQL_WHERE . "\nORDER BY " . $order);
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $stmt->setFetchMode(\PDO::FETCH_CLASS, self::_CLASS);
        $stmt->execute();
        return $stmt;
    }


    public static function queryByUserID($id) {
        $sql = MerchantRow::SQL_SELECT
            . "\nLEFT JOIN user u on m.id = u.merchant_id "
            . "\nWHERE m.status_id = ? AND u.id = ?";
        $DB = DBConfig::getInstance();
        $MerchantQuery = $DB->prepare($sql);
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $MerchantQuery->setFetchMode(\PDO::FETCH_CLASS, self::_CLASS);
        $MerchantQuery->execute(array(MerchantRow::ENUM_STATUS_LIVE, $id));
        return $MerchantQuery;
    }

    /**
     * @param $post
     * @return MerchantRow
     */
    public static function createNewMerchant($post) {
        if (strlen($post['name']) < 5)
            throw new \InvalidArgumentException("Merchant Name must be at least 5 characters");

        if (!filter_var($post['main_email_id'], FILTER_VALIDATE_EMAIL))
            throw new \InvalidArgumentException("Invalid Merchant Email");

        $params = array();
        $Merchant = new MerchantRow();
        $Merchant->uid = strtoupper(self::generateGUID());
        $params[':uid'] = $Merchant->uid;

        $sqlSet = "";
        foreach (self::$UPDATE_FIELDS as $field) {
            if (!empty($post[$field])) {
                $params[':' . $field] = $post[$field];
                $sqlSet .= ($sqlSet ? ",\n" : "\nSET ") . $field . '=:' . $field;
                $Merchant->$field = $post[$field];
            }
        }
        if (!$sqlSet)
            return 0;
        $sql = "INSERT INTO " . self::TABLE_NAME . $sqlSet
            . ", uid = :uid";
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare($sql);
        $ret = $stmt->execute($params);
        if (!$ret || !$DB->lastInsertId())
            throw new \PDOException("Failed to insert new row");
        $Merchant->id = $DB->lastInsertId();
        return $Merchant;
    }

    public static function delete(MerchantRow $MerchantRow) {
        $SQL = "DELETE FROM merchant WHERE id = ?";
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare($SQL);
        $ret = $stmt->execute(array($MerchantRow->getID()));
        if(!$ret)
            throw new \PDOException("Failed to delete row");
        if($stmt->rowCount() === 0)
            error_log("Failed to delete row: " . print_r($MerchantRow, true));
    }

    public static function generateGUID() {
        $site_type = SiteConfig::$SITE_UID_PREFIX;
        return sprintf($site_type . 'M' . '-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
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

        $post = array(
            'id' => 27,
            'uid' => '359ae330-e892-43ce-a3f2-3bae893df26381',
            'version' => 1,
            'address1' => 'Fake Address 123',
            'address2' => '#101',
            'agent_chain' => '0j0876',
            'batch_capture_time' => '08:00 PM',
            'batch_capture_time_zone' => 'EST',
            'city' => 'Miami',
            'convenience_fee_flat' => 0,
            'convenience_fee_limit' => 0,
            'convenience_fee_variable_rate' => 3.95,
            'amex_external' => 1096643534,
            'discover_external' => 601105011666431,
//            'gateway_id' => 4445018852749,
//            'gateway_token' => null,
            'main_contact' => 'Sandra Test',
            'main_email_id' => 'sandra@test.com',
            'merchant_id' => 4445017451724,
            'name' => 'Interamerica Data Florida',
            'notes' => 'Test Notes',
            'open_date' => '2015-03-04 00:00:00',
//            'profile_id' => 4445017451721,
            'sale_rep' => 'Referral',
            'short_name' => 'In Da Fl',
            'sic' => 7549,
            'store_id' => 0020,
            'telephone' => '305 9828371',
            'zipcode' => 33147,

            'payout_type' => 'BANK_ACCOUNT',
            'payout_account_name' => 'Fran Lemke',
            'payout_account_type' => 'SAVINGS',
            'payout_account_number' => '123123123',
            'payout_bank_code' => '123123123',

            'url' => 'http://paylogicnetwork.com',
        );

        $TestMerchant = self::createNewMerchant($post);
        self::delete($TestMerchant);
        echo "\nMerchant Unit Test Successful";
    }

}

if(isset($argv) && in_array(@$argv[1], array('test-merchant', 'test-all')))
    MerchantRow::unitTest();
