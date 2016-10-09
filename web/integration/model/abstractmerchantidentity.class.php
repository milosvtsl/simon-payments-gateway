<?php
namespace Integration\Model;
use Config\DBConfig;
use Integration\Model\Ex\IntegrationException;
use Integration\Request\Model\IntegrationRequestRow;
use Merchant\Model\MerchantRow;
use Order\Model\OrderRow;
use Transaction\Model\TransactionRow;

/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/14/2016
 * Time: 8:14 PM
 */
abstract class AbstractMerchantIdentity {

    private $merchant;
    /** @var IntegrationRow */
    private $integration;

    abstract function getRemoteID();
    abstract function getCreateDate();
    abstract function getUpdateDate();

    abstract function isProfileComplete(&$message=null);
    abstract function isProvisioned(&$reason=null);
    abstract function canSettleFunds(&$reason=null);

//    abstract function settleFunds();


    /**
     * Remove provision a merchant
     * @return mixed
     */
    abstract function provisionRemote();

    /**
     * Settle funds to a merchant
     * @return mixed
     */
    abstract function settleRemote();

    /**
     * Parse remote response and return a data object
     * @param IntegrationRequestRow $APIRequest
     * @return mixed
     */
    abstract protected function parseRequest(IntegrationRequestRow $APIRequest);

    /**
     * Calculate Transaction Service Fee
     * @param OrderRow $OrderRow
     * @return mixed
     */
    abstract public function calculateServiceFee(OrderRow $OrderRow);

    /**
     * Construct a new Merchant Identity
     * @param MerchantRow $Merchant
     * @param IntegrationRow $APIData
     * @throws IntegrationException
     * @throws \Exception
     */
    public function __construct(MerchantRow $Merchant, IntegrationRow $APIData) {
        $this->merchant = $Merchant;
        $this->integration = $APIData;
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
            if($Request->getResult() === IntegrationRequestRow::ENUM_RESULT_SUCCESS)
                $this->parseRequest($Request);
        }
    }

    /**
     * Submit a new transaction
     * @param OrderRow $Order
     * @param array $post
     * @return TransactionRow
     */
    function submitNewTransaction(OrderRow $Order, Array $post) {
        $Integration = $this->integration->getIntegration();
        return $Integration->submitNewTransaction($this, $Order, $post);
    }

    function createOrResumeOrder(Array $post) {
        $Integration = $this->integration->getIntegration();
        return $Integration->createOrResumeOrder($this, $post);
    }

    public function getMerchantRow() {
        return $this->merchant;
    }
    public function getIntegrationRow() {
        return $this->integration;
    }

    public function getMarketCode() {
        return "Retail"; // Default or AutoRental or DirectMarketing or ECommerce or FoodRestaurant or HotelLodging or Petroleum or Retail or QSR;
    }


//    public function submitNewTransaction(Array $post) {
//        $Order = OrderRow::createNewOrder($post);
//        OrderRow::insert($Order);
//        $Transaction = $Order->createNewTransaction($post);
//        TransactionRow::insert($Transaction);
//    }
}