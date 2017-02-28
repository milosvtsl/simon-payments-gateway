<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/28/2016
 * Time: 1:32 PM
 */
namespace Order\Fee\Model;

use Order\Model\OrderRow;
use System\Config\DBConfig;

class FeeRow
{
    const _CLASS = __CLASS__;
    const TABLE = 'fee';
    const TYPE_SALE_AMOUNT = 'sale';
    const TYPE_CONVENIENCE_FEE = 'conv';
    const TYPE_SERVICE_FEE = 'service';
    const TYPE_AUTH_FEE = 'auth';
    const TYPE_VOID_FEE = 'void';
    const TYPE_REFUND_FEE = 'refund';

    static $FEE_TYPES = array(
//        self::TYPE_SALE_AMOUNT => "Sale Amount",
        self::TYPE_CONVENIENCE_FEE => "Convenience Fee",
        self::TYPE_SERVICE_FEE => "Service Fee",
        self::TYPE_AUTH_FEE => "Auth Fee",
        self::TYPE_VOID_FEE => "Void Fee",
        self::TYPE_REFUND_FEE => "Refund Fee",
    );

    // Table fee
    protected $amount;
    protected $type;
    protected $date;
    protected $order_item_id;
    protected $merchant_id;

    public function getAmount()             { return $this->amount; }
    public function getType()               { return $this->type; }
    public function getDate()               { return $this->date; }
    public function getOrderItemID()        { return $this->order_item_id; }
    public function getMerchantID()         { return $this->merchant_id; }

    // Static

    public static function create($amount, $type, $order_item_id, $merchant_id) {
        $Fee = new FeeRow();
        $Fee->amount = $amount;
        $Fee->type = $type;
        $Fee->order_item_id = $order_item_id;
        $Fee->merchant_id = $merchant_id;
        return $Fee;
    }

    public static function delete($order_item_id, $type=null) {
        $SQL = "DELETE FROM fee WHERE order_item_id = :order_item_id";
        $params = array(
            ':order_item_id' => $order_item_id
        );
        if($type) {
            $params[':type'] = $type;
            $SQL .= " AND type LIKE :type";
        }
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare($SQL);
        $ret = $stmt->execute($params);
        if(!$ret)
            throw new \PDOException("Failed to delete order field");
        return $stmt->rowCount();
    }

    public static function set($order_item_id, $merchant_id, $type, $amount) {
        $SQL = "INSERT INTO fee SET `order_item_id`=:order_item_id, `merchant_id`=:merchant_id, `amount`=:amount, `type`=:type, `date`=UTC_TIMESTAMP()";
        $params = array(
            ':order_item_id' => $order_item_id,
            ':merchant_id' => $merchant_id,
            ':type' => $type,
            ':amount' => $amount
        );
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

        self::set(9466, 5, self::TYPE_CONVENIENCE_FEE, '5.00');
        self::delete(9466);
    }
}

//if(isset($argv) && in_array(@$argv[1], array('test-fee-row', 'test-all')))
//    FeeRow::unitTest();

