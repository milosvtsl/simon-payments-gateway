<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/28/2016
 * Time: 1:32 PM
 */
namespace Order\Model;

use System\Config\DBConfig;

class OrderFieldRow
{
    const _CLASS = __CLASS__;
    const TABLE = 'order_field';

    // Table order_field
    protected $order_id;
    protected $field_name;
    protected $field_value;

    public function getOrderID()            { return $this->order_id; }
    public function getFieldName()          { return $this->field_name; }
    public function getFieldValue()         { return $this->field_value; }

    // Static

    public static function delete(OrderRow $OrderRow, $field_name, $field_value) {
        $SQL = "DELETE FROM order_field WHERE order_id = ? AND field_name = ?";
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare($SQL);
        $ret = $stmt->execute(array($OrderRow->getID(), $field_name));
        if(!$ret)
            throw new \PDOException("Failed to delete order field");
        if($stmt->rowCount() === 0)
            error_log("Failed to delete field row: " . print_r($OrderRow, true));
    }

    public static function insertOrUpdate(OrderRow $OrderRow, $field_name, $field_value) {
        $SQL = "INSERT INTO order_field SET order_id = ?, field_name = ?, field_value = ? ON DUPLICATE KEY UPDATE field_value = ?";
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare($SQL);
        $ret = $stmt->execute(array($OrderRow->getID(), $field_name, $field_value, $field_value));
        if(!$ret)
            throw new \PDOException("Failed to insert order field");
    }

}

