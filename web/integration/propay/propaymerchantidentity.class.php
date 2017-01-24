<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/12/2016
 * Time: 6:00 PM
 */
namespace Integration\ProPay;

use Integration\Model\AbstractMerchantIdentity;
use Integration\Model\Ex\IntegrationException;
use Integration\Model\IntegrationRow;
use Integration\Request\Model\IntegrationRequestRow;
use Merchant\Model\MerchantRow;
use Order\Model\OrderRow;
use Order\Model\TransactionRow;

class ProPayMerchantIdentity extends AbstractMerchantIdentity
{
    const DEFAULT_MAX_TRANSACTION_AMOUNT = 12000;
    const DEFAULT_ANNUAL_CARD_VOLUME = 12000000;

    protected $entity;
    protected $tags;
    protected $created_at;
    protected $updated_at;

    protected $AuthToken;
    protected $BillerID;


    public function __construct(MerchantRow $Merchant, IntegrationRow $APIData) {
        parent::__construct($Merchant, $APIData);
    }

    public function getRemoteID()       { return $this->BillerID; }
    public function getEntityData()     { return $this->entity; }
    public function getTags()           { return $this->tags; }
    public function getCreateDate()     { return $this->created_at; }
    public function getUpdateDate()     { return $this->updated_at; }


    public function getBillerID()       { return $this->BillerID; }
    public function getAuthToken()       { return $this->AuthToken; }

    function isProfileComplete(&$message=null) {
        $message = "Complete";
        return true;
    }

    function isProvisioned(&$message=null) {
//        if($this->AccountID) {
//            $message = "Yes";
//            return true;
//        }
        $message = "No";
        return false;
    }

    function canSettleFunds(&$message=null) {
        $message = "No";
        return false;
    }

    /**
     * Remove provision a merchant
     * @return mixed
     */
    function provisionRemote() {
        // TODO: Implement provisionRemote() method.
    }

    /**
     * Settle funds to a merchant
     * @return mixed
     */
    function settleRemote() {
        // TODO: Implement settleRemote() method.
    }



    protected function parseRequest(IntegrationRequestRow $APIRequest) {
        $response = $APIRequest->getResponse();
        $data = json_decode($response, true);
        if(!$data)
            throw new IntegrationException("Response failed to parse JSON");

        if($APIRequest->getResult() !== IntegrationRequestRow::ENUM_RESULT_SUCCESS)
            throw new IntegrationException("Only successful responses may be parsed");

        switch($APIRequest->getIntegrationType()) {
            case IntegrationRequestRow::ENUM_TYPE_MERCHANT_IDENTITY:
                $this->AccountID = $data['AccountID'];
                $this->AccountToken = $data['AccountToken'];
                $this->ApplicationID = $data['ApplicationID'];
                $this->AcceptorID = $data['AcceptorID'];
                $this->DefaultTerminalID = @$data['DefaultTerminalID'];
                $this->created_at = $APIRequest->getDate();
                $this->updated_at = $APIRequest->getDate();
                break;

//            case IntegrationRequestRow::ENUM_TYPE_MERCHANT_PAYMENT:
//                $this->payment_instrument_id = $data['id'];
//                $this->payment_instrument_fingerprint = $data['fingerprint'];
//                break;

            case IntegrationRequestRow::ENUM_TYPE_TRANSACTION:
                break;
        }
    }


    /**
     * Calculate Transaction Service Fee
     * @param OrderRow $OrderRow
     * @return mixed
     */
    public function calculateConvenienceFee(OrderRow $OrderRow) {
        $Merchant = $this->getMerchantRow();
        $amount = $OrderRow->getAmount();
        $fee = $Merchant->getConvenienceFeeFlat();
        $fee += $amount * floatval($Merchant->getConvenienceFeeVariable())/100;
        if($Merchant->getConvenienceFeeLimit() > 0 && $fee > $Merchant->getConvenienceFeeLimit())
            $fee = $Merchant->getConvenienceFeeLimit();
        return round($fee, 2);
    }


    /**
     * Calculate Transaction Service Fee
     * @param OrderRow $OrderRow
     * @param $action
     * @return mixed
     */
    public function calculateServiceFee(OrderRow $OrderRow, $action) {
        switch(strtolower($action)) {
            default:
            case 'settled':
            case 'authorized':
                return 0;
        }
    }


    // Static

}
