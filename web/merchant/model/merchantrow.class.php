<?php
/**
 * Created by PhpStorm.
 * Merchant: ari
 * Date: 8/28/2016
 * Time: 1:32 PM
 */
namespace Merchant\Model;

use Config\DBConfig;
use Integration\Model\AbstractIntegration;
use Integration\Request\Model\IntegrationRequestRow;

class MerchantRow
{
    const _CLASS = __CLASS__;
    const TABLE_NAME = 'merchant';

    const SORT_BY_ID                = 'm.id';
    const SORT_BY_NAME              = 'm.name';
    const SORT_BY_MAIN_EMAIL_ID     = 'm.main_email_id';

    public static $ENUM_BUSINESS_TYPE = array(
        'INDIVIDUAL_SOLE_PROPRIETORSHIP' => "Individual Sole Proprietorship",
        'CORPORATION'                    => "Corporation",
        'LIMITED_LIABILITY_COMPANY'      => "Limited Liability Company",
        'PARTNERSHIP'                    => "Partnership",
        'ASSOCIATION_ESTATE_TRUST'       => "Association Estate Trust",
        'TAX_EXEMPT_ORGANIZATION'        => "Tax Exempt Organization",
        'INTERNATIONAL_ORGANIZATION'     => "International Organization",
        'GOVERNMENT_AGENCY'              => "Government Agency",
    );

    public static $ENUM_STATUS = array(
        1 => "Live",
        2 => "In Progress",
        3 => "Canceled",
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
        'merchant_id',
        'sic',
        'notes',
        'convenience_fee_flat',
        'convenience_fee_limit',
        'convenience_fee_variable_rate',
        'batch_capture_time',
        'batch_capture_time_zone',
        'open_date',
        'status_id',
        'store_id',
        'discover_external',
        'amex_external',
        'agent_chain',
        'main_contact',
        'telephone',
        'address1',
        'address2',
        'agent_chain',
        'city',
        'agent_chain',
        'state_id',
        'zipcode',
        'sale_rep',

        'title',
        'dob',
        'country',
        'tax_id',
        'business_tax_id',
        'business_type',

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
    protected $amex_external;
    protected $discover_external;
    protected $gateway_id;
    protected $gateway_token;
    protected $main_contact;
    protected $main_email_id;
    protected $merchant_id;
    protected $name;
    protected $title;
    protected $dob;
    protected $notes;
    protected $open_date;
    protected $profile_id;
    protected $sale_rep;
    protected $short_name;
    protected $sic;
    protected $store_id;
    protected $telephone;
    protected $zipcode;
    protected $country;
    protected $url;
    protected $state_id;

    protected $status_id;
    protected $tax_id;
    protected $business_tax_id;
    protected $business_type;

    // Table status
    protected $status_name;

    // Table state
    protected $state_short_code;
    protected $state_name;

    const SQL_SELECT = "
SELECT m.*,
  (SELECT ms.name FROM merchant_status ms WHERE ms.id = m.status_id) as status_name,
  s.name as state_name, s.short_code as state_short_code
FROM merchant m
LEFT JOIN state s on m.state_id = s.id
";
    const SQL_GROUP_BY = "\nGROUP BY m.id";
    const SQL_ORDER_BY = "\nORDER BY m.id DESC";


    public function __construct(Array $params=array()) {
        foreach($params as $key=>$param)
            $this->$key = $param;
    }
    public function __set($key, $value) {
        throw new \InvalidArgumentException("Property does not exist: " . $key);
    }

    public function getID()             { return $this->id; }
    public function getUID()            { return $this->uid; }
    public function getName()           { return $this->name; }
    public function getShortName()      { return $this->short_name; }
    public function getMerchantID()     { return $this->merchant_id; }
    public function getMerchantSIC()    { return $this->sic; }

    public function getFeeLimit()       { return $this->convenience_fee_limit; }
    public function getFeeFlat()        { return $this->convenience_fee_flat; }
    public function getFeeVariable()    { return $this->convenience_fee_variable_rate; }

    public function getBatchTime()      { return $this->batch_capture_time; }
    public function getBatchTimeZone()  { return $this->batch_capture_time_zone; }

    public function getOpenDate()       { return $this->open_date; }
    public function getStatusID()       { return $this->status_id; }
    public function getStatusName()     { return $this->status_name; }
    public function getStoreID()        { return $this->store_id; }

    public function getCountryCode()    { return $this->country; }
    public function getTitle()          { return $this->title; }
    public function getTaxID()          { return $this->tax_id; }
    public function getBusinessTaxID()  { return $this->business_tax_id; }
    public function getBusinessType()   { return $this->business_type; }
    public function getDOB()            { return $this->dob; }

    public function getDiscoverExt()    { return $this->discover_external; }
    public function getAmexExt()        { return $this->amex_external; }

    public function getAgentChain()     { return $this->agent_chain; }
    public function getMainContact()    { return $this->main_contact; }
    public function getTelephone()      { return $this->telephone; }
    public function getAddress()        { return $this->address1; }
    public function getAddress2()       { return $this->address2; }

    public function getCity()           { return $this->city; }
    public function getState()          { return $this->state_name; }
    public function getRegionCode()     { return $this->state_short_code; }
    public function getZipCode()        { return $this->zipcode; }

    public function getMainEmailID()    { return $this->main_email_id; }
    public function getSaleRep()        { return $this->sale_rep; }
    public function getNotes()          { return $this->notes; }

    public function getURL()            { return $this->url; }

    public function getMainContactFirstName() {
        list($first, $last) = explode(" ", $this->getMainContact(), 2);
        return $first;
    }
    public function getMainContactLastName() {
        list($first, $last) = explode(" ", $this->getMainContact(), 2);
        return $last;
    }


    public function updateFields($post) {
        $sqlSet = "";
        $params = array();
        foreach(self::$UPDATE_FIELDS as $field) {
            if(!empty($post[$field])) {
                $params[':'.$field] = $post[$field];
                $sqlSet .= ($sqlSet ? ",\n" : "\nSET ") . $field . '=:' . $field;
                $this->$field = $post[$field];
            }
        }
        if(!$sqlSet)
            return 0;
        $sql = "UPDATE " . self::TABLE_NAME . $sqlSet . "\nWHERE id=:id";
        $params[':id'] = $this->getID();
        $DB = DBConfig::getInstance();
        $EditQuery = $DB->prepare($sql);
        $EditQuery->execute($params);
        return $EditQuery->rowCount();
    }

    /**
     * @param AbstractIntegration $Integration
     * @param string $result
     * @param string $type
     * @return IntegrationRequestRow
     */
    public function fetchAPIRequest(AbstractIntegration $Integration, $type, $result=IntegrationRequestRow::ENUM_RESULT_SUCCESS) {
        return IntegrationRequestRow::fetchByType(
            $type,
            $this->getID(),
            $Integration->getIntegrationRow()->getID(),
            $result
        );
    }

    // Static

    /**
     * @param $id
     * @return MerchantRow
     */
    public static function fetchByID($id) {
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare(static::SQL_SELECT . "WHERE m.id = ?");
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $stmt->setFetchMode(\PDO::FETCH_CLASS, self::_CLASS);
        $stmt->execute(array($id));
        return $stmt->fetch();
    }

    /**
     * @param $uid
     * @return MerchantRow
     */
    public static function fetchByUID($uid) {
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare(static::SQL_SELECT . "WHERE m.uid = ?");
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $stmt->setFetchMode(\PDO::FETCH_CLASS, self::_CLASS);
        $stmt->execute(array($uid));
        return $stmt->fetch();
    }

    public static function queryAll($order = 'm.id DESC') {
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare(static::SQL_SELECT . "\nORDER BY " . $order);
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $stmt->setFetchMode(\PDO::FETCH_CLASS, self::_CLASS);
        $stmt->execute();
        return $stmt;
    }

    public static function queryByUserID($id) {
        $sql = MerchantRow::SQL_SELECT
            . "\nLEFT JOIN user_merchants um on m.id = um.id_merchant "
            . "\nWHERE um.id_user = ?";
        $DB = DBConfig::getInstance();
        $MerchantQuery = $DB->prepare($sql);
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $MerchantQuery->setFetchMode(\PDO::FETCH_CLASS, self::_CLASS);
        $MerchantQuery->execute(array($id));
        return $MerchantQuery;
    }


}

