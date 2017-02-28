<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/28/2016
 * Time: 1:32 PM
 */
namespace Order\Fee\Model;

use System\Config\DBConfig;

class MerchantFeeRow
{
    const _CLASS = __CLASS__;
    const TABLE = 'merchant_fee';

    // Table fee
    protected $id;
    protected $comment;
    protected $type;
    protected $entry_mode;
    protected $amount_flat;
    protected $amount_variable;
    protected $amount_limit;
    protected $merchant_id;
    protected $merchant_fee_account_id;
    protected $integration_id;

    public function getID()                             { return $this->id; }
    public function getComment()                        { return $this->comment; }
    public function getType()                           { return $this->type; }
    public function getEntryMode()                      { return $this->entry_mode; }
    public function getAmountFlat()                     { return $this->amount_flat; }
    public function getAmountVariable()                 { return $this->amount_variable; }
    public function getAmountLimit()                    { return $this->amount_limit; }
    public function getMerchantID()                     { return $this->merchant_id; }
    public function getMerchantFeeAccountID()           { return $this->merchant_fee_account_id; }
    public function getIntegrationID()                  { return $this->integration_id; }


    // Static

    /**
     * @param $merchant_id
     * @return MerchantFeeRow[]|\PDOStatement
     */
    public static function queryAll($merchant_id) {
        $SQL = "SELECT * FROM merchant_fee WHERE (merchant_id = :merchant_id OR merchant_id IS NULL) ORDER BY merchant_id ASC";
        $params = array(
            ':merchant_id' => $merchant_id,
        );
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare($SQL);
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $stmt->setFetchMode(\PDO::FETCH_CLASS, self::_CLASS);
        $ret = $stmt->execute($params);
        if(!$ret)
            throw new \PDOException("Failed to fetch merchant fees");
        return $stmt;
    }

    public static function delete($merchant_id, $type='%') {
        $SQL = "DELETE FROM merchant_fee WHERE merchant_id = :merchant_id AND type LIKE :type";
        $params = array(
            ':merchant_id' => $merchant_id,
            ':type' => $type,
        );
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare($SQL);
        $ret = $stmt->execute($params);
        if(!$ret)
            throw new \PDOException("Failed to delete order field");
        return $stmt->rowCount();
    }

    public static function set($merchant_id, $type, $comment=null, $entry_mode=null, $amount_flat=null, $amount_variable=null, $amount_limit=null, $merchant_fee_account_id=null, $integration_id=null) {
        $vars = get_defined_vars();

        $SQL = "INSERT INTO merchant_fee SET ";
        $params = array();
        foreach($vars as $name=>$value) {
            if($value === null)
                continue;
            $SQL .= (sizeof($params) > 0 ? ', ' : '') . "`{$name}`=:{$name}";
            $params[':' . $name] = $value;
        }

        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare($SQL);
        $ret = $stmt->execute($params);
        if(!$ret)
            throw new \PDOException("Failed to insert order field");
    }

    /**
     * Unit Test
     * @throws \Exception
     */
    public static function unitTest() {
        require_once __DIR__ . '/../../../system/config/dbconfig.class.php';

        self::set(5, FeeRow::TYPE_CONVENIENCE_FEE, NULL, '5.00');
        self::delete(5);
    }
}

//if(isset($argv) && in_array(@$argv[1], array('test-merchant-fee-row', 'test-all')))
//    MerchantFeeRow::unitTest();
