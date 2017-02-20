<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/28/2016
 * Time: 1:32 PM
 */
namespace Support\Model;

use Support\Mail\TicketEmail;
use System\Config\DBConfig;

class SupportTicketRow
{
    const _CLASS = __CLASS__;
    const TABLE = 'support_ticket';

    const ENUM_STATUS_OPEN      = 'Active';
    const ENUM_STATUS_CLOSE     = 'InActive';

    const ENUM_CATEGORY_GENERAL     = 'General';

    const SORT_BY_ID                = 'st.id';
    const SORT_BY_DATE              = 'st.date';
    const SORT_BY_STATUS            = 'st.status';
    const SORT_BY_MERCHANT_ID       = 'st.merchant_id';
    const SORT_BY_CATEGORY          = 'st.category';

    public static $SORT_FIELDS = array(
        self::SORT_BY_ID,
        self::SORT_BY_DATE,

        self::SORT_BY_STATUS,
        self::SORT_BY_CATEGORY,
        self::SORT_BY_MERCHANT_ID,
    );

    // Table support_ticket
    protected $id;
    protected $uid;
    protected $date;
    protected $status;
    protected $category;
    protected $subject;
    protected $content;
    protected $reply_to_email;
    protected $merchant_id;
    protected $order_item_id;
    protected $assigned_user_id;

    // Table order_item
    protected $order_status;
    protected $customer_first_name;
    protected $customer_last_name;

    // Table merchant
    protected $merchant_short_name;

    // Table Integration
    protected $integration_name;
    protected $integration_id;

    const SQL_SELECT = "
SELECT
    st.*,
    oi.date as order_date,
    oi.status as order_status,
    oi.customer_first_name as customer_first_name,
    oi.customer_last_name as customer_last_name,
    m.short_name as merchant_short_name
FROM support_ticket st
LEFT JOIN order_item oi on st.order_item_id = oi.id
LEFT JOIN merchant m on st.merchant_id = m.id
";
    const SQL_GROUP_BY = ""; // "\nGROUP BY s.id";
    const SQL_ORDER_BY = "\nORDER BY st.id DESC";

    public function getID()                 { return $this->id; }
    public function getOrderID()            { return $this->order_item_id; }
    public function getUID()                { return $this->uid; }
    public function getDate()               { return $this->date; }
    public function getStatus()             { return $this->status; }
    public function getCategory()           { return $this->category; }
    public function getSubject()            { return $this->subject; }
    public function getContent()            { return $this->content; }
    public function getReplyToEmail()       { return $this->reply_to_email; }
    public function getMerchantID()         { return $this->merchant_id; }

    public function getOrderItemID()        { return $this->order_item_id; }
    public function getOrderStatus()        { return $this->order_status; }
    public function getMerchantShortName()  { return $this->merchant_short_name; }

    public function getCustomerFullName()   { return $this->customer_first_name . ' ' . $this->customer_last_name; }

    /**
     * Cancel an active support ticket
     * @param null $additional_message
     * @param null $closed_by
     * @throws \Exception
     */
    public function close($additional_message=null, $closed_by=null) {
        if($this->status === self::ENUM_STATUS_CLOSE)
            throw new \InvalidArgumentException("Close failed: Support ticket is already closed.");

        $this->status = self::ENUM_STATUS_CLOSE;
        $this->content .= "\n\nTicket Closed\nDate: " . date('Y-m-d G:i');
        if($closed_by)
            $this->content .= "\nClosed By: " . $closed_by;
        if($additional_message)
            $this->content .= "\n" . $additional_message;

        $values = array(
            ':id' => $this->id,
            ':status' => $this->status,
            ':content' => $this->content,
        );

        $SQL = '';
        foreach($values as $key=>$value)
            $SQL .= ($SQL ? ',' : '') . "\n\t`" . substr($key, 1) . "` = " . $key;
        $SQL = "UPDATE support_ticket\nSET recur_cancel_date = NOW(), "
            . $SQL
            . "\nWHERE id = :id LIMIT 1";

        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare($SQL);
        $ret = $stmt->execute($values);
        if(!$ret)
            throw new \PDOException("Failed to update row");
    }


    public function sendStatusEmail() {
        $Email = new TicketEmail($this);
        return $Email->send();
    }

    // Static

    public static function delete(SupportTicketRow $SupportTicketRow) {
        $SQL = "DELETE FROM support_ticket WHERE id = ?";
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare($SQL);
        $ret = $stmt->execute(array($SupportTicketRow->getID()));
        if(!$ret)
            throw new \PDOException("Failed to delete row");
    }

    /**
     * @param $subject
     * @param null $content
     * @param null $category
     * @param null $reply_to_email
     * @param null $merchant_id
     * @param null $order_item_id
     * @param null $assigned_user_id
     * @return SupportTicketRow
     * @throws \Exception
     */
    public static function create(
        $subject,
        $content=null,
        $category=null,
        $reply_to_email=null,
        $merchant_id=null,
        $order_item_id=null,
        $assigned_user_id=null
    ) {
        $values = array(
            ':uid' => self::generateReferenceNumber(),
            ':status' => self::ENUM_STATUS_OPEN,
            ':subject' => $subject,
            ':content' => $content,
            ':category' => $category ?: self::ENUM_CATEGORY_GENERAL,
            ':reply_to_email' => $reply_to_email,
            ':merchant_id' => $merchant_id,
            ':order_item_id' => $order_item_id,
            ':assigned_user_id' => $assigned_user_id,
        );

        $SQL = '';
        foreach($values as $key=>$value)
            if($value !== null)
                $SQL .= ($SQL ? ',' : '') . "\n\t`" . substr($key, 1) . "` = " . $key;
        $SQL = "INSERT INTO support_ticket\nSET date = NOW()," . $SQL;

        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare($SQL);
        $ret = $stmt->execute($values);
        if(!$ret || !$DB->lastInsertId())
            throw new \PDOException("Failed to insert new row");

        $id = $DB->lastInsertId();
        $SupportTicketRow = self::fetchByID($id);
        return $SupportTicketRow;
    }

    /**
     * @param $field
     * @param $value
     * @return SupportTicketRow
     * @throws \Exception
     */
    public static function fetchByField($field, $value) {
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare(static::SQL_SELECT . "WHERE st.{$field} = ?");
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
     * @return SupportTicketRow
     */
    public static function fetchByUID($uid) {
        return static::fetchByField('uid', $uid);
    }

    /**
     * @param $id
     * @return SupportTicketRow
     */
    public static function fetchByID($id) {
        return static::fetchByField('id', $id);
    }


    public static function generateReferenceNumber() {
        return sprintf('%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535));
    }



}

