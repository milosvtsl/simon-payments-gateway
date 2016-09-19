<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/12/2016
 * Time: 6:00 PM
 */
namespace Integration\Finix;

use Integration\Model\AbstractMerchantIdentity;
use Integration\Model\Ex\IntegrationException;
use Integration\Model\IntegrationRow;
use Integration\Request\Model\IntegrationRequestRow;
use Merchant\Model\MerchantRow;

class FinixMerchantIdentity extends AbstractMerchantIdentity
{
    const DEFAULT_MAX_TRANSACTION_AMOUNT = 12000;
    const DEFAULT_ANNUAL_CARD_VOLUME = 12000000;

    protected $id;
    protected $entity;
    protected $tags;
    protected $created_at;
    protected $updated_at;
    protected $payment_instrument_id;
    protected $payment_instrument_fingerprint;
    protected $onboarding_state;
    protected $settlement_enabled;
    protected $processing_enabled;
    protected $processor;
    protected $merchant_profile;
    protected $verification;
    protected $identity;

    public function __construct(MerchantRow $Merchant, IntegrationRow $APIData) {
        parent::__construct($Merchant, $APIData);
    }

//    abstract function hasPaymentInstrument();

    public function getRemoteID()       {
//        if(!$this->id) throw new IntegrationException("Remote ID not set");
        return $this->id;
    }
    public function getEntityData()     { return $this->entity; }
    public function getTags()           { return $this->tags; }
    public function getCreateDate()     { return $this->created_at; }
    public function getUpdateDate()     { return $this->updated_at; }


    function isProfileComplete(&$message=null) {
        $message = "Ready";
        return true;
    }

    function isProvisioned(&$message=null) {
        if($this->identity) {
            $message = "Ready";
            return true;
        }
        $message = "No";
        return false;
    }

    function canSettleFunds(&$message=null) {
        if($this->payment_instrument_id) {
            $message = "Ready";
            return true;
        }
        $message = "Needs PI";
        return false;
    }

    /**
     * Remove provision a merchant
     */
    function provisionRemote() {
        if($this->isProvisioned())
            throw new IntegrationException("Merchant is already provisioned");

        $IntegrationRow = $this->getIntegrationRow();
        $Integration = $IntegrationRow->getIntegration();

        if(!$this->id) {
            // Create Request
            $IdentityRequest = $this->prepareMerchantIdentityRequest();

            // Execute Identity Request
            $Integration->execute($IdentityRequest);
            $this->parseRequest($IdentityRequest);
            if (!$this->id)
                throw new IntegrationException("Identity Request failed to return id");
            unset($IdentityRequest);
        }

        if(!$this->payment_instrument_id) {
            // Create Payment Instrument Request
            $PaymentRequest = $this->prepareMerchantPaymentInstrumentRequest();

            // Execute Request
            $Integration->execute($PaymentRequest);
            $this->parseRequest($PaymentRequest);
            if (!$this->payment_instrument_id)
                throw new IntegrationException("Payment Instrument Request failed to return id");
            unset($PaymentRequest);
        }

        // Create Merchant Provision Request
        $ProvisionRequest = $this->prepareMerchantProvisionRequest();

        // Execute Request
        $Integration->execute($ProvisionRequest);
        $this->parseRequest($ProvisionRequest);
        if(!$this->id)
            throw new IntegrationException("Payment Instrument Request failed to return id");
        unset($ProvisionRequest);


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

        $errorMessage = null;
        if(!empty($data['_embedded'])) {
            if(!empty($data['_embedded']['errors'])) {
                foreach($data['_embedded']['errors'] as $i => $errInfo) {
                    $errorMessage .= ($errorMessage ? "\n" : '') . '#' . ($i+1) . ' ' . $errInfo['code'] . ': ' . $errInfo['message'];
                }
            }
        }

        if($errorMessage)
            throw new IntegrationException($errorMessage);

        if(!empty($data['entity']))
            $this->entity = $data['entity'];

        switch($APIRequest->getIntegrationType()) {
            case IntegrationRequestRow::ENUM_TYPE_MERCHANT_IDENTITY:
                $this->id = $data['id'];
                $this->updated_at = $data['updated_at'];
                $this->created_at = $data['created_at'];
                break;

            case IntegrationRequestRow::ENUM_TYPE_MERCHANT_PROVISION:
                $this->identity = $data['identity'];
                $this->verification = $data['verification'];
                $this->merchant_profile = $data['merchant_profile'];
                $this->processor = $data['processor'];
                $this->processing_enabled = $data['processing_enabled'];
                $this->settlement_enabled = $data['settlement_enabled'];
                $this->onboarding_state = $data['onboarding_state'];
                break;
            case IntegrationRequestRow::ENUM_TYPE_MERCHANT_PAYMENT:
                $this->payment_instrument_id = $data['id'];
                $this->payment_instrument_fingerprint = $data['fingerprint'];
                break;
            case IntegrationRequestRow::ENUM_TYPE_TRANSACTION:
                break;
        }
    }


    public function prepareMerchantIdentityRequest() {
        $IntegrationRow = $this->getIntegrationRow();
        $NewRequest = IntegrationRequestRow::prepareNew(
            $IntegrationRow->getClassPath(),
            $IntegrationRow->getID(),
            IntegrationRequestRow::ENUM_TYPE_MERCHANT_IDENTITY,
            $this->getMerchantRow()->getID()
        );

        $M = $this->getMerchantRow();
        $POST = array(
            'tags' => array(
                'key' => 'value'
            ),
            'entity' => array(
                "last_name" => $M->getMainContactLastName(),                        // "Sunkhronos",
                "max_transaction_amount" => self::DEFAULT_MAX_TRANSACTION_AMOUNT,   // 120000,
                // "has_accepted_credit_cards_previously" => false,                 // true,
                "default_statement_descriptor" => substr($M->getName(), 0, 20),     // "Golds Gym",
                "personal_address" => array(
                    "city" => $M->getCity(),                                        // "San Mateo",
                    "country" => $M->getCountryCode(),                              // "USA",
                    "region" => $M->getRegionCode(),                                // "CA",
                    "line2" => $M->getAddress2(),                                   // "Apartment 7",
                    "line1" => $M->getAddress(),                                    // "741 Douglass St",
                    "postal_code" => $M->getZipCode(),                              // "94114"
                ),
                "incorporation_date" => array(
                    "year" => date('Y', strtotime($M->getOpenDate())),              // "year" => 1978,
                    "day" => date('d', strtotime($M->getOpenDate())),               // "day" => 27,
                    "month" => date('m', strtotime($M->getOpenDate())),             // "month" => 6
                ),
                "business_address" => array(
                    "city" => $M->getCity(),                                        // "San Mateo",
                    "country" => $M->getCountryCode(),                              // "USA",
                    "region" => $M->getRegionCode(),                                // "CA",
                    "line2" => $M->getAddress2(),                                   // "Apartment 7",
                    "line1" => $M->getAddress(),                                    // "741 Douglass St",
                    "postal_code" => $M->getZipCode(),                              // "94114"
                ),
                "first_name" => $M->getMainContactFirstName(),                      // "dwayne",
                "title" => $M->getTitle(),                                          // "CEO",
                "business_tax_id" => $M->getBusinessTaxID(),                        // "123456789",
                "doing_business_as" => $M->getName(),                               // "Golds Gym",
                "principal_percentage_ownership" => 100,                            // 50,
                "email" => $M->getMainEmailID(),                                    // "user@example.org",
                "mcc" => 3137,                                                      // "0742",
                "phone" => $M->getTelephone(),                                      // "1234567890",
                "business_name" => $M->getName(),                                   // "Golds Gym",
                "tax_id" => $M->getTaxID(),                                         // "123456789",
                "business_type" => $M->getBusinessType(),                           // "INDIVIDUAL_SOLE_PROPRIETORSHIP",
                "business_phone" => $M->getTelephone(),                             // "+1 (408) 756-4497",
                "dob" => array(
                    "year" => date('Y', strtotime($M->getDOB())),                   // "year" => 1978,
                    "day" => date('d', strtotime($M->getDOB())),                    // "day" => 27,
                    "month" => date('m', strtotime($M->getDOB())),                  // "month" => 6
                ),
                "url" => $M->getURL(),                                              // "www.GoldsGym.com",
                "annual_card_volume" => self::DEFAULT_ANNUAL_CARD_VOLUME,           // 12000000
            )
        );

        $request = json_encode($POST, JSON_PRETTY_PRINT);
        $NewRequest->setRequest($request);
        return $NewRequest;
    }


    public function prepareMerchantPaymentInstrumentRequest() {
        $IntegrationRow = $this->getIntegrationRow();
        $NewRequest = IntegrationRequestRow::prepareNew(
            $IntegrationRow->getClassPath(),
            $IntegrationRow->getID(),
            IntegrationRequestRow::ENUM_TYPE_MERCHANT_PAYMENT,
            $this->getMerchantRow()->getID()
        );

        $M = $this->getMerchantRow();
        $POST = array(
            "account_type" => $M->getPayoutAccountType(),
            "name" => $M->getPayoutAccountName(),
            "tags" => array(
                "Bank Account" => "Company Account"
            ),
            "country" => "USA",
            "bank_code" => $M->getPayoutBankCode(),
            "account_number" => $M->getPayoutAccountNumber(),
            "type" => $M->getPayoutType(),
            "identity" => $this->getRemoteID()
        );

        $request = json_encode($POST, JSON_PRETTY_PRINT);
        $NewRequest->setRequest($request);
        return $NewRequest;

    }

    public function prepareMerchantProvisionRequest() {

        $IntegrationRow = $this->getIntegrationRow();
        $Integration = $IntegrationRow->getIntegration();
        $NewRequest = IntegrationRequestRow::prepareNew(
            $IntegrationRow->getClassPath(),
            $IntegrationRow->getID(),
            IntegrationRequestRow::ENUM_TYPE_MERCHANT_PROVISION,
            $this->getMerchantRow()->getID()
        );
        $url = $Integration->getRequestURL($NewRequest);
        $url = str_replace(':IDENTITY_ID', $this->getRemoteID(), $url);
        $NewRequest->setRequestURL($url);

//        $M = $this->getMerchantRow();
        $POST = array(
            'tags' => array(
                'key' => 'value'
            )
        );

        $request = json_encode($POST, JSON_PRETTY_PRINT);
        $NewRequest->setRequest($request);
        return $NewRequest;

    }
}

//{
//  "id" : "IDhnR4KxxqzQczVdXjnm4DLH",
//  "entity" : {
//        "title" : "CEO",
//    "first_name" : "Sandra",
//    "last_name" : "Test",
//    "email" : "sandra@test.com",
//    "business_name" : "Interamerica Data Florida",
//    "business_type" : "INDIVIDUAL_SOLE_PROPRIETORSHIP",
//    "doing_business_as" : "Interamerica Data Florida",
//    "phone" : "305 9828371",
//    "business_phone" : "305 9828371",
//    "personal_address" : {
//            "line1" : "Fake Address 123",
//      "line2" : "#101",
//      "city" : "Miami",
//      "region" : null,
//      "postal_code" : "33147",
//      "country" : "USA"
//    },
//    "business_address" : {
//            "line1" : "Fake Address 123",
//      "line2" : "#101",
//      "city" : "Miami",
//      "region" : null,
//      "postal_code" : "33147",
//      "country" : "USA"
//    },
//    "mcc" : 3137,
//    "dob" : {
//            "day" : 3,
//      "month" : 2,
//      "year" : 1978
//    },
//    "max_transaction_amount" : 12000,
//    "amex_mid" : null,
//    "discover_mid" : null,
//    "url" : "http://paylogicnetwork.com",
//    "annual_card_volume" : 12000000,
//    "has_accepted_credit_cards_previously" : false,
//    "incorporation_date" : {
//            "day" : 4,
//      "month" : 3,
//      "year" : 2015
//    },
//    "principal_percentage_ownership" : 100,
//    "short_business_name" : null,
//    "tax_id_provided" : true,
//    "business_tax_id_provided" : true,
//    "default_statement_descriptor" : "Interamerica Data Fl"
//  },
//  "tags" : {
//        "key" : "value"
//  },
//  "created_at" : "2016-09-13T15:33:22.23Z",
//  "updated_at" : "2016-09-13T15:33:22.23Z",
//  "_links" : {
//        "self" : {
//            "href" : "https://simonpay-staging.finix.io/identities/IDhnR4KxxqzQczVdXjnm4DLH"
//    },
//    "verifications" : {
//            "href" : "https://simonpay-staging.finix.io/identities/IDhnR4KxxqzQczVdXjnm4DLH/verifications"
//    },
//    "merchants" : {
//            "href" : "https://simonpay-staging.finix.io/identities/IDhnR4KxxqzQczVdXjnm4DLH/merchants"
//    },
//    "settlements" : {
//            "href" : "https://simonpay-staging.finix.io/identities/IDhnR4KxxqzQczVdXjnm4DLH/settlements"
//    },
//    "authorizations" : {
//            "href" : "https://simonpay-staging.finix.io/identities/IDhnR4KxxqzQczVdXjnm4DLH/authorizations"
//    },
//    "transfers" : {
//            "href" : "https://simonpay-staging.finix.io/identities/IDhnR4KxxqzQczVdXjnm4DLH/transfers"
//    },
//    "payment_instruments" : {
//            "href" : "https://simonpay-staging.finix.io/identities/IDhnR4KxxqzQczVdXjnm4DLH/payment_instruments"
//    },
//    "disputes" : {
//            "href" : "https://simonpay-staging.finix.io/identities/IDhnR4KxxqzQczVdXjnm4DLH/disputes"
//    },
//    "application" : {
//            "href" : "https://simonpay-staging.finix.io/applications/APeALXKsYEYgsn9QBdHmy9hP"
//    }
//  }
//}
