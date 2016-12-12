<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/21/2016
 * Time: 7:29 PM
 */
namespace Integration\Element;

use Integration\Model\Ex\IntegrationException;
use Order\Model\OrderRow;
use Order\Model\TransactionRow;

class ElementAPIUtil {

    protected function prepareSOAPRequest(
        ElementMerchantIdentity $MerchantIdentity,
        $Action,
        Array $args = array(),
        Array $post = array()
    ) {
        $args['terminal'] += array(
            'TerminalID' => $MerchantIdentity->getDefaultTerminalID(),
            'TerminalType' => 'PointOfSale',                    // Unknown or PointOfSale or ECommerce or MOTO or FuelPump or ATM or Voice
            'CardPresentCode' => 'UseDefault',                  // UseDefault or Unknown or Present or NotPresent;
            'CardholderPresentCode' => 'UseDefault',            // UseDefault or Unknown or Present or NotPresent or MailOrder or PhoneOrder or StandingAuth or ECommerce;
            'CardInputCode' => 'MagstripeRead',                 // UseDefault or Unknown or MagstripeRead or ContactlessMagstripeRead or ManualKeyed or ManualKeyedMagstripeFailure or ChipRead or ContactlessChipRead or ManualKeyedChipReadFailure or MagstripeReadChipReadFailure;
            'CVVPresenceCode' => 'UseDefault',                  // UseDefault or NotProvided or Provided or Illegible or CustomerIllegible;
            'TerminalCapabilityCode' => 'UseDefault',           // UseDefault or Unknown or NoTerminal or MagstripeReader or ContactlessMagstripeReader or KeyEntered or ChipReader or ContactlessChipReader
            'TerminalEnvironmentCode' => 'UseDefault',          // UseDefault or NoTerminal or LocalAttended or LocalUnattended or RemoteAttended or RemoteUnattended or ECommerce
            'MotoECICode' => 'NotUsed',                         // UseDefault or NotUsed or Single or Recurring or Installment or SecureECommerce or NonAuthenticatedSecureTransaction or NonAuthenticatedSecureECommerceTransaction or NonSecureECommerceTransaction
            'CVVResponseType' => 'Regular',                     // Regular or Extended
            'ConsentCode' => 'NotUsed',                         // NotUsed or FaceToFace or Phone or Internet
            'TerminalSerialNumber' => '',
            'TerminalEncryptionFormat' => 'Default',            // Default or Format1 or Format2 or Format3 or Format4 or Format5 or Format6 or Format7
            'LaneNumber' => '',
            'Model' => '',
            'EMVKernelVersion' => '',
        );

        if(isset($args['card'])) {
            if (@$args['card']['MagneprintData']) { // Card Present
                $args['card']['CardholderPresentCode'] =    'Present'; // UseDefault or Unknown or Present or NotPresent or MailOrder or PhoneOrder or StandingAuth or ECommerce;
                $args['card']['CardInputCode'] =            'MagstripeRead'; // UseDefault or Unknown or MagstripeRead or ContactlessMagstripeRead or ManualKeyed or ManualKeyedMagstripeFailure or ChipRead or ContactlessChipRead or ManualKeyedChipReadFailure or MagstripeReadChipReadFailure;
                $args['card']['CardPresentCode'] =          'Present'; // UseDefault or Unknown or Present or NotPresent;
                $args['card']['TerminalCapabilityCode'] =   'MagstripeReader'; // UseDefault or Unknown or NoTerminal or MagstripeReader or ContactlessMagstripeReader or KeyEntered or ChipReader or ContactlessChipReader
                $args['card']['TerminalEnvironmentCode'] =  'LocalAttended'; // UseDefault or NoTerminal or LocalAttended or LocalUnattended or RemoteAttended or RemoteUnattended or ECommerce
                $args['card']['TerminalType'] =             'PointOfSale'; // Unknown or PointOfSale or ECommerce or MOTO or FuelPump or ATM or Voice
                $args['card']['MarketCode'] =               'Retail'; // Default or AutoRental or DirectMarketing or ECommerce or FoodRestaurant or HotelLodging or Petroleum or Retail or QSR;
                $args['card']['CardNumber'] =               '';
            } else {
                $args['card']['CardholderPresentCode'] =    'ECommerce'; // UseDefault or Unknown or Present or NotPresent or MailOrder or PhoneOrder or StandingAuth or ECommerce;
                $args['card']['CardInputCode'] =            'ManualKeyed'; // UseDefault or Unknown or MagstripeRead or ContactlessMagstripeRead or ManualKeyed or ManualKeyedMagstripeFailure or ChipRead or ContactlessChipRead or ManualKeyedChipReadFailure or MagstripeReadChipReadFailure;
                $args['card']['CardPresentCode'] =          'NotPresent'; // UseDefault or Unknown or Present or NotPresent;
                $args['card']['TerminalCapabilityCode'] =   'KeyEntered'; // UseDefault or Unknown or NoTerminal or MagstripeReader or ContactlessMagstripeReader or KeyEntered or ChipReader or ContactlessChipReader
                $args['card']['TerminalEnvironmentCode'] =  'ECommerce'; // UseDefault or NoTerminal or LocalAttended or LocalUnattended or RemoteAttended or RemoteUnattended or ECommerce
                $args['card']['TerminalType'] =             'ECommerce'; // Unknown or PointOfSale or ECommerce or MOTO or FuelPump or ATM or Voice
                $args['card']['MarketCode'] =               'ECommerce'; // Default or AutoRental or DirectMarketing or ECommerce or FoodRestaurant or HotelLodging or Petroleum or Retail or QSR;
                $args['card']['MagneprintData'] =           '';
                $args['card']['MotoECICode'] =              'NonAuthenticatedSecureECommerceTransaction'; // UseDefault or NotUsed or Single or Recurring or Installment or SecureECommerce or NonAuthenticatedSecureTransaction or NonAuthenticatedSecureECommerceTransaction or NonSecureECommerceTransaction
            }
        }

        $args['credentials'] += array(
            'AccountID' => $MerchantIdentity->getAccountID(),
            'AccountToken' => $MerchantIdentity->getAccountToken(),
            'AcceptorID' => $MerchantIdentity->getAcceptorID(),
            'NewAccountToken' => $MerchantIdentity->getAccountToken(), // ?
        );

        if(!$args['credentials']['AccountID'])          throw new IntegrationException("Invalid AccountID");
        if(!$args['credentials']['AccountToken'])       throw new IntegrationException("Invalid AccountToken");
        if(!$args['credentials']['AcceptorID'])         throw new IntegrationException("Invalid AcceptorID");
        if(!$args['credentials']['NewAccountToken'])    throw new IntegrationException("Invalid NewAccountToken");

        $args['application'] += array(
            'ApplicationID' => $MerchantIdentity->getApplicationID(),
            'ApplicationName' => 'SimonPaymentsGateway',
            'ApplicationVersion' => '1',
        );

        $contentXML = '';
        foreach($args as $section => $sectionArgs) {
            $contentXML .= "\r\n      <{$section}>";
            foreach($sectionArgs as $arg => $val)
                $contentXML .= "\r\n        <{$arg}>{$val}</{$arg}>";
            $contentXML .= "\r\n      </{$section}>";
        }

        $request = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<soap12:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">
  <soap12:Body>
    <{$Action} xmlns="https://transaction.elementexpress.com">{$contentXML}
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
    </{$Action}>
  </soap12:Body>
</soap12:Envelope>
XML;

        return $request;
    }

    public function prepareCreditCardSaleRequest(
        ElementMerchantIdentity $MerchantIdentity,
        TransactionRow $TransactionRow,
        OrderRow $OrderRow,
        Array $post
    ) {
        $Action = 'CreditCardSale';
        if(@$post['pin'])
            $Action = 'DebitCardSale';

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
                'CardholderName' => $OrderRow->getCardHolderFullName(),
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
                'PartialApprovedFlag' => 'True',
                'ApprovedAmount' => '',
                'ConvenienceFeeAmount' => $ConvenienceFeeAmount,
                'EMVEncryptionFormat' => 'Default',
                'ReversalReason' => 'Unknown', // Unknown or RejectedPartialApproval or Timeout or EditError or MACVerifyError or MACSyncError or EncryptionError or SystemError or PossibleFraud or CardRemoval or ChipDecline or TerminalError
            ),
            'address' => array(
                'BillingName' => $OrderRow->getCardHolderFullName(),
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

        $request = $this->prepareSOAPRequest(
            $MerchantIdentity,
            $Action,
            $args,
            $post
        );

        return $request;
    }
    public function prepareCreditCardReversalRequest(
        ElementMerchantIdentity $MerchantIdentity,
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
                'CardholderName' => $OrderRow->getCardHolderFullName(),
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
                'PartialApprovedFlag' => 'True',
                'ApprovedAmount' => '',
                'ConvenienceFeeAmount' => $ConvenienceFeeAmount,
                'EMVEncryptionFormat' => 'Default',
                'ReversalReason' => 'Unknown', // Unknown or RejectedPartialApproval or Timeout or EditError or MACVerifyError or MACSyncError or EncryptionError or SystemError or PossibleFraud or CardRemoval or ChipDecline or TerminalError
            ),
            'address' => array(
                'BillingName' => $OrderRow->getCardHolderFullName(),
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

        $request = $this->prepareSOAPRequest(
            $MerchantIdentity,
            $Action,
            $args,
            $post
        );
        return $request;
    }

    public function prepareCreditCardReturnRequest(
        ElementMerchantIdentity $MerchantIdentity,
        OrderRow $OrderRow,
        TransactionRow $AuthorizedTransaction,
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
            'card' => array(
                'Track1Data' => '',
                'Track2Data' => '',
                'Track3Data' => '',
                'MagneprintData' => $OrderRow->getCardTrack(),
                'CardNumber' => $OrderRow->getCardNumber(),
                'TruncatedCardNumber' => substr($OrderRow->getCardNumber(), -4, 4),
                'ExpirationMonth' => $OrderRow->getCardExpMonth(),
                'ExpirationYear' => $OrderRow->getCardExpYear(),
                'CardholderName' => $OrderRow->getCardHolderFullName(),
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
                'TransactionID' => $AuthorizedTransaction->getTransactionID(),
                'ClerkNumber' => '',
                'ShiftID' => '',
                'TransactionAmount' => $TransactionAmount,
                'OriginalAuthorizedAmount' => '',
                'TotalAuthorizedAmount' => '',
                'SalesTaxAmount' => '',
                'TipAmount' => '',
                'ReferenceNumber' => $OrderRow->getReferenceNumber(),
                'TicketNumber' => substr(strtoupper($AuthorizedTransaction->getReferenceNumber()), 0, 6),
                'ReversalType' => 'System', // System or Full or Partial;
                'MarketCode' => $MerchantIdentity->getMarketCode(), // Default or AutoRental or DirectMarketing or ECommerce or FoodRestaurant or HotelLodging or Petroleum or Retail or QSR;
                'BillPaymentFlag' => 'False', // False or True
                'DuplicateCheckDisableFlag' => 'False',
                'DuplicateOverrideFlag' => 'False',
                'RecurringFlag' => 'False',
                'TransactionStatus' => '',
                'TransactionStatusCode' => '',
                'HostTransactionID' => '',
                'PartialApprovedFlag' => 'True',
                'ApprovedAmount' => '',
                'ConvenienceFeeAmount' => $ConvenienceFeeAmount,
                'EMVEncryptionFormat' => 'Default',
                'ReversalReason' => 'Unknown', // Unknown or RejectedPartialApproval or Timeout or EditError or MACVerifyError or MACSyncError or EncryptionError or SystemError or PossibleFraud or CardRemoval or ChipDecline or TerminalError
            ),
            'address' => array(
                'BillingName' => $OrderRow->getCardHolderFullName(),
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

        $request = $this->prepareSOAPRequest(
            $MerchantIdentity,
            $Action,
            $args,
            $post
        );
        return $request;
    }


    public function prepareCreditCardVoidRequest(
        ElementMerchantIdentity $MerchantIdentity,
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
                'TransactionID' => $AuthorizedTransaction->getTransactionID(),
                'ClerkNumber' => '',
                'ShiftID' => '',
                'TransactionAmount' => $TransactionAmount,
                'OriginalAuthorizedAmount' => '',
                'TotalAuthorizedAmount' => '',
                'SalesTaxAmount' => '',
                'TipAmount' => '',
                'ReferenceNumber' => $OrderRow->getReferenceNumber(),
                'TicketNumber' => substr(strtoupper($AuthorizedTransaction->getReferenceNumber()), 0, 6),
                'ReversalType' => 'System', // System or Full or Partial;
                'MarketCode' => $MerchantIdentity->getMarketCode(), // Default or AutoRental or DirectMarketing or ECommerce or FoodRestaurant or HotelLodging or Petroleum or Retail or QSR;
                'BillPaymentFlag' => 'False', // False or True
                'DuplicateCheckDisableFlag' => 'False',
                'DuplicateOverrideFlag' => 'False',
                'RecurringFlag' => 'False',
                'TransactionStatus' => '',
                'TransactionStatusCode' => '',
                'HostTransactionID' => '',
                'PartialApprovedFlag' => 'True',
                'ApprovedAmount' => '',
                'ConvenienceFeeAmount' => $ConvenienceFeeAmount,
                'EMVEncryptionFormat' => 'Default',
                'ReversalReason' => 'Unknown', // Unknown or RejectedPartialApproval or Timeout or EditError or MACVerifyError or MACSyncError or EncryptionError or SystemError or PossibleFraud or CardRemoval or ChipDecline or TerminalError
            ),
        );

        $request = $this->prepareSOAPRequest(
            $MerchantIdentity,
            $Action,
            $args,
            $post
        );
        return $request;
    }

    public function prepareCheckSaleRequest(
        ElementMerchantIdentity $MerchantIdentity,
        TransactionRow $TransactionRow,
        OrderRow $OrderRow,
        Array $post
    ) {
        $Action = 'CheckSale';

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
            'demandDepositAccount' => array(
                'DDAAccountType' => $OrderRow->getCheckAccountType(),
                'AccountNumber' => $OrderRow->getCheckAccountNumber(),
                'RoutingNumber' => $OrderRow->getCheckRoutingNumber(),
                'CheckNumber' => $OrderRow->getCheckNumber(),
                'CheckType' => $OrderRow->getCheckType(),
                'TruncatedAccountNumber' => '',
                'TruncatedRoutingNumber' => '',
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
                'PartialApprovedFlag' => 'True',
                'ApprovedAmount' => '',
                'ConvenienceFeeAmount' => $ConvenienceFeeAmount,
                'EMVEncryptionFormat' => 'Default',
                'ReversalReason' => 'Unknown', // Unknown or RejectedPartialApproval or Timeout or EditError or MACVerifyError or MACSyncError or EncryptionError or SystemError or PossibleFraud or CardRemoval or ChipDecline or TerminalError
            ),
            'identification' => array(
                'TaxIDNumber' => '',
                'DriversLicenseNumber' => '',
                'DriversLicenseState' => '',
                'BirthDate' => '',
            ),
            'address' => array(
                'BillingName' => $OrderRow->getCardHolderFullName(),
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

        $request = $this->prepareSOAPRequest(
            $MerchantIdentity,
            $Action,
            $args,
            $post
        );

        return $request;
    }

    public function prepareCheckVoidRequest(
        ElementMerchantIdentity $MerchantIdentity,
        OrderRow $OrderRow,
        TransactionRow $AuthorizedTransaction,
        Array $post)
    {
        $Action = 'CheckVoid';

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
                'TransactionID' => $AuthorizedTransaction->getTransactionID(),
                'ClerkNumber' => '',
                'ShiftID' => '',
                'TransactionAmount' => $TransactionAmount,
                'OriginalAuthorizedAmount' => '',
                'TotalAuthorizedAmount' => '',
                'SalesTaxAmount' => '',
                'TipAmount' => '',
                'ReferenceNumber' => $OrderRow->getReferenceNumber(),
                'TicketNumber' => substr(strtoupper($AuthorizedTransaction->getReferenceNumber()), 0, 6),
                'ReversalType' => 'System', // System or Full or Partial;
                'MarketCode' => $MerchantIdentity->getMarketCode(), // Default or AutoRental or DirectMarketing or ECommerce or FoodRestaurant or HotelLodging or Petroleum or Retail or QSR;
                'BillPaymentFlag' => 'False', // False or True
                'DuplicateCheckDisableFlag' => 'False',
                'DuplicateOverrideFlag' => 'False',
                'RecurringFlag' => 'False',
                'TransactionStatus' => '',
                'TransactionStatusCode' => '',
                'HostTransactionID' => '',
                'PartialApprovedFlag' => 'True',
                'ApprovedAmount' => '',
                'ConvenienceFeeAmount' => $ConvenienceFeeAmount,
                'EMVEncryptionFormat' => 'Default',
                'ReversalReason' => 'Unknown', // Unknown or RejectedPartialApproval or Timeout or EditError or MACVerifyError or MACSyncError or EncryptionError or SystemError or PossibleFraud or CardRemoval or ChipDecline or TerminalError
            ),
        );

        $request = $this->prepareSOAPRequest(
            $MerchantIdentity,
            $Action,
            $args,
            $post
        );
        return $request;
    }


    public function prepareCheckReturnRequest(
        ElementMerchantIdentity $MerchantIdentity,
        OrderRow $OrderRow,
        TransactionRow $AuthorizedTransaction,
        Array $post)
    {
        $Action = 'CheckReturn';

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
                'TransactionID' => $AuthorizedTransaction->getTransactionID(),
                'ClerkNumber' => '',
                'ShiftID' => '',
                'TransactionAmount' => $TransactionAmount,
                'OriginalAuthorizedAmount' => '',
                'TotalAuthorizedAmount' => '',
                'SalesTaxAmount' => '',
                'TipAmount' => '',
                'ReferenceNumber' => $OrderRow->getReferenceNumber(),
                'TicketNumber' => substr(strtoupper($AuthorizedTransaction->getReferenceNumber()), 0, 6),
                'ReversalType' => 'System', // System or Full or Partial;
                'MarketCode' => $MerchantIdentity->getMarketCode(), // Default or AutoRental or DirectMarketing or ECommerce or FoodRestaurant or HotelLodging or Petroleum or Retail or QSR;
                'BillPaymentFlag' => 'False', // False or True
                'DuplicateCheckDisableFlag' => 'False',
                'DuplicateOverrideFlag' => 'False',
                'RecurringFlag' => 'False',
                'TransactionStatus' => '',
                'TransactionStatusCode' => '',
                'HostTransactionID' => '',
                'PartialApprovedFlag' => 'True',
                'ApprovedAmount' => '',
                'ConvenienceFeeAmount' => $ConvenienceFeeAmount,
                'EMVEncryptionFormat' => 'Default',
                'ReversalReason' => 'Unknown', // Unknown or RejectedPartialApproval or Timeout or EditError or MACVerifyError or MACSyncError or EncryptionError or SystemError or PossibleFraud or CardRemoval or ChipDecline or TerminalError
            ),
        );

        $request = $this->prepareSOAPRequest(
            $MerchantIdentity,
            $Action,
            $args,
            $post
        );
        return $request;
    }


    function prepareHealthCheckRequest(
        ElementMerchantIdentity $MerchantIdentity,
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
    <{$Action} xmlns="https://transaction.elementexpress.com">
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
        ElementMerchantIdentity $MerchantIdentity,
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
    <TransactionQuery xmlns="https://reporting.elementexpress.com">
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

}