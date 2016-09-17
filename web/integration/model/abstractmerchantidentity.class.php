<?php
namespace Integration\Model;
use Config\DBConfig;
use Integration\Model\Ex\IntegrationException;
use Integration\Request\Model\IntegrationRequestRow;
use Merchant\Model\MerchantRow;

/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/14/2016
 * Time: 8:14 PM
 */
abstract class AbstractMerchantIdentity {


    abstract function getRemoteID();
    abstract function getCreateDate();
    abstract function getUpdateDate();

    abstract function isProfileComplete(&$message=null);
    abstract function isProvisioned(&$reason=null);
    abstract function canSettleFunds(&$reason=null);
//    abstract function settleFunds();

    abstract protected function parseRequest(IntegrationRequestRow $APIRequest);

    public function __construct(MerchantRow $Merchant, IntegrationRow $APIData) {
        $DB = DBConfig::getInstance();
        $stmt = $DB->prepare(IntegrationRequestRow::SQL_SELECT
            . "WHERE ir.type LIKE :type"
            . "\n\tAND ir.type_id = :type_id"
            . "\n\tAND ir.integration_id = :integration_id");
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $stmt->setFetchMode(\PDO::FETCH_CLASS, IntegrationRequestRow::_CLASS);
        $stmt->execute(array(
            ':type' => "merchant%",
            ':type_id' => $Merchant->getID(),
            ':integration_id' => $APIData->getID(),
        ));

        foreach($stmt as $Request) {
            /** @var IntegrationRequestRow $Request */
            if(!$Request->getResponse())
                throw new IntegrationException("Empty response");
            $this->parseRequest($Request);
        }
    }


}