<?php
/**
 * Created by PhpStormi.
 * Merchant: ari
 * Date: 8/28/2016
 * Time: 1:32 PM
 */
namespace Merchant\Model;

use Integration\Model\AbstractMerchantIdentity;
use System\Config\DBConfig;

class MerchantIntegrationRow
{
    const _CLASS = __CLASS__;
    const TABLE_NAME = 'merchant_integration';

    const SQL_SELECT = "
SELECT mi.*
FROM merchant_integration mi
";
    const SQL_GROUP_BY = "\nGROUP BY mi.id";
    const SQL_ORDER_BY = "\nORDER BY mi.created DESC";
    const SQL_WHERE =    "\nWHERE 1";

    protected $merchant_id;
    protected $integration_id;
    protected $created;
    protected $updated;
    protected $credentials;

    public function __set($key, $value) {
        error_log("Property does not exist: " . $key);
    }

    public function getMerchantID()             { return $this->merchant_id; }
    public function getIntegrationID()          { return $this->integration_id; }
    public function getCredentials()            { return json_decode($this->credentials, true); }

    /**
     * Return unserialized Merchant Identity
     * @return AbstractMerchantIdentity
     */
    public function getMerchantIdentity() {
        $identity = $this->getCredentials();
        $Identity = new $identity;
        return $Identity;
    }

    // Static

    public static function writeMerchantIdentity(AbstractMerchantIdentity $MerchantIdentity) {
        $params = array(
            ':credentials' => json_encode($MerchantIdentity->getCredentials(), JSON_PRETTY_PRINT),
            ':integration_id' => $MerchantIdentity->getIntegrationRow()->getID(),
            ':merchant_id' => $MerchantIdentity->getMerchantRow()->getID(),
        );

        $sql = "INSERT INTO " . self::TABLE_NAME
            . "\nSET created=UTC_TIMESTAMP(), updated=UTC_TIMESTAMP(), "
            . "merchant_id=:merchant_id, "
            . "integration_id=:integration_id, "
            . "credentials=:credentials"
            . "\nON DUPLICATE KEY UPDATE credentials=:credentials";

        $DB = DBConfig::getInstance();
        $WriteQuery = $DB->prepare($sql);
        $WriteQuery->execute($params);
        return $WriteQuery->rowCount();
    }

    /**
     * @param $merchant_id
     * @param $integration_id
     * @return MerchantIntegrationRow|NULL
     */
    public static function fetch($merchant_id, $integration_id) {
        $DB = DBConfig::getInstance();
        $sql = static::SQL_SELECT . "\nWHERE merchant_id=:merchant_id && integration_id=:integration_id ";
        $stmt = $DB->prepare($sql);
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $stmt->setFetchMode(\PDO::FETCH_CLASS, self::_CLASS);
        $stmt->execute(array(
            ':integration_id' => $integration_id,
            ':merchant_id' => $merchant_id,
        ));
        return $stmt->fetch()?:NULL;
    }

    /**
     * @param $userID
     * @return MerchantIntegrationRow[] | \PDOStatement
     * @throws \Exception
     */
    public static function queryAvailableMerchantIntegrations($userID) {
        $sql = static::SQL_SELECT
            . "\nWHERE (u.id = ? AND u.merchant_id = mi.merchant_id) OR mi.merchant_id is NULL"
            . "\nORDER BY mi.merchant_id desc, mi.id desc";
        $DB = DBConfig::getInstance();
        $MerchantIntegrationQuery = $DB->prepare($sql);
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $MerchantIntegrationQuery->setFetchMode(\PDO::FETCH_CLASS, self::_CLASS);
        $MerchantIntegrationQuery->execute(array($userID));
        return $MerchantIntegrationQuery;
    }

}