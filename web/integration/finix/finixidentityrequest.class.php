<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/12/2016
 * Time: 6:00 PM
 */
namespace Integration\Finix;

use Integration\Model\AbstractIntegration;
use Integration\Model\IntegrationRequestParser;
use Integration\Model\Ex\IntegrationException;
use Merchant\Model\MerchantRow;

// TODO: IntegrationRequestParser => IntegrationRequest
class FinixIdentityRequestParser extends IntegrationRequestParser
{
    const POST_URL = "/identities/";

    const DEFAULT_MAX_TRANSACTION_AMOUNT = 12000;
    const DEFAULT_ANNUAL_CARD_VOLUME = 12000000;

    protected function parseResponse() {
        $response = $this->getRequestRow()->getResponse();
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

        if(empty($data['entity']))
            throw new IntegrationException("Missing response key: 'entity'");
//        $id = $data['id'];
//        $entity = $data['entity'];
        return $data;
    }

    public function requestIsSuccessful() {
//        if($this->getRequestRow()->getResult() !== IntegrationRequestRow::ENUM_RESULT_SUCCESS)
//            return false;
        $data = $this->getParsedResponseData();
        if(empty($data['id']))
            return false;

        return true;
    }

    public function parseRemoteID() {
        $data = $this->getParsedResponseData();
        return $data['id'];
    }

    public function parseRemoteCreateDate() {
        $data = $this->getParsedResponseData();
        return $data['created_at'];
    }

    public function parseRemoteUpdateDate() {
        $data = $this->getParsedResponseData();
        return $data['updated_at'];
    }


    // Static

    /**
     * @param AbstractIntegration $Integration
     * @param MerchantRow $M
     * @param resource $ch
     * @return string
     */
    public static function prepareCURL(AbstractIntegration $Integration, MerchantRow $M, $ch) {
        $APIData = $Integration->getIntegrationRow();
        $url = $APIData->getAPIURLBase() . self::POST_URL;
        $userpass = $APIData->getAPIUsername() . ':' . $APIData->getAPIPassword();
        $headers = array(
            "Content-Type: application/vnd.json+api",
        );

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

        // Set CURL options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_USERPWD, $userpass);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);

        return $request;

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
