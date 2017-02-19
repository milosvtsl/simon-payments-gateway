<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/21/2016
 * Time: 7:29 PM
 */
namespace Integration\ProtectPay;

use Integration\Model\AbstractMerchantIdentity;
use Integration\Model\Ex\IntegrationException;
use Integration\Model\IntegrationRow;
use Integration\Request\Model\IntegrationRequestRow;
use Merchant\Model\MerchantRow;
use Order\Model\OrderRow;
use Order\Model\TransactionRow;

class ProtectPayAPIUtil {

    //Change this URL to point to Production by changing it to https://api.propay.com/... instead of https://xmltestapi.propay.com/....
    const POST_URL_PAYERS = "/ProtectPay/Payers/"; // https://xmltestapi.propay.com

    /**
     * @param ProtectPayMerchantIdentity|AbstractMerchantIdentity $MerchantIdentity
     * @param IntegrationRequestRow $Request
     * @return mixed|string
     * @throws IntegrationException
     */
    public function executeAPIRequest(
        ProtectPayMerchantIdentity $MerchantIdentity,
        IntegrationRequestRow $Request
    ) {

        $url = $Request->getRequestURL();
        $userpass = $MerchantIdentity->getBillerAccountId() . ':' . $MerchantIdentity->getAuthenticationToken();
        $Auth_Header = "Basic " . base64_encode($userpass);
        $headers = array(
//            "Content-Type: application/soap+xml; charset=utf-8",
            "Content-type: application/json; charset=utf-8",
            "Content-Length: ". strlen($Request->getRequest()),
            "Authorization: " . $Auth_Header,
        );

        // Init curl
        $ch = curl_init();

        // Disable SSL verification
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        // Set CURL options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//        curl_setopt($ch, CURLOPT_USERPWD, $userpass);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if($Request->getRequest()) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $Request->getRequest());
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        }
//        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);

        $response = curl_exec($ch);

        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

        if(!$response) {
            $response = curl_error($ch);
            if($response)
                trigger_error($response);

            $Request->setResult(IntegrationRequestRow::ENUM_RESULT_ERROR);
        } else {

            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $header = substr($response, 0, $header_size);
            if($httpcode !== 200) {
                throw new IntegrationException("Invalid Response: " . $header);
            }
            $body = substr($response, $header_size);
            $response = $body;
        }

        return $response;
    }


    /**
     * @param ProtectPayMerchantIdentity $MerchantIdentity
     * @param array $post
     * @return IntegrationRequestRow
     */
    public function prepareProPayMerchantProvisionRequest(
        ProtectPayMerchantIdentity $MerchantIdentity,
        Array $post
    ) {
        $Merchant = $MerchantIdentity->getMerchantRow();
        $IntegrationRow = $MerchantIdentity->getIntegrationRow();

        $Request = IntegrationRequestRow::prepareNew(
            $MerchantIdentity,
            IntegrationRequestRow::ENUM_TYPE_MERCHANT_IDENTITY
        );

        $MCCCode = $Merchant->getMerchantMCC();
        $url = ProtectPayIntegration::REST_DOMAIN_PROPAY; //  . ProtectPayIntegration::POST_URL_MERCHANT_IDENTITY;
        if($IntegrationRow->getAPIType() == IntegrationRow::ENUM_API_TYPE_TESTING) {
            $url = ProtectPayIntegration::REST_DOMAIN_PROPAY_TEST;
            $MCCCode = 5499;
        }

        $Request->setRequestURL($url);


        $params = array(
            "transType" => '01',
            "AccountCountryCode" => $Merchant->getCountryCode(),
            "accountName" => $Merchant->getPayoutAccountName(),
            "AccountNumber" => $Merchant->getPayoutAccountNumber(),
            "AccountOwnershipType" => $Merchant->getPayoutAccountType() ?: 'Personal',
            "accountType" => 'C',
            "BusinessAddress" => $Merchant->getAddress(),
            "BusinessAddress2" => $Merchant->getAddress2(),
            "BusinessCity" => $Merchant->getCity(),
            "BusinessCountry" => $Merchant->getCountryCode(),
            "BusinessLegalName" => $Merchant->getName(),
            "BusinessState" => $Merchant->getState(),
            "BusinessZip" => $Merchant->getZipCode(),
            "dayPhone" => $Merchant->getTelephone(),
            "DoingBusinessAs" => @$post['billing_descriptor'] ?: $Merchant->getShortName(),
            "EIN" => $Merchant->getBusinessTaxID(),
            "MCCCode" => $MCCCode,
            "RoutingNumber" => $Merchant->getPayoutRoutingNumber(),
            "sourceEmail" => $Merchant->getMainEmailID()
        );

        $XMLParams = '';
        foreach($params as $name => $value)
            $XMLParams .= "\n\t<{$name}>" . htmlentities($value) . "</{$name}>";

        $MyCertStr = $MerchantIdentity->getCertStr();
        $XML = <<<XML
<?xml version='1.0'?>
<!DOCTYPE Request.dtd>
<XMLRequest>
    <certStr>{$MyCertStr}</certStr>
    <class>partner</class>
    <XMLTrans>{$XMLParams}
    </XMLTrans>
</XMLRequest>
XML;

        $Request->setRequest($XML);
        return $Request;
    }

    public function prepareProtectPayMerchantProvisionRequest(
        ProtectPayMerchantIdentity $MerchantIdentity,
        Array $post
    ) {
        $Merchant = $MerchantIdentity->getMerchantRow();
        $APIData = $MerchantIdentity->getIntegrationRow();

        $Request = IntegrationRequestRow::prepareNew(
            $MerchantIdentity,
            IntegrationRequestRow::ENUM_TYPE_MERCHANT_IDENTITY
        );

        $url = ProtectPayIntegration::REST_DOMAIN_PROTECTPAY;
        if($APIData->getAPIType() == IntegrationRow::ENUM_API_TYPE_TESTING)
            $url = ProtectPayIntegration::REST_DOMAIN_PROTECTPAY_TEST;
        $url .= ProtectPayIntegration::POST_URL_MERCHANT_IDENTITY;
        $Request->setRequestURL($url);

        $params = array(
            "ProfileName" => @$post['profile_name'] ?: $Merchant->getName(),
            "PaymentProcessor" => "LegacyProPay",
            "ProcessorData" => array(
                array(
                    "ProcessorField" => "certStr",
                    "Value" => $MerchantIdentity->getCertStr(),
                ),
                array(
                    "ProcessorField" => "accountNum",
                    "Value" => $MerchantIdentity->getProPayAccountNum(),
                ),
                array(
                    "ProcessorField" => "termId",
                    "Value" => $MerchantIdentity->getTermId(),
                )
            )
        );

        $JSON = json_encode($params, JSON_PRETTY_PRINT);
        $Request->setRequest($JSON);
        return $Request;
    }

    // ProtectPay API
    public function preparePayerAccountIdRequest(
        ProtectPayMerchantIdentity $MerchantIdentity,
        OrderRow $OrderRow,
        Array $post
    ) {
        $APIData = $MerchantIdentity->getIntegrationRow();

        $Request = IntegrationRequestRow::prepareNew(
            $MerchantIdentity,
            IntegrationRequestRow::ENUM_TYPE_TRANSACTION_PAYER
        );

        $url = ProtectPayIntegration::REST_DOMAIN_PROTECTPAY;
        if($APIData->getAPIType() == IntegrationRow::ENUM_API_TYPE_TESTING)
            $url = ProtectPayIntegration::REST_DOMAIN_PROTECTPAY_TEST;
        $url .= ProtectPayIntegration::POST_URL_TRANSACTION_PAYER;
        $Request->setRequestURL($url);

        $args = array(
            'AuthenticationToken' => $MerchantIdentity->getAuthenticationToken(),           // String 100 Authorization Valid value is a GUID. Value supplied by ProPay. Used to access the API
            'BillerAccountId' => $MerchantIdentity->getBillerAccountId(),                   // String 16 Authorization Value supplied by ProPay. Used to identify the correct collection of tokens.

            'EmailAddress' => $OrderRow->getPayeeEmail(),                                   // String 100 Optional Used to identify a payer.
            'ExternalId1' => $OrderRow->getUID(),                                           // String 50 Optional Used to identify a payer. This is a custom identifier rather than ProtectPay�s. *If more than 50 characters are supplied the value will be truncated to 50
            'ExternalId2' => null,                                                          // String 50 Optional Used to identify a payer. This is a custom identifier rather than ProtectPay�s. *If more than 50 characters are supplied the value will be truncated to 50
            'Name' => $OrderRow->getCardHolderFullName(),                                   // String 50 Required Used to identify a payer.
        );

        $request = json_encode($args, JSON_PRETTY_PRINT);

        $Request->setRequest($request);
        return $Request;
    }

    // ProtectPay API 4.5.3
    public function prepareSaleRequest(
        ProtectPayMerchantIdentity $MerchantIdentity,
        TransactionRow $TransactionRow,
        OrderRow $OrderRow,
        Array $post
    ) {

        $KeySerialNumber = $post['swipe_key_serial_number'];


        $APIData = $MerchantIdentity->getIntegrationRow();
        $json = json_decode($OrderRow->getIntegrationRemoteID(), true);
        $PayerAccountId = $json['PayerAccountId'];
        if(!$PayerAccountId)
            throw new IntegrationException("Invalid PayerAccountId");

        $Request = IntegrationRequestRow::prepareNew(
            $MerchantIdentity,
            IntegrationRequestRow::ENUM_TYPE_TRANSACTION
        );

        $url = ProtectPayIntegration::REST_DOMAIN_PROTECTPAY;
        if($APIData->getAPIType() == IntegrationRow::ENUM_API_TYPE_TESTING)
            $url = ProtectPayIntegration::REST_DOMAIN_PROTECTPAY_TEST;
        $url .= ProtectPayIntegration::POST_URL_TRANSACTION_AUTHORIZE_AND_CAPTURE;
        $url = str_replace('{PayerID}', urlencode($PayerAccountId), $url);

        $Request->setRequestURL($url);

        $TransactionAmount = number_format($OrderRow->getAmount(), 2, '.', '');
        $ConvenienceFeeAmount = $MerchantIdentity->calculateConvenienceFee($OrderRow);
        if($ConvenienceFeeAmount) {
            $TransactionAmount = number_format($OrderRow->getAmount() + $ConvenienceFeeAmount, 2, '.', '');
            $ConvenienceFeeAmount = number_format($ConvenienceFeeAmount, 2, '.', '');
        }

        $args = array(
//            'AuthenticationToken' => $MerchantIdentity->getAuthenticationToken(),           // String 100 Authorization Valid value is a GUID. Value supplied by ProPay. Used to access the API
//            'BillerAccountId' => $MerchantIdentity->getBillerAccountId(),                   // String 16 Authorization Value supplied by ProPay. Used to identify the correct collection of tokens.

            'AccountName' => $OrderRow->getCardHolderFullName(),                            // String 50 Optional Cardholder name. Will be passed on to gateway if gateway accepts it.
            'Address1' => $OrderRow->getPayeeAddress(),                                     // String 50 Optional Cardholder address
            'Address2' => $OrderRow->getPayeeAddress2(),                                    // String 50 Optional Cardholder address
            'City' => $OrderRow->getPayeeCity(),                                            // String 25 Optional Cardholder address
            'Country' => $OrderRow->getPayeeCountry(),                                      // String 3 Optional Cardholder address. *Must be ISO 3166 standard 3 character country code.
            'Description' => $OrderRow->getCardHolderFullName(),                            // String 25 Required Description for the new stored payment method.
            'Email' => $OrderRow->getPayeeEmail(),                                          // String 100 Optional Email address for payment method
            'State' => $OrderRow->getPayeeState(),                                          // String 3 Optional Cardholder address
            'TelephoneNumber' => $OrderRow->getPayeePhone(),                                // String 20* Optional The phone number for the payment method. *10 digits for US numbers.
            'ZipCode' => $OrderRow->getPayeeZipCode(),                                      // String 10 Optional Cardholder address
            'ShouldCapture' => 'true',                                                      // Boolean Required Valid values are: ? true ? false // Set this value to false for Authorization Only
            'ShouldCreatePaymentMethod' => 'true',                                          // Boolean Required True or False; Determines if the data should be stored as a PaymentMethodId after processing it.
            'CreatePaymentMethodDuplicateAction' => 'SaveNew',                              // String - Determines action to take in the event that a new payment method duplicates an existing payment method. Valid values are: ? SaveNew -default if not specified ? Error -return error if duplicate found ? ReturnDup -causes payment method id to be returned when duplicate found
            'EncryptedTrackData' => array(                                                  //  Object - Required
                'DeviceType' => 'MagTekDynamag',                                            // String Required Valid Values are:  ? MagTekM20 ? MagTekFlash ? IdTechUniMag ? Manual ? MagTekADynamo ? MagTekDynamag ? RoamData
                'KeySerialNumber' => base64_encode($KeySerialNumber),                       // Base64 String Required This value will be obtained from the ProPay supported device.
                'EncryptedTrackData' => base64_encode($OrderRow->getCardTrack()),           // Base64 String ** Encrypted data as pulled from the ProPay approved encrypted swipe device.
                'EncryptedTrack2Data' => null,                                              // Base64 String ** Encrypted data as pulled from the ProPay approved encrypted swipe device.
            ),
            'Transaction' => array(                                                         // Object - Required Contains Transaction Information *REST passes the transaction values directly and not nested.
                'Amount' => floor(100*$TransactionAmount),                                  // Integer Required The value representing the number of pennies in USD, or the number of [currency] without decimals.
                'Comment1' => null,                                                         // String 128 Optional Transaction descriptor. Only passed if supported by the gateway.
                'Comment2' => null,                                                         // String 128 Optional Transaction descriptor. Only passed if supported by the gateway.
                'CurrencyCode' => null,                                                     // String 3 Required ISO 4217 standard 3 character currency code.
                'Invoice' => $OrderRow->getInvoiceNumber(),                                 // String 50 Optional Recommended. Transaction descriptor-only passed if supported by the gateway. *ProPay gateway rejects duplicates for same invoice #, card # and amount in 1 minute. Transaction .MerchantProfileId Integer Required The MerchantProfileId that was created using the supplied credentials for the supplied Gateway that is used to process against this particular gateway
                'PayerAccountId' => $PayerAccountId,                                        // String 16 Required This is the ProtectPay ID for the Payer Created and belongs to the BillerID that created it
                'IsDebtRepayment' => 'False',                                               // Boolean - Optional Valid Values are: ? True ? False Only applicable for LegacyProPay and LegacyProPayCan gateways Defaults to False if not submitted
            ),
//            'Transaction.Frauddetectors' => array(                                        // Object - Optional Please See ProtectPay Appendix C for details concerning the
            //         FrauddetectorsObject
//                'Frauddetectors.FrauddetectorProviderName' => '',                           // String Required* If using Frauddetectors Object this attribute is required.
        );

        $request = json_encode($args, JSON_PRETTY_PRINT);

        $Request->setRequest($request);
        return $Request;
    }


    public function prepareCreditCardReversalRequest(
        ProtectPayMerchantIdentity $MerchantIdentity,
        TransactionRow $TransactionRow,
        OrderRow $OrderRow,
        Array $post
    )
    {
        $Action = 'CreditCardReversal';
//        if(@$post['pin'])
//            $Action = 'DebitCardReversal';

        $TransactionAmount = number_format($OrderRow->getAmount(), 2, '.', '');
        $ConvenienceFeeAmount = $MerchantIdentity->calculateConvenienceFee($OrderRow);
        if($ConvenienceFeeAmount) {
            $TransactionAmount = number_format($OrderRow->getAmount() + $ConvenienceFeeAmount, 2, '.', '');
            $ConvenienceFeeAmount = number_format($ConvenienceFeeAmount, 2, '.', '');
        }

        $args = array(
            'credentials' => array(),
            'application' => array(),
            'terminal' => array(),
            'card' => array(
                'Track1Data' => '',
                'Track2Data' => '',
                'Track3Data' => '',
                'MagneprintData' => $OrderRow->getCardTrack(),
                'CardNumber' => $OrderRow->getCardNumber(),
                'TruncatedCardNumber' => substr($OrderRow->getCardNumber(), -4, 4),
                'ExpirationMonth' => $OrderRow->getCardExpMonth(),
                'ExpirationYear' => $OrderRow->getCardExpYear(),
                'CardholderName' => $OrderRow->getPayeeFullName(),
                'CVV' => @$post['card_cvv2'],
                'CAVV' => '',
                'XID' => '',
                'PINBLOCK' => @$post['pin'],
                'KeySerialNumber' => '',
                'EncryptedFormat' =>            'Default', // Default or Format1 or Format2 or Format3 or Format4 or Format5 or Format6 or Format7;
                'EncryptedTrack1Data' => '',
                'EncryptedTrack2Data' => '',
                'EncryptedCardData' => '',
                'CardDataKeySerialNumber' => '',
                'AVSResponseCode' => '',
                'CVVResponseCode' => '',
                'CAVVResponseCode' => '',
                'CardLogo' => '',
                'GiftCardSecurityCode' => '',
                'AlternateCardNumber1' => '',
                'AlternateCardNumber2' => '',
                'AlternateCardNumber3' => '',
                'SecondaryCardNumber' => '',
            ),
            'transaction' => array(
                'TransactionID' => '',                  // $TransactionRow->getTransactionID();
                'ClerkNumber' => '',
                'ShiftID' => '',
                'TransactionAmount' => $TransactionAmount,
                'OriginalAuthorizedAmount' => '',
                'TotalAuthorizedAmount' => '',
                'SalesTaxAmount' => '',
                'TipAmount' => '',
                'ReferenceNumber' => $OrderRow->getReferenceNumber(),
                'TicketNumber' => substr(strtoupper($TransactionRow->getReferenceNumber()), 0, 6),
                'ReversalType' => 'System', // System or Full or Partial;
                'MarketCode' => $MerchantIdentity->getMarketCode(), // Default or AutoRental or DirectMarketing or ECommerce or FoodRestaurant or HotelLodging or Petroleum or Retail or QSR;
                'BillPaymentFlag' => 'False', // False or True
                'DuplicateCheckDisableFlag' => 'False',
                'DuplicateOverrideFlag' => 'False',
                'RecurringFlag' => 'False',
                'TransactionStatus' => '',
                'TransactionStatusCode' => '',
                'HostTransactionID' => '',
                'PartialApprovedFlag' => 'False',
                'ApprovedAmount' => '',
                'ConvenienceFeeAmount' => $ConvenienceFeeAmount,
                'EMVEncryptionFormat' => 'Default',
                'ReversalReason' => 'Unknown', // Unknown or RejectedPartialApproval or Timeout or EditError or MACVerifyError or MACSyncError or EncryptionError or SystemError or PossibleFraud or CardRemoval or ChipDecline or TerminalError
            ),
            'address' => array(
                'BillingName' => $OrderRow->getPayeeFullName(),
                'BillingAddress1' => $OrderRow->getPayeeAddress(),
                'BillingAddress2' => $OrderRow->getPayeeAddress2(),
                'BillingCity' => $OrderRow->getPayeeCity(),
                'BillingState' => $OrderRow->getPayeeState(),
                'BillingZipcode' => $OrderRow->getPayeeZipCode(),
                'BillingEmail' => $OrderRow->getPayeeEmail(),
                'BillingPhone' => $OrderRow->getPayeePhone(),

                'ShippingName' => '', // $BillingName;
                'ShippingAddress1' => '', // $BillingAddress1;
                'ShippingAddress2' => '', // $BillingAddress2;
                'ShippingCity' => '', // $BillingCity;
                'ShippingState' => '', // $BillingState;
                'ShippingZipcode' => '', // $BillingZipcode;
                'ShippingEmail' => '', // $BillingEmail;
                'ShippingPhone' => '', // $BillingPhone;
            ),
        );

        $request = $this->prepareJSONRequest(
            $MerchantIdentity,
            $Action,
            $args,
            $post
        );
        return $request;
    }


    public function prepareCreditCardReturnRequest(
        ProtectPayMerchantIdentity $MerchantIdentity,
        OrderRow $OrderRow,
        TransactionRow $AuthorizedTransaction,
        TransactionRow $ReturnTransaction,
        Array $post)
    {
        $Action = 'CreditCardReturn';

        $TransactionAmount = number_format($OrderRow->getAmount(), 2, '.', '');
        $ConvenienceFeeAmount = $MerchantIdentity->calculateConvenienceFee($OrderRow);
        if($ConvenienceFeeAmount) {
            $TransactionAmount = number_format($OrderRow->getAmount() + $ConvenienceFeeAmount, 2, '.', '');
            $ConvenienceFeeAmount = number_format($ConvenienceFeeAmount, 2, '.', '');
        }

        $args = array(
            'credentials' => array(),
            'application' => array(),
            'terminal' => array(),
//            'card' => array(
//                'Track1Data' => '',
//                'Track2Data' => '',
//                'Track3Data' => '',
//                'MagneprintData' => $OrderRow->getCardTrack(),
//                'CardNumber' => $OrderRow->getCardNumber(),
//                'TruncatedCardNumber' => substr($OrderRow->getCardNumber(), -4, 4),
//                'ExpirationMonth' => $OrderRow->getCardExpMonth(),
//                'ExpirationYear' => $OrderRow->getCardExpYear(),
//                'CardholderName' => $OrderRow->getPayeeFullName(),
//                'CVV' => @$post['card_cvv2'],
//                'CAVV' => '',
//                'XID' => '',
//                'PINBLOCK' => @$post['pin'],
//                'KeySerialNumber' => '',
//                'EncryptedFormat' =>            'Default', // Default or Format1 or Format2 or Format3 or Format4 or Format5 or Format6 or Format7;
//                'EncryptedTrack1Data' => '',
//                'EncryptedTrack2Data' => '',
//                'EncryptedCardData' => '',
//                'CardDataKeySerialNumber' => '',
//                'AVSResponseCode' => '',
//                'CVVResponseCode' => '',
//                'CAVVResponseCode' => '',
//                'CardLogo' => '',
//                'GiftCardSecurityCode' => '',
//                'AlternateCardNumber1' => '',
//                'AlternateCardNumber2' => '',
//                'AlternateCardNumber3' => '',
//                'SecondaryCardNumber' => '',
//            ),
            'transaction' => array(
                'TransactionID' => $AuthorizedTransaction->getIntegrationRemoteID(),
                'ClerkNumber' => '',
                'ShiftID' => '',
                'TransactionAmount' => $TransactionAmount,
                'OriginalAuthorizedAmount' => $TransactionAmount,
                'TotalAuthorizedAmount' => '',
                'SalesTaxAmount' => '',
                'TipAmount' => '',
                'ReferenceNumber' => $OrderRow->getReferenceNumber(),
                'TicketNumber' => substr(strtoupper($AuthorizedTransaction->getReferenceNumber()), 0, 6),
                'ReversalType' => 'Full', // System or Full or Partial;
                'MarketCode' => $MerchantIdentity->getMarketCode(), // Default or AutoRental or DirectMarketing or ECommerce or FoodRestaurant or HotelLodging or Petroleum or Retail or QSR;
                'BillPaymentFlag' => 'False', // False or True
                'DuplicateCheckDisableFlag' => 'False',
                'DuplicateOverrideFlag' => 'False',
                'RecurringFlag' => 'False',
                'TransactionStatus' => '',
                'TransactionStatusCode' => '',
                'HostTransactionID' => '',
                'PartialApprovedFlag' => 'False',
                'ApprovedAmount' => '',
                'ConvenienceFeeAmount' => $ConvenienceFeeAmount,
                'EMVEncryptionFormat' => 'Default',
                'ReversalReason' => 'Unknown', // Unknown or RejectedPartialApproval or Timeout or EditError or MACVerifyError or MACSyncError or EncryptionError or SystemError or PossibleFraud or CardRemoval or ChipDecline or TerminalError
            ),
        );

        if(isset($post['partial_return_amount']) && ($post['partial_return_amount'] >= 0.01)) {
            $partial_return_amount = $post['partial_return_amount'];
            if($partial_return_amount > $OrderRow->getAmount())
                throw new \InvalidArgumentException("Invalid Partial Return Amount");
            $args['transaction']['ReversalType'] = 'Partial';
            $args['transaction']['TransactionAmount'] = number_format($partial_return_amount, 2, '.', '');
            $ReturnTransaction->setAmount($partial_return_amount);
            $OrderRow->setTotalReturnedAmount($partial_return_amount);
        } else {
            $OrderRow->setTotalReturnedAmount($OrderRow->getAmount());
        }

        $request = $this->prepareJSONRequest(
            $MerchantIdentity,
            $Action,
            $args,
            $post
        );

        return $request;
    }

    public function prepareCreditCardVoidRequest(
        ProtectPayMerchantIdentity $MerchantIdentity,
        OrderRow $OrderRow,
        TransactionRow $AuthorizedTransaction,
        Array $post)
    {
        $Action = 'CreditCardVoid';

        $TransactionAmount = number_format($OrderRow->getAmount(), 2, '.', '');
        $ConvenienceFeeAmount = $MerchantIdentity->calculateConvenienceFee($OrderRow);
        if($ConvenienceFeeAmount) {
            $TransactionAmount = number_format($OrderRow->getAmount() + $ConvenienceFeeAmount, 2, '.', '');
            $ConvenienceFeeAmount = number_format($ConvenienceFeeAmount, 2, '.', '');
        }

        $args = array(
            'credentials' => array(),
            'application' => array(),
            'terminal' => array(),
            'transaction' => array(
                'TransactionID' => $AuthorizedTransaction->getIntegrationRemoteID(),
                'ClerkNumber' => '',
                'ShiftID' => '',
                'TransactionAmount' => $TransactionAmount,
                'OriginalAuthorizedAmount' => '',
                'TotalAuthorizedAmount' => '',
                'SalesTaxAmount' => '',
                'TipAmount' => '',
                'ReferenceNumber' => $OrderRow->getReferenceNumber(),
                'TicketNumber' => substr(strtoupper($AuthorizedTransaction->getReferenceNumber()), 0, 6),
                'ReversalType' => 'Full', // System or Full or Partial;
                'MarketCode' => $MerchantIdentity->getMarketCode(), // Default or AutoRental or DirectMarketing or ECommerce or FoodRestaurant or HotelLodging or Petroleum or Retail or QSR;
                'BillPaymentFlag' => 'False', // False or True
                'DuplicateCheckDisableFlag' => 'False',
                'DuplicateOverrideFlag' => 'False',
                'RecurringFlag' => 'False',
                'TransactionStatus' => '',
                'TransactionStatusCode' => '',
                'HostTransactionID' => '',
                'PartialApprovedFlag' => 'False',
                'ApprovedAmount' => '',
                'ConvenienceFeeAmount' => $ConvenienceFeeAmount,
                'EMVEncryptionFormat' => 'Default',
                'ReversalReason' => 'Unknown', // Unknown or RejectedPartialApproval or Timeout or EditError or MACVerifyError or MACSyncError or EncryptionError or SystemError or PossibleFraud or CardRemoval or ChipDecline or TerminalError
            ),
        );

        $request = $this->prepareJSONRequest(
            $MerchantIdentity,
            $Action,
            $args,
            $post
        );
        return $request;
    }

    function prepareHealthCheckRequest(
        ProtectPayMerchantIdentity $MerchantIdentity,
        Array $post
    ) {

        $ApplicationID = $MerchantIdentity->getApplicationID();
        $ApplicationName = 'SimonPayments';
        $ApplicationVersion = '1';

        $AccountID = $MerchantIdentity->getAccountID();
        $AccountToken = $MerchantIdentity->getAccountToken();
        $AcceptorID = $MerchantIdentity->getAcceptorID();
        $NewAccountToken = $MerchantIdentity->getAccountToken(); // ?

        if(!$AccountID) throw new IntegrationException("Invalid AccountID");
        if(!$AccountToken) throw new IntegrationException("Invalid AccountToken");
        if(!$AcceptorID) throw new IntegrationException("Invalid AcceptorID");
        if(!$NewAccountToken) throw new IntegrationException("Invalid NewAccountToken");

        $Action = 'HealthCheck';

        $request = <<<SOAP
<?xml version="1.0" encoding="utf-8"?>
<soap12:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">
  <soap12:Body>
    <{$Action} xmlns="https://transaction.propayexpress.com">
      <credentials>
        <AccountID>{$AccountID}</AccountID>
        <AccountToken>{$AccountToken}</AccountToken>
        <AcceptorID>{$AcceptorID}</AcceptorID>
        <NewAccountToken>{$NewAccountToken}</NewAccountToken>
      </credentials>
      <application>
        <ApplicationID>{$ApplicationID}</ApplicationID>
        <ApplicationName>{$ApplicationName}</ApplicationName>
        <ApplicationVersion>{$ApplicationVersion}</ApplicationVersion>
      </application>
    </{$Action}>
  </soap12:Body>
</soap12:Envelope>
SOAP;

        return $request;

    }

    function prepareTransactionQueryRequest(
        ProtectPayMerchantIdentity $MerchantIdentity,
        Array $post
    ) {

        $ApplicationID = $MerchantIdentity->getApplicationID();
        $ApplicationName = 'SimonPayments';
        $ApplicationVersion = '1';

        $AccountID = $MerchantIdentity->getAccountID();
        $AccountToken = $MerchantIdentity->getAccountToken();
        $AcceptorID = $MerchantIdentity->getAcceptorID();
        $NewAccountToken = $MerchantIdentity->getAccountToken(); // ?

        $TerminalType = 'Unknown'; // Unknown or PointOfSale or ECommerce or MOTO or FuelPump or ATM or Voice
        $LogTraceLevel = 'All'; // None or Fatal or Error or Warning or Information or Trace or Debug or All
        $ReverseOrder = 'False';

        $TransactionID = @$post['transaction_id'];
        $TerminalID = $MerchantIdentity->getDefaultTerminalID();
        $ApprovedAmount = '';
        $ExpressTransactionDate = '';
        $ExpressTransactionTime = '';
        $HostBatchID = '';
        $HostItemID = '';
        $HostReversalQueueID = '';
        $OriginalAuthorizedAmount = '';
        $ReferenceNumber = '';
        $ShiftID = '';
        $TrackingID = '';
        $TransactionAmount = '';
        $TransactionStatus = @$post['status'];
        $TransactionStatusCode = '';
        $TransactionType = '';
        $XID = '';
        $SourceIPAddress = '';
        $ExternalInterface = '';
        $LogTraceLevelName = '';
        $MachineName = '';
        $SourceObject = '';
        $ProcessID = '';
        $ThreadID = '';
        $TransactionDateTimeBegin = @$post['date_start'];
        $TransactionDateTimeEnd = @$post['date_end'];;

        $request = <<<SOAP
<?xml version="1.0" encoding="utf-8"?>
<soap12:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">
  <soap12:Body>
    <TransactionQuery xmlns="https://reporting.propayexpress.com">
      <credentials>
        <AccountID>{$AccountID}</AccountID>
        <AccountToken>{$AccountToken}</AccountToken>
        <AcceptorID>{$AcceptorID}</AcceptorID>
        <NewAccountToken>{$NewAccountToken}</NewAccountToken>
      </credentials>
      <application>
        <ApplicationID>{$ApplicationID}</ApplicationID>
        <ApplicationName>{$ApplicationName}</ApplicationName>
        <ApplicationVersion>{$ApplicationVersion}</ApplicationVersion>
      </application>
      <parameters>
        <TransactionID>{$TransactionID}</TransactionID>
        <TerminalID>{$TerminalID}</TerminalID>
        <ApplicationID>{$ApplicationID}</ApplicationID>
        <ApprovedAmount>{$ApprovedAmount}</ApprovedAmount>
        <ExpressTransactionDate>{$ExpressTransactionDate}</ExpressTransactionDate>
        <ExpressTransactionTime>{$ExpressTransactionTime}</ExpressTransactionTime>
        <HostBatchID>{$HostBatchID}</HostBatchID>
        <HostItemID>{$HostItemID}</HostItemID>
        <HostReversalQueueID>{$HostReversalQueueID}</HostReversalQueueID>
        <OriginalAuthorizedAmount>{$OriginalAuthorizedAmount}</OriginalAuthorizedAmount>
        <ReferenceNumber>{$ReferenceNumber}</ReferenceNumber>
        <ShiftID>{$ShiftID}</ShiftID>
        <SourceTransactionID>{$TransactionID}</SourceTransactionID>
        <TerminalType>{$TerminalType}</TerminalType>
        <TrackingID>{$TrackingID}</TrackingID>
        <TransactionAmount>{$TransactionAmount}</TransactionAmount>
        <TransactionStatus>{$TransactionStatus}</TransactionStatus>
        <TransactionStatusCode>{$TransactionStatusCode}</TransactionStatusCode>
        <TransactionType>{$TransactionType}</TransactionType>
        <XID>{$XID}</XID>
        <SourceIPAddress>{$SourceIPAddress}</SourceIPAddress>
        <ExternalInterface>{$ExternalInterface}</ExternalInterface>
        <LogTraceLevel>{$LogTraceLevel}</LogTraceLevel>
        <LogTraceLevelName>{$LogTraceLevelName}</LogTraceLevelName>
        <MachineName>{$MachineName}</MachineName>
        <SourceObject>{$SourceObject}</SourceObject>
        <ProcessID>{$ProcessID}</ProcessID>
        <ThreadID>{$ThreadID}</ThreadID>
        <ReverseOrder>{$ReverseOrder}</ReverseOrder>
        <TransactionDateTimeBegin>{$TransactionDateTimeBegin}</TransactionDateTimeBegin>
        <TransactionDateTimeEnd>{$TransactionDateTimeEnd}</TransactionDateTimeEnd>
      </parameters>
      <extendedParameters>
        <ExtendedParameters>
          <Key>string</Key>
          <Value />
        </ExtendedParameters>
        <ExtendedParameters>
          <Key>string</Key>
          <Value />
        </ExtendedParameters>
      </extendedParameters>
    </TransactionQuery>
  </soap12:Body>
</soap12:Envelope>
SOAP;

        return $request;
    }

    public function decodeXMLResponse($response) {
        $response = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $response);
        $xml = new \SimpleXMLElement($response);
        $data = json_decode(json_encode((array)$xml), TRUE);
        return $data;
    }

}