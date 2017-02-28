<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/12/2016
 * Time: 6:00 PM
 */
namespace Integration\ProtectPay;

use Integration\Model\AbstractMerchantIdentity;
use Integration\Model\Ex\IntegrationException;
use Integration\Model\IntegrationRow;
use Integration\Request\Model\IntegrationRequestRow;
use Merchant\Model\MerchantIntegrationRow;
use Merchant\Model\MerchantRow;
use Order\Fee\Model\FeeRow;
use Order\Model\OrderRow;

class ProtectPayMerchantIdentity extends AbstractMerchantIdentity
{
    const DEFAULT_MAX_TRANSACTION_AMOUNT = 12000;
    const DEFAULT_ANNUAL_CARD_VOLUME = 12000000;

    protected $creds = array(
        'MerchantProfileId' => null,
        'propayAccountNum' => null,
        'propayPassword' => null,
    );

    public function __construct(MerchantRow $Merchant, IntegrationRow $APIData, MerchantIntegrationRow $MerchantIntegration=null) {
        parent::__construct($Merchant, $APIData);
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


    public function getRemoteID()               { return $this->getMerchantProfileId(); }

    public function getMerchantProfileId()      { return $this->creds['MerchantProfileId']; }
    public function getProPayAccountNum()       { return $this->creds['propayAccountNum']; }
    public function getProPayPassword()         { return $this->creds['propayPassword']; }

    public function getProcessorCredentials($param) {
        $APIData = $this->getIntegrationRow();
        $cred = json_decode($APIData->getAPICredentialString(), true);
        return $cred[$param];
    }
    public function getAuthenticationToken()    { return $this->getProcessorCredentials('AuthenticationToken'); }
    public function getBillerAccountId()        { return $this->getProcessorCredentials('BillerAccountId'); }
    public function getCertStr()                { return $this->getProcessorCredentials('certStr'); }
    public function getTermId()                 { return $this->getProcessorCredentials('termId'); }

    function isProfileComplete(&$message=null) {
        $message = "Complete";
        return true;
    }

    function isProvisioned(&$message=null) {
        if($this->creds['MerchantProfileId']) {
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
     * @throws IntegrationException
     */
    function provisionRemote(Array $post=array()) {
        $APIUtil = new ProtectPayAPIUtil();
        $IntegrationRow = $this->getIntegrationRow();
        $Integration = $IntegrationRow->getIntegration();

        if(!$this->creds['propayAccountNum']) {
            $Request = $APIUtil->prepareProPayMerchantProvisionRequest($this, $post);

            $Integration->execute($this, $Request);

            // Try parsing the response
            $data = $APIUtil->decodeXMLResponse($Request->getResponse());
            $Request->setResponseCode($data['XMLTrans']['status']);

            if ($Request->getResponseCode() !== '00')
                throw new IntegrationException($Request->getResponseCode() . ' : ' . $Request->getResponse());

            $this->creds['propayAccountNum'] = $data['XMLTrans']['accntNum'];
            $this->creds['propayPassword'] = $data['XMLTrans']['password'];
            MerchantIntegrationRow::writeMerchantIdentity($this);

            $Request->setResponseMessage("Success");
            $Request->setResult(IntegrationRequestRow::ENUM_RESULT_SUCCESS);

            IntegrationRequestRow::insert($Request);
        }

        if(!$this->creds['MerchantProfileId']) {
            $Request = $APIUtil->prepareProtectPayMerchantProvisionRequest($this, $post);

            $Integration->execute($this, $Request);

            // Try parsing the response
            $data = json_decode($Request->getResponse(), true);
            $Request->setResponseCode($data['RequestResult']['ResultCode']);

            if ($Request->getResponseCode() !== '00')
                throw new IntegrationException($Request->getResponseCode() . ' : ' . $Request->getResponseMessage());

            $this->creds['MerchantProfileId'] = $data['ProfileId'];
            MerchantIntegrationRow::writeMerchantIdentity($this);

            $Request->setResponseMessage(@$data['RequestResult']['ResultMessage'] ?: $data['RequestResult']['ResultValue']);
            $Request->setResult(IntegrationRequestRow::ENUM_RESULT_SUCCESS);

            IntegrationRequestRow::insert($Request);
        }

        if(!$this->isProvisioned())
            throw new IntegrationException("Merchant Failed to Provision");
    }


    /**
     * Settle funds to a merchant
     * @return mixed
     * @throws IntegrationException
     */
    function settleRemote() {
        throw new IntegrationException("Not implemented");
    }


    protected function parseRequest(IntegrationRequestRow $APIRequest) {
        throw new IntegrationException("Not implemented");
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
     * Calculate all transaction fees
     * @param OrderRow $OrderRow
     * @return FeeRow[]
     */
    public function calculateFees(OrderRow $OrderRow) {

    }


    // Static
}
