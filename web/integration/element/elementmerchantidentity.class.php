<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/12/2016
 * Time: 6:00 PM
 */
namespace Integration\Element;

use Integration\Model\AbstractMerchantIdentity;
use Integration\Model\Ex\IntegrationException;
use Integration\Model\IntegrationRow;
use Integration\Request\Model\IntegrationRequestRow;
use Merchant\Model\MerchantIntegrationRow;
use Merchant\Model\MerchantRow;
use Order\Model\OrderRow;

class ElementMerchantIdentity extends AbstractMerchantIdentity
{
    const DEFAULT_MAX_TRANSACTION_AMOUNT = 12000;
    const DEFAULT_ANNUAL_CARD_VOLUME = 12000000;

    protected $creds = array(
        'AcceptorID' => null,
        'AccountID' => null,
        'AccountToken' => null,
        'ApplicationID' => null,
    );

//    protected $entity;

//    protected $tags;
//    protected $created_at;
//    protected $updated_at;
//    protected $AccountID;

//    protected $AccountToken;
//    protected $ApplicationID;
//    protected $AcceptorID;
//    protected $DefaultTerminalID;
    public function __construct(MerchantRow $Merchant, IntegrationRow $APIData, MerchantIntegrationRow $MerchantIntegration=null) {
        parent::__construct($Merchant, $APIData, true);
        if($MerchantIntegration)
            $this->creds = $MerchantIntegration->getCredentials() ?: $this->creds;
    }

    /**
     * Return an array of remote credentials
     * @return Array
     */
    function getCredentials() {
        return $this->creds;
    }

    public function getRemoteID()       { return $this->creds['AcceptorID']; }
//    public function getEntityData()     { return $this->creds['entity']; }
//    public function getTags()           { return $this->creds['tags']; }
//    public function getCreateDate()     { return $this->creds['created_at']; }
//    public function getUpdateDate()     { return $this->creds['updated_at']; }


    public function getAccountID()      { return $this->creds['AccountID']; }

    public function getAccountToken()   { return $this->creds['AccountToken']; }

    public function getDefaultTerminalID() {
        return @$this->creds['DefaultTerminalID'] ?: '0001';
    }

    public function getAcceptorID()     { return $this->creds['AcceptorID']; }
    public function getApplicationID()  { return $this->creds['ApplicationID']; }


    function isProfileComplete(&$message=null) {
        $message = "Complete";
        return true;
    }

    function isProvisioned(&$message=null) {
        if($this->creds['AccountID']) {
            $message = "Yes";
            return true;
        }
        $message = "No";
        return false;
    }

    function canSettleFunds(&$message=null) {
        $message = "No";
        return false;
    }

    /**
     * Remove provision a merchant
     * @param array $post
     * @return mixed
     */
    function provisionRemote(Array $post=array()) {
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
