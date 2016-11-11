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
use Integration\Model\AbstractMerchantIdentity;
use Integration\Model\Ex\IntegrationException;
use Order\Model\OrderRow;
use User\Model\UserRow;

class SupportTicketReplyRow
{
    const _CLASS = __CLASS__;
    const TABLE = 'support_ticket';

    const ENUM_STATUS_OPEN      = 'Open';
    const ENUM_STATUS_CLOSE     = 'Closed';

    const ENUM_CATEGORY_GENERAL     = 'General';

    const SORT_BY_ID                = 'str.id';
    const SORT_BY_DATE              = 'str.date';


    // Table support_ticket_reply
    protected $id;
    protected $ticket_id;
    protected $date;
    protected $subject;
    protected $content;
    protected $from_name;
    protected $from_user_id;



    // Table support_ticket
    protected $ticket_uid;
    protected $ticket_status;
    protected $ticket_category;
    protected $ticket_subject;
    protected $ticket_reply_to_email;
    protected $ticket_merchant_id;
    protected $ticket_order_item_id;
    protected $ticket_assigned_user_id;


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
    str.*,
    st.uid as ticket_uid,
    st.status as ticket_status,
    st.category as ticket_category,
    st.subject as ticket_subject,
    st.reply_to_email as ticket_reply_to_email,
    st.merchant_id as ticket_merchant_id,
    st.order_item_id as ticket_order_item_id,
    st.assigned_user_id as ticket_assigned_user_id,


    oi.date as order_date,
    oi.status as order_status,
    oi.customer_first_name as customer_first_name,
    oi.customer_last_name as customer_last_name,
    m.short_name as merchant_short_name
FROM support_ticket_reply str
LEFT JOIN support_ticket st on str.ticket_id = st.id
LEFT JOIN order_item oi on st.order_item_id = oi.id
LEFT JOIN merchant m on st.merchant_id = m.id
";
    const SQL_GROUP_BY = ""; // "\nGROUP BY s.id";
    const SQL_ORDER_BY = "\nORDER BY s.id DESC";

    public function getID()                 { return $this->id; }
    public function getDate()               { return $this->date; }
    public function getSubject()            { return $this->subject; }
    public function getContent()            { return $this->content; }
    public function getTicketID()           { return $this->ticket_id; }

    public function getUID()                { return $this->ticket_uid; }
    public function getStatus()             { return $this->ticket_status; }
    public function getCategory()           { return $this->ticket_category; }
    public function getReplyToEmail()       { return $this->ticket_reply_to_email; }
    public function getMerchantID()         { return $this->ticket_merchant_id; }
    public function getOrderItemID()        { return $this->ticket_order_item_id; }

    public function getOrderStatus()        { return $this->order_status; }
    public function getMerchantShortName()  { return $this->merchant_short_name; }

    public function getCustomerFullName()   { return $this->customer_first_name . ' ' . $this->customer_last_name; }


    // Static

    public static function delete(SupportTicketReplyRow $SupportTicketReplyRow) {
        $SQL = "DELETE FROM support_ticket WHERE id = ?";
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare($SQL);
        $ret = $stmt->execute(array($SupportTicketReplyRow->getID()));
        if(!$ret)
            throw new \PDOException("Failed to delete row");
    }

    /**
     * @param SupportTicketRow $SupportTicket
     * @param String$content
     * @param null|UserRow $SessionUser
     * @param null|String $subject
     * @param null|String $from_name
     * @return SupportTicketReplyRow
     * @throws \Exception
     */
    public static function create(
        SupportTicketRow $SupportTicket,
        $content,
        UserRow $SessionUser = null,
        $subject=null,
        $from_name=null
    ) {
        $values = array(
            ':uid' => self::generateReferenceNumber(),
            ':ticket_id' => $SupportTicket->getID(),
            ':content' => $content,
            ':subject' => $subject,
            ':from_name' => $from_name,
        );
        if($SessionUser) {
            $value[':from_user_id'] = $SessionUser->getID();
            $value[':from_name'] = $SessionUser->getFullName();
        }
        $SQL = '';
        foreach($values as $key=>$value)
            if($value !== null)
                $SQL .= ($SQL ? ',' : '') . "\n\t`" . substr($key, 1) . "` = " . $key;
        $SQL = "INSERT INTO support_ticket_reply\nSET date = NOW()," . $SQL;

        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare($SQL);
        $ret = $stmt->execute($values);
        if(!$ret || !$DB->lastInsertId())
            throw new \PDOException("Failed to insert new row");

        $id = $DB->lastInsertId();
        $SupportTicketReplyRow = self::fetchByID($id);
        return $SupportTicketReplyRow;
    }

    /**
     * @param $field
     * @param $value
     * @return SupportTicketReplyRow
     * @throws \Exception
     */
    public static function fetchByField($field, $value) {
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare(static::SQL_SELECT . "WHERE s.{$field} = ?");
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
     * @return SupportTicketReplyRow
     */
    public static function fetchByUID($uid) {
        return static::fetchByField('uid', $uid);
    }

    /**
     * @param $id
     * @return SupportTicketReplyRow
     */
    public static function fetchByID($id) {
        return static::fetchByField('id', $id);
    }


    public static function generateReferenceNumber() {
        return sprintf('%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535));
    }



}

