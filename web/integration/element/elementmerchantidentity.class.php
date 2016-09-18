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
use Merchant\Model\MerchantRow;

class ElementMerchantIdentity extends AbstractMerchantIdentity
{
    const DEFAULT_MAX_TRANSACTION_AMOUNT = 12000;
    const DEFAULT_ANNUAL_CARD_VOLUME = 12000000;

    protected $id;
    protected $entity;
    protected $tags;
    protected $created_at;
    protected $updated_at;

    public function __construct(MerchantRow $Merchant, IntegrationRow $APIData) {
        parent::__construct($Merchant, $APIData);
    }

//    abstract function hasPaymentInstrument();

    public function getRemoteID()       { return $this->id; }
    public function getEntityData()     { return $this->entity; }
    public function getTags()           { return $this->tags; }
    public function getCreateDate()     { return $this->created_at; }
    public function getUpdateDate()     { return $this->updated_at; }


    function isProfileComplete(&$message=null) {
        $message = "Incomplete";
        return false;
    }

    function isProvisioned(&$message=null) {
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
        throw new IntegrationException("TODO");
//
//        $errorMessage = null;
//        if(!empty($data['_embedded'])) {
//            if(!empty($data['_embedded']['errors'])) {
//                foreach($data['_embedded']['errors'] as $i => $errInfo) {
//                    $errorMessage .= ($errorMessage ? "\n" : '') . '#' . ($i+1) . ' ' . $errInfo['code'] . ': ' . $errInfo['message'];
//                }
//            }
//        }
//
//        if($errorMessage)
//            throw new IntegrationException($errorMessage);
//
//        if(!empty($data['entity']))
//            $this->entity = $data['entity'];
//
//        switch($APIRequest->getIntegrationType()) {
//            case IntegrationRequestRow::ENUM_TYPE_MERCHANT:
//                $this->id = $data['id'];
//                $this->updated_at = $data['updated_at'];
//                $this->created_at = $data['created_at'];
//                break;
//
//            case IntegrationRequestRow::ENUM_TYPE_MERCHANT_PROVISION:
//                break;
//            case IntegrationRequestRow::ENUM_TYPE_PAYMENT_INSTRUMENT:
//                break;
//            case IntegrationRequestRow::ENUM_TYPE_TRANSACTION:
//                break;
//        }
    }


    // Static

    public static function prepareMerchantRequest(IntegrationRequestRow $NewRequest, MerchantRow $M) {
        $POST = array(
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
    }

}
