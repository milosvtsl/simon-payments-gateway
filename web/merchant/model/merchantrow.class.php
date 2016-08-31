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

    // Table state
    protected $state_short_code;
    protected $state_name;

    const SQL_SELECT = "
SELECT m.*, s.name as state_name, s.short_code as state_short_code
FROM merchant m
LEFT JOIN state s on m.state_id = s.id
";

    public function getID()         { return $this->id; }
    public function getUID()        { return $this->uid; }
    public function getName()       { return $this->name; }
    public function getShortName()  { return $this->short_name; }
    public function getEmail()      { return $this->main_email_id; }
    public function getCity()       { return $this->city; }
    public function getState()      { return $this->state_name; }
    public function getStateCode()  { return $this->state_short_code; }
    public function getZipCode()   { return $this->zipcode; }

    // Static

    /**
     * @param $id
     * @return MerchantRow
     */
    public static function fetchByID($id) {
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare(static::SQL_SELECT . "WHERE m.id = ?");
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $stmt->setFetchMode(\PDO::FETCH_CLASS, 'Merchant\Model\MerchantRow');
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
        $stmt->setFetchMode(\PDO::FETCH_CLASS, 'Merchant\Model\MerchantRow');
        $stmt->execute(array($uid));
        return $stmt->fetch();
    }

}

