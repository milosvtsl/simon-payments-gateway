<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/28/2016
 * Time: 1:32 PM
 */
namespace System\Model;

use Order\Mail\DeclineEmail;
use Order\Mail\MerchantDeclineEmail;
use Order\Mail\MerchantReceiptEmail;
use Order\Mail\ReceiptEmail;
use System\Config\DBConfig;
use User\Mail\UserWelcomeEmail;

class EmailTemplateRow
{
    const _CLASS = __CLASS__;

    // Table transaction
    protected $id;
    protected $class;
    protected $subject;
    protected $body;
    protected $bcc;
    protected $updated;
    protected $merchant_id;

    const SQL_SELECT = "
SELECT et.*,
m.short_name as merchant_short_name,
m.uid as merchant_uid
FROM email_template et
LEFT JOIN merchant m on et.merchant_id = m.id
";
    const SQL_GROUP_BY = ''; //"\nGROUP BY et.id";
    const SQL_ORDER_BY = "\nORDER BY et.id DESC";

    public function getID()                 { return $this->id; }

    public function getClass()              { return $this->class; }

    public function getSubject()            { return $this->subject; }

    public function getBody()               { return $this->body; }
    public function getBCC()                { return $this->bcc; }
    public function getUpdated()            { return $this->updated; }
    public function getMerchantID()         { return $this->merchant_id; }
    // Static

    public static function insertOrUpdate($merchant_id, Array $post) {
        $SQL = <<<SQL
INSERT INTO email_template SET 
    merchant_id = :merchant_id,
    class = :class,
    subject = :subject,
    body = :body,
    updated = UTC_TIMESTAMP()
ON DUPLICATE KEY UPDATE
    subject = :subject,
    body = :body,
    updated = UTC_TIMESTAMP()
SQL;


        $params = array(
            ':merchant_id' => $merchant_id,
            ':class' => $post['class'],
            ':subject' => $post['subject'],
            ':body' => $post['body'],
        );

        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare($SQL);
        $stmt->execute($params);

        return $DB->lastInsertId();
    }

    /**
     * @param $class
     * @param $merchant_id
     * @return EmailTemplateRow
     */
    public static function fetchAvailableTemplate($class, $merchant_id) {
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare(static::SQL_SELECT . "\nWHERE et.merchant_id=:merchant_id AND et.class=:class");
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $stmt->setFetchMode(\PDO::FETCH_CLASS, self::_CLASS);
        $stmt->execute(array(
            'class' => $class,
            'merchant_id' => $merchant_id,
        ));
        return $stmt->fetch();
    }


    /**
     * @param $id
     * @return EmailTemplateRow
     */
    public static function fetchByID($id){
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare(static::SQL_SELECT . "\nWHERE et.id=:id");
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $stmt->setFetchMode(\PDO::FETCH_CLASS, self::_CLASS);
        $stmt->execute(array(
            'id' => $id
        ));
        return $stmt->fetch();
    }

    public static function queryAll() {
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare(static::SQL_SELECT);
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $stmt->setFetchMode(\PDO::FETCH_CLASS, self::_CLASS);
        $stmt->execute();
        return $stmt;
    }

    public static function getAvailableEmailTemplateClasses() {
        return array(
            ReceiptEmail::TITLE => ReceiptEmail::class,
            DeclineEmail::TITLE => DeclineEmail::class,

            MerchantReceiptEmail::TITLE => MerchantReceiptEmail::class,
            MerchantDeclineEmail::TITLE => MerchantDeclineEmail::class,
        );
    }
}

