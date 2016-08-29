<?php
/**
 * Created by PhpStorm.
 * Merchant: ari
 * Date: 8/28/2016
 * Time: 1:32 PM
 */
namespace Merchant;

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


    public function getID()         { return $this->id; }
    public function getShortName()  { return $this->short_name; }
    public function getEmail()      { return $this->main_email_id; }

    // Static

    /**
     * @param $id
     * @return MerchantRow
     */
    public static function fetchByID($id) {
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare("SELECT * FROM merchant where id = ?");
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $stmt->setFetchMode(\PDO::FETCH_CLASS, 'Merchant\MerchantRow');
        $stmt->execute(array($id));
        return $stmt->fetch();
    }

    /**
     * @param $uid
     * @return MerchantRow
     */
    public static function fetchByUID($uid) {
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare("SELECT * FROM merchant where uid = ?");
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $stmt->setFetchMode(\PDO::FETCH_CLASS, 'Merchant\MerchantRow');
        $stmt->execute(array($uid));
        return $stmt->fetch();
    }

}

