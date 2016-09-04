<?php
/**
 * Created by PhpStorm.
 * Merchant: ari
 * Date: 8/28/2016
 * Time: 1:32 PM
 */
namespace Merchant\Model;

use Config\DBConfig;

class MerchantRow
{
    const _CLASS = __CLASS__;
    protected $id;
    protected $uid;
    protected $version;
    protected $address1;
    protected $address2;
    protected $agent_chain;
    protected $amex_external;
    protected $batch_capture_time;
    protected $batch_capture_time_zone;
    protected $city;
    protected $convenience_fee_flat;
    protected $convenience_fee_limit;
    protected $convenience_fee_variable_rate;
    protected $discover_external;
    protected $gateway_id;
    protected $gateway_token;
    protected $main_contact;
    protected $main_email_id;
    protected $merchant_id;
    protected $name;
    protected $notes;
    protected $open_date;
    protected $profile_id;
    protected $sale_rep;
    protected $short_name;
    protected $sic;
    protected $store_id;
    protected $telephone;
    protected $zipcode;

    protected $status_id;
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

    public function getDiscoverExt()    { return $this->discover_external; }
    public function getAmexExt()        { return $this->amex_external; }

    public function getAgentChain()     { return $this->agent_chain; }
    public function getMainContact()    { return $this->main_contact; }
    public function getTelephone()      { return $this->telephone; }
    public function getAddress()        { return $this->address1; }
    public function getAddress2()       { return $this->address2; }

    public function getCity()           { return $this->city; }
    public function getState()          { return $this->state_name; }
    public function getStateCode()      { return $this->state_short_code; }
    public function getZipCode()        { return $this->zipcode; }

    public function getMainEmailID()    { return $this->main_email_id; }
    public function getSaleRep()        { return $this->sale_rep; }
    public function getNotes()          { return $this->notes; }

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

    public static function queryAll() {
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare(static::SQL_SELECT );
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

