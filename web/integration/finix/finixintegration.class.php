<?php
/**
 * Created by PhpStorm.
 * Merchant: ari
 * Date: 9/2/2016
 * Time: 11:13 AM
 */
namespace Integration\Finix;

use Integration\Model;
use Integration\Model\AbstractIntegration;
use Integration\Model\IntegrationRow;
use Integration\Model\Ex\IntegrationException;
use Integration\Request\Model\IntegrationRequestRow;
use Merchant\Model\MerchantRow;

// https://finix-payments.github.io/simonpay-docs/?shell#step-1-create-an-identity-for-a-merchant
class FinixIntegration extends AbstractIntegration
{
    const _CLASS = __CLASS__;
    const POST_URL = "identities/";

    const DEFAULT_MAX_TRANSACTION_AMOUNT = 12000;
    const DEFAULT_ANNUAL_CARD_VOLUME = 12000000;

    public function __construct(IntegrationRow $IntegrationRow) {
        parent::__construct($IntegrationRow);
    }

    /**
     * @param MerchantRow $Merchant
     * @return FinixIdentityRequestParser
     */
    public function createMerchantIdentity(MerchantRow $Merchant) {
        $MerchantRequestRow = $Merchant->fetchAPIRequest($this);
        // If identity was already found, return it
        if($MerchantRequestRow)
            return new FinixIdentityRequestParser($MerchantRequestRow);


        // No remote identity found, so we have to provision the merchant
        $FinixAPIUtil = new FinixAPIUtil();
        $ch = curl_init();
        $result = IntegrationRequestRow::ENUM_RESULT_FAIL;

        // Disable SSL verification
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        $request = $FinixAPIUtil->prepareMerchantIdentityCURL($this, $Merchant, $ch);
        if(!$response = curl_exec($ch)) {
            $response = curl_error($ch);
            trigger_error($response);
            $result = IntegrationRequestRow::ENUM_RESULT_ERROR;
        }
        curl_close($ch);

        // Create new Merchant Identity
        $MerchantRequestRow = new IntegrationRequestRow(array(
            'type' => IntegrationRequestRow::ENUM_TYPE_MERCHANT,
            'type_id' => $Merchant->getID(),
            'integration_id' => $this->getIntegrationRow()->getID(),
            'request' => $request,
            'response' => $response,
            'result' => $result
        ));

        $MerchantIdentity = new FinixIdentityRequestParser($MerchantRequestRow);
        try {
            $MerchantIdentity->getParsedResponseData();
            if($MerchantIdentity->requestIsSuccessful())
                $MerchantRequestRow->setResult(IntegrationRequestRow::ENUM_RESULT_SUCCESS);

        } catch (IntegrationException $ex) {
            $MerchantRequestRow->setResult(IntegrationRequestRow::ENUM_RESULT_ERROR);
        }

        // Insert Request
        IntegrationRequestRow::insert($MerchantRequestRow);

        return $MerchantIdentity;
    }

    /**
     * @param MerchantRow $Merchant
     * @return FinixIdentityRequestParser
     */
    public function createPaymentInstrument(MerchantRow $Merchant) {
        $MerchantRequestRow = $Merchant->fetchAPIRequest($this);
        // If identity was already found, return it
        if($MerchantRequestRow)
            return new FinixIdentityRequestParser($MerchantRequestRow);


        // No remote identity found, so we have to provision the merchant
        $FinixAPIUtil = new FinixAPIUtil();
        $ch = curl_init();
        $result = IntegrationRequestRow::ENUM_RESULT_FAIL;

        // Disable SSL verification
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        $request = $FinixAPIUtil->prepareMerchantIdentityCURL($this, $Merchant, $ch);
        if(!$response = curl_exec($ch)) {
            $response = curl_error($ch);
            trigger_error($response);
            $result = IntegrationRequestRow::ENUM_RESULT_ERROR;
        }
        curl_close($ch);

        // Create new Merchant Identity
        $MerchantRequestRow = new IntegrationRequestRow(array(
            'type' => IntegrationRequestRow::ENUM_TYPE_MERCHANT,
            'type_id' => $Merchant->getID(),
            'integration_id' => $this->getIntegrationRow()->getID(),
            'request' => $request,
            'response' => $response,
            'result' => $result
        ));

        $MerchantIdentity = new FinixIdentityRequestParser($MerchantRequestRow);
        try {
            $MerchantIdentity->getParsedResponseData();
            if($MerchantIdentity->requestIsSuccessful())
                $MerchantRequestRow->setResult(IntegrationRequestRow::ENUM_RESULT_SUCCESS);

        } catch (IntegrationException $ex) {
            $MerchantRequestRow->setResult(IntegrationRequestRow::ENUM_RESULT_ERROR);
        }

        // Insert Request
        IntegrationRequestRow::insert($MerchantRequestRow);

        return $MerchantIdentity;
    }


    /**
     * @param MerchantRow $Merchant
     * @return FinixIdentityRequestParser
     */
    public function provisionMerchant(MerchantRow $Merchant) {
        $MerchantRequestRow = $Merchant->fetchAPIRequest($this);
        // If identity was already found, return it
        if($MerchantRequestRow)
            return new FinixIdentityRequestParser($MerchantRequestRow);


        // No remote identity found, so we have to provision the merchant
        $FinixAPIUtil = new FinixAPIUtil();
        $ch = curl_init();
        $result = IntegrationRequestRow::ENUM_RESULT_FAIL;

        // Disable SSL verification
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        $request = $FinixAPIUtil->prepareMerchantIdentityCURL($this, $Merchant, $ch);
        if(!$response = curl_exec($ch)) {
            $response = curl_error($ch);
            trigger_error($response);
            $result = IntegrationRequestRow::ENUM_RESULT_ERROR;
        }
        curl_close($ch);

        // Create new Merchant Identity
        $MerchantRequestRow = new IntegrationRequestRow(array(
            'type' => IntegrationRequestRow::ENUM_TYPE_MERCHANT,
            'type_id' => $Merchant->getID(),
            'integration_id' => $this->getIntegrationRow()->getID(),
            'request' => $request,
            'response' => $response,
            'result' => $result
        ));

        $MerchantIdentity = new FinixIdentityRequestParser($MerchantRequestRow);
        try {
            $MerchantIdentity->getParsedResponseData();
            if($MerchantIdentity->requestIsSuccessful())
                $MerchantRequestRow->setResult(IntegrationRequestRow::ENUM_RESULT_SUCCESS);

        } catch (IntegrationException $ex) {
            $MerchantRequestRow->setResult(IntegrationRequestRow::ENUM_RESULT_ERROR);
        }

        // Insert Request
        IntegrationRequestRow::insert($MerchantRequestRow);
        return $MerchantIdentity;
    }

}

