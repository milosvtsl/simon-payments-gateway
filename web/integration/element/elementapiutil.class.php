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

    public function prepareCreditCardSaleRequest(
        ElementMerchantIdentity $MerchantIdentity,
        TransactionRow $TransactionRow,
        OrderRow $OrderRow,
        Array $post
    ) {
        $CVV = @$post['card_cvv2'];
        $PINBlock = @$post['pin'];

        $MagneprintData = $OrderRow->getCardTrack();
        $CardholderName = $OrderRow->getCardHolderFullName();
        $TransactionID = ''; // $TransactionRow->getTransactionID();

        $TicketNumber = substr(strtoupper($TransactionRow->getReferenceNumber()), 0, 6);
        $ReferenceNumber = $OrderRow->getReferenceNumber();

        $AccountID = $MerchantIdentity->getAccountID();
        $AccountToken = $MerchantIdentity->getAccountToken();
        $AcceptorID = $MerchantIdentity->getAcceptorID();
        $NewAccountToken = $MerchantIdentity->getAccountToken(); // ?

        $CardNumber = $OrderRow->getCardNumber();
        $TruncatedCardNumber = substr($CardNumber, -4, 4);
        $ExpirationMonth = $OrderRow->getCardExpMonth();
        $ExpirationYear = $OrderRow->getCardExpYear();

        $TransactionAmount = $OrderRow->getAmount();
        $ConvenienceFeeAmount = $MerchantIdentity->calculateConvenienceFee($OrderRow);
        if($ConvenienceFeeAmount) {
            $TransactionAmount = $TransactionAmount + $ConvenienceFeeAmount;
            $ConvenienceFeeAmount = number_format($ConvenienceFeeAmount, 2, '.', '');
        }
        $TransactionAmount = number_format($TransactionAmount, 2, '.', '');

        $BillingName = $OrderRow->getCardHolderFullName();
        $BillingAddress1 = $OrderRow->getPayeeAddress();
        $BillingAddress2 = $OrderRow->getPayeeAddress2();
        $BillingCity = $OrderRow->getPayeeCity();
        $BillingState = $OrderRow->getPayeeState();
        $BillingZipcode = $OrderRow->getPayeeZipCode();
        $BillingEmail = $OrderRow->getPayeeEmail();
        $BillingPhone = $OrderRow->getPayeePhone();

        $ShippingName = ''; // $BillingName;
        $ShippingAddress1 = ''; // $BillingAddress1;
        $ShippingAddress2 = ''; // $BillingAddress2;
        $ShippingCity = ''; // $BillingCity;
        $ShippingState = ''; // $BillingState;
        $ShippingZipcode = ''; // $BillingZipcode;
        $ShippingEmail = ''; // $BillingEmail;
        $ShippingPhone = ''; // $BillingPhone;


        $TerminalID = $MerchantIdentity->getDefaultTerminalID();
        $TerminalType = 'PointOfSale'; // Unknown or PointOfSale or ECommerce or MOTO or FuelPump or ATM or Voice
        $CardPresentCode = 'UseDefault'; // UseDefault or Unknown or Present or NotPresent;
        $CardholderPresentCode = 'UseDefault'; // UseDefault or Unknown or Present or NotPresent or MailOrder or PhoneOrder or StandingAuth or ECommerce;
        $CardInputCode = 'MagstripeRead'; // UseDefault or Unknown or MagstripeRead or ContactlessMagstripeRead or ManualKeyed or ManualKeyedMagstripeFailure or ChipRead or ContactlessChipRead or ManualKeyedChipReadFailure or MagstripeReadChipReadFailure;
        $CVVPresenceCode = 'UseDefault'; // UseDefault or NotProvided or Provided or Illegible or CustomerIllegible;
        $TerminalCapabilityCode = 'UseDefault'; // UseDefault or Unknown or NoTerminal or MagstripeReader or ContactlessMagstripeReader or KeyEntered or ChipReader or ContactlessChipReader
        $TerminalEnvironmentCode = 'UseDefault'; // UseDefault or NoTerminal or LocalAttended or LocalUnattended or RemoteAttended or RemoteUnattended or ECommerce
        $MotoECICode = 'NotUsed'; // UseDefault or NotUsed or Single or Recurring or Installment or SecureECommerce or NonAuthenticatedSecureTransaction or NonAuthenticatedSecureECommerceTransaction or NonSecureECommerceTransaction

        $CVVResponseType = 'Regular'; // Regular or Extended
        $ConsentCode = 'NotUsed'; // NotUsed or FaceToFace or Phone or Internet
        $TerminalSerialNumber = '';
        $TerminalEncryptionFormat = 'Default'; // Default or Format1 or Format2 or Format3 or Format4 or Format5 or Format6 or Format7
        $LaneNumber = '';
        $Model = '';
        $EMVKernelVersion = '';
        $Track1Data = '';
        $Track2Data = '';
        $Track3Data = '';
        $EncryptedFormat = 'Default'; // Default or Format1 or Format2 or Format3 or Format4 or Format5 or Format6 or Format7;
        $ReversalType = 'System'; // System or Full or Partial;
        $MarketCode = $MerchantIdentity->getMarketCode(); // Default or AutoRental or DirectMarketing or ECommerce or FoodRestaurant or HotelLodging or Petroleum or Retail or QSR;
        $BillPaymentFlag = 'False'; // False or True
        $ReversalReason = 'Unknown'; // Unknown or RejectedPartialApproval or Timeout or EditError or MACVerifyError or MACSyncError or EncryptionError or SystemError or PossibleFraud or CardRemoval or ChipDecline or TerminalError


        $ApplicationID = $MerchantIdentity->getApplicationID();
        $ApplicationName = 'Simon Payments Gateway';
        $ApplicationVersion = '1';

        $CAVV = '';
        $XID = '';
        $KeySerialNumber = '';
        $EncryptedTrack1Data = '';
        $EncryptedTrack2Data = '';
        $EncryptedCardData = '';
        $CardDataKeySerialNumber = '';
        $AVSResponseCode = '';
        $CVVResponseCode = '';
        $CAVVResponseCode = '';
        $CardLogo = '';
        $GiftCardSecurityCode = '';
        $AlternateCardNumber1 = '';
        $AlternateCardNumber2 = '';
        $AlternateCardNumber3 = '';
        $SecondaryCardNumber = '';
        $ClerkNumber = '';
        $ShiftID = '';
        $OriginalAuthorizedAmount = '';
        $TotalAuthorizedAmount = '';
        $SalesTaxAmount = '';
        $TipAmount = '';
        $DuplicateCheckDisableFlag = 'False';
        $DuplicateOverrideFlag = 'False';
        $TransactionStatus = '';
        $TransactionStatusCode = '';
        $HostTransactionID = '';
        $PartialApprovedFlag = 'True';
        $ApprovedAmount = '';
        $EMVEncryptionFormat = 'Default';

        $Action = 'CreditCardSale';
        if($PINBlock)
            $Action = 'DebitCardSale';


        if(strtolower($OrderRow->getEntryMode()) === 'swipe' && $MagneprintData) { // Card Present
            $CardholderPresentCode = 'Present'; // UseDefault or Unknown or Present or NotPresent or MailOrder or PhoneOrder or StandingAuth or ECommerce;
            $CardInputCode = 'MagstripeRead'; // UseDefault or Unknown or MagstripeRead or ContactlessMagstripeRead or ManualKeyed or ManualKeyedMagstripeFailure or ChipRead or ContactlessChipRead or ManualKeyedChipReadFailure or MagstripeReadChipReadFailure;
            $CardPresentCode = 'Present'; // UseDefault or Unknown or Present or NotPresent;
            $TerminalCapabilityCode = 'MagstripeReader'; // UseDefault or Unknown or NoTerminal or MagstripeReader or ContactlessMagstripeReader or KeyEntered or ChipReader or ContactlessChipReader
            $TerminalEnvironmentCode = 'LocalAttended'; // UseDefault or NoTerminal or LocalAttended or LocalUnattended or RemoteAttended or RemoteUnattended or ECommerce
            $TerminalType = 'PointOfSale'; // Unknown or PointOfSale or ECommerce or MOTO or FuelPump or ATM or Voice
            $MarketCode = 'Retail'; // Default or AutoRental or DirectMarketing or ECommerce or FoodRestaurant or HotelLodging or Petroleum or Retail or QSR;
            $CardNumber = '';
        } else {
            $CardholderPresentCode = 'ECommerce'; // UseDefault or Unknown or Present or NotPresent or MailOrder or PhoneOrder or StandingAuth or ECommerce;
            $CardInputCode = 'ManualKeyed'; // UseDefault or Unknown or MagstripeRead or ContactlessMagstripeRead or ManualKeyed or ManualKeyedMagstripeFailure or ChipRead or ContactlessChipRead or ManualKeyedChipReadFailure or MagstripeReadChipReadFailure;
            $CardPresentCode = 'NotPresent'; // UseDefault or Unknown or Present or NotPresent;
            $TerminalCapabilityCode = 'KeyEntered'; // UseDefault or Unknown or NoTerminal or MagstripeReader or ContactlessMagstripeReader or KeyEntered or ChipReader or ContactlessChipReader
            $TerminalEnvironmentCode = 'ECommerce'; // UseDefault or NoTerminal or LocalAttended or LocalUnattended or RemoteAttended or RemoteUnattended or ECommerce
            $TerminalType = 'ECommerce'; // Unknown or PointOfSale or ECommerce or MOTO or FuelPump or ATM or Voice
            $MarketCode = 'ECommerce'; // Default or AutoRental or DirectMarketing or ECommerce or FoodRestaurant or HotelLodging or Petroleum or Retail or QSR;
            $MagneprintData = '';
            $MotoECICode = 'NonAuthenticatedSecureECommerceTransaction'; // UseDefault or NotUsed or Single or Recurring or Installment or SecureECommerce or NonAuthenticatedSecureTransaction or NonAuthenticatedSecureECommerceTransaction or NonSecureECommerceTransaction
        }


        $request = <<<PHP
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
      <terminal>
        <TerminalID>{$TerminalID}</TerminalID>
        <TerminalType>{$TerminalType}</TerminalType>
        <CardPresentCode>{$CardPresentCode}</CardPresentCode>
        <CardholderPresentCode>{$CardholderPresentCode}</CardholderPresentCode>
        <CardInputCode>{$CardInputCode}</CardInputCode>
        <CVVPresenceCode>{$CVVPresenceCode}</CVVPresenceCode>
        <TerminalCapabilityCode>{$TerminalCapabilityCode}</TerminalCapabilityCode>
        <TerminalEnvironmentCode>{$TerminalEnvironmentCode}</TerminalEnvironmentCode>
        <MotoECICode>{$MotoECICode}</MotoECICode>
        <CVVResponseType>{$CVVResponseType}</CVVResponseType>
        <ConsentCode>{$ConsentCode}</ConsentCode>
        <TerminalSerialNumber>{$TerminalSerialNumber}</TerminalSerialNumber>
        <TerminalEncryptionFormat>{$TerminalEncryptionFormat}</TerminalEncryptionFormat>
        <LaneNumber>{$LaneNumber}</LaneNumber>
        <Model>{$Model}</Model>
        <EMVKernelVersion>{$EMVKernelVersion}</EMVKernelVersion>
      </terminal>
      <card>
        <Track1Data>{$Track1Data}</Track1Data>
        <Track2Data>{$Track2Data}</Track2Data>
        <Track3Data>{$Track3Data}</Track3Data>
        <MagneprintData>{$MagneprintData}</MagneprintData>
        <CardNumber>{$CardNumber}</CardNumber>
        <TruncatedCardNumber>{$TruncatedCardNumber}</TruncatedCardNumber>
        <ExpirationMonth>{$ExpirationMonth}</ExpirationMonth>
        <ExpirationYear>{$ExpirationYear}</ExpirationYear>
        <CardholderName>{$CardholderName}</CardholderName>
        <CVV>{$CVV}</CVV>
        <CAVV>{$CAVV}</CAVV>
        <XID>{$XID}</XID>
        <PINBlock>{$PINBlock}</PINBlock>
        <KeySerialNumber>{$KeySerialNumber}</KeySerialNumber>
        <EncryptedFormat>{$EncryptedFormat}</EncryptedFormat>
        <EncryptedTrack1Data>{$EncryptedTrack1Data}</EncryptedTrack1Data>
        <EncryptedTrack2Data>{$EncryptedTrack2Data}</EncryptedTrack2Data>
        <EncryptedCardData>{$EncryptedCardData}</EncryptedCardData>
        <CardDataKeySerialNumber>{$CardDataKeySerialNumber}</CardDataKeySerialNumber>
        <AVSResponseCode>{$AVSResponseCode}</AVSResponseCode>
        <CVVResponseCode>{$CVVResponseCode}</CVVResponseCode>
        <CAVVResponseCode>{$CAVVResponseCode}</CAVVResponseCode>
        <CardLogo>{$CardLogo}</CardLogo>
        <GiftCardSecurityCode>{$GiftCardSecurityCode}</GiftCardSecurityCode>
        <AlternateCardNumber1>{$AlternateCardNumber1}</AlternateCardNumber1>
        <AlternateCardNumber2>{$AlternateCardNumber2}</AlternateCardNumber2>
        <AlternateCardNumber3>{$AlternateCardNumber3}</AlternateCardNumber3>
        <SecondaryCardNumber>{$SecondaryCardNumber}</SecondaryCardNumber>
      </card>
      <transaction>
        <TransactionID>{$TransactionID}</TransactionID>
        <ClerkNumber>{$ClerkNumber}</ClerkNumber>
        <ShiftID>{$ShiftID}</ShiftID>
        <TransactionAmount>{$TransactionAmount}</TransactionAmount>
        <OriginalAuthorizedAmount>{$OriginalAuthorizedAmount}</OriginalAuthorizedAmount>
        <TotalAuthorizedAmount>{$TotalAuthorizedAmount}</TotalAuthorizedAmount>
        <SalesTaxAmount>{$SalesTaxAmount}</SalesTaxAmount>
        <TipAmount>{$TipAmount}</TipAmount>
        <ReferenceNumber>{$ReferenceNumber}</ReferenceNumber>
        <TicketNumber>{$TicketNumber}</TicketNumber>
        <ReversalType>{$ReversalType}</ReversalType>
        <MarketCode>{$MarketCode}</MarketCode>
        <BillPaymentFlag>{$BillPaymentFlag}</BillPaymentFlag>
        <DuplicateCheckDisableFlag>{$DuplicateCheckDisableFlag}</DuplicateCheckDisableFlag>
        <DuplicateOverrideFlag>{$DuplicateOverrideFlag}</DuplicateOverrideFlag>
        <RecurringFlag>{$DuplicateOverrideFlag}</RecurringFlag>
        <TransactionStatus>{$TransactionStatus}</TransactionStatus>
        <TransactionStatusCode>{$TransactionStatusCode}</TransactionStatusCode>
        <HostTransactionID>{$HostTransactionID}</HostTransactionID>
        <PartialApprovedFlag>{$PartialApprovedFlag}</PartialApprovedFlag>
        <ApprovedAmount>{$ApprovedAmount}</ApprovedAmount>
        <ConvenienceFeeAmount>{$ConvenienceFeeAmount}</ConvenienceFeeAmount>
        <EMVEncryptionFormat>{$EMVEncryptionFormat}</EMVEncryptionFormat>
        <ReversalReason>{$ReversalReason}</ReversalReason>
      </transaction>
      <address>
        <BillingName>{$BillingName}</BillingName>
        <BillingAddress1>{$BillingAddress1}</BillingAddress1>
        <BillingAddress2>{$BillingAddress2}</BillingAddress2>
        <BillingCity>{$BillingCity}</BillingCity>
        <BillingState>{$BillingState}</BillingState>
        <BillingZipcode>{$BillingZipcode}</BillingZipcode>
        <BillingEmail>{$BillingEmail}</BillingEmail>
        <BillingPhone>{$BillingPhone}</BillingPhone>
        <ShippingName>{$ShippingName}</ShippingName>
        <ShippingAddress1>{$ShippingAddress1}</ShippingAddress1>
        <ShippingAddress2>{$ShippingAddress2}</ShippingAddress2>
        <ShippingCity>{$ShippingCity}</ShippingCity>
        <ShippingState>{$ShippingState}</ShippingState>
        <ShippingZipcode>{$ShippingZipcode}</ShippingZipcode>
        <ShippingEmail>{$ShippingEmail}</ShippingEmail>
        <ShippingPhone>{$ShippingPhone}</ShippingPhone>
      </address>
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
PHP;

        return $request;
    }


    public function prepareCreditCardReversalRequest(
        ElementMerchantIdentity $MerchantIdentity,
        TransactionRow $TransactionRow,
        OrderRow $OrderRow,
        Array $post
    ) {
        $CVV = @$post['card_cvv2'];
        $PINBlock = @$post['pin'];

        $MagneprintData = $OrderRow->getCardTrack();
        $CardholderName = $OrderRow->getCardHolderFullName();
        $TransactionID = ''; // $TransactionRow->getTransactionID();

        $TicketNumber = substr(strtoupper($TransactionRow->getReferenceNumber()), 0, 6);
        $ReferenceNumber = $OrderRow->getReferenceNumber();

        $AccountID = $MerchantIdentity->getAccountID();
        $AccountToken = $MerchantIdentity->getAccountToken();
        $AcceptorID = $MerchantIdentity->getAcceptorID();
        $NewAccountToken = $MerchantIdentity->getAccountToken(); // ?

        if(!$AccountID) throw new IntegrationException("Invalid AccountID");
        if(!$AccountToken) throw new IntegrationException("Invalid AccountToken");
        if(!$AcceptorID) throw new IntegrationException("Invalid AcceptorID");
        if(!$NewAccountToken) throw new IntegrationException("Invalid NewAccountToken");

        $CardNumber = $OrderRow->getCardNumber();
        $TruncatedCardNumber = substr($CardNumber, -4, 4);
        $ExpirationMonth = $OrderRow->getCardExpMonth();
        $ExpirationYear = $OrderRow->getCardExpYear();

        $TransactionAmount = $OrderRow->getAmount();
        $ConvenienceFeeAmount = $MerchantIdentity->calculateConvenienceFee($OrderRow);
        $ConvenienceFeeAmount = number_format($ConvenienceFeeAmount, 2, '.', '');
        $TransactionAmount = number_format($TransactionAmount, 2, '.', '');

        $BillingName = $OrderRow->getCardHolderFullName();
        $BillingAddress1 = $OrderRow->getPayeeAddress();
        $BillingAddress2 = $OrderRow->getPayeeAddress2();
        $BillingZipcode = $OrderRow->getPayeeZipCode();
        $BillingCity = $OrderRow->getPayeeCity();
        $BillingState = $OrderRow->getPayeeState();
        $BillingEmail = $OrderRow->getPayeeEmail();
        $BillingPhone = $OrderRow->getPayeePhone();

        $ShippingName = ''; // $BillingName;
        $ShippingAddress1 = ''; // $BillingAddress1;
        $ShippingAddress2 = ''; // $BillingAddress2;
        $ShippingCity = ''; // $BillingCity;
        $ShippingState = ''; // $BillingState;
        $ShippingZipcode = ''; // $BillingZipcode;
        $ShippingEmail = ''; // $BillingEmail;
        $ShippingPhone = ''; // $BillingPhone;


        $TerminalID = $MerchantIdentity->getDefaultTerminalID();
        $TerminalType = 'PointOfSale'; // Unknown or PointOfSale or ECommerce or MOTO or FuelPump or ATM or Voice
        $CardPresentCode = 'UseDefault'; // UseDefault or Unknown or Present or NotPresent;
        $CardholderPresentCode = 'UseDefault'; // UseDefault or Unknown or Present or NotPresent or MailOrder or PhoneOrder or StandingAuth or ECommerce;
        $CardInputCode = 'MagstripeRead'; // UseDefault or Unknown or MagstripeRead or ContactlessMagstripeRead or ManualKeyed or ManualKeyedMagstripeFailure or ChipRead or ContactlessChipRead or ManualKeyedChipReadFailure or MagstripeReadChipReadFailure;
        $CVVPresenceCode = 'UseDefault'; // UseDefault or NotProvided or Provided or Illegible or CustomerIllegible;
        $TerminalCapabilityCode = 'UseDefault'; // UseDefault or Unknown or NoTerminal or MagstripeReader or ContactlessMagstripeReader or KeyEntered or ChipReader or ContactlessChipReader
        $TerminalEnvironmentCode = 'UseDefault'; // UseDefault or NoTerminal or LocalAttended or LocalUnattended or RemoteAttended or RemoteUnattended or ECommerce
        $MotoECICode = 'NotUsed'; // UseDefault or NotUsed or Single or Recurring or Installment or SecureECommerce or NonAuthenticatedSecureTransaction or NonAuthenticatedSecureECommerceTransaction or NonSecureECommerceTransaction

        $CVVResponseType = 'Regular'; // Regular or Extended
        $ConsentCode = 'NotUsed'; // NotUsed or FaceToFace or Phone or Internet
        $TerminalSerialNumber = '';
        $TerminalEncryptionFormat = 'Default'; // Default or Format1 or Format2 or Format3 or Format4 or Format5 or Format6 or Format7
        $LaneNumber = '';
        $Model = '';
        $EMVKernelVersion = '';
        $Track1Data = '';
        $Track2Data = '';
        $Track3Data = '';
        $EncryptedFormat = 'Default'; // Default or Format1 or Format2 or Format3 or Format4 or Format5 or Format6 or Format7;
        $ReversalType = 'System'; // System or Full or Partial;
        $MarketCode = $MerchantIdentity->getMarketCode(); // Default or AutoRental or DirectMarketing or ECommerce or FoodRestaurant or HotelLodging or Petroleum or Retail or QSR;
        $BillPaymentFlag = 'False'; // False or True
        $ReversalReason = 'Unknown'; // Unknown or RejectedPartialApproval or Timeout or EditError or MACVerifyError or MACSyncError or EncryptionError or SystemError or PossibleFraud or CardRemoval or ChipDecline or TerminalError


        $ApplicationID = $MerchantIdentity->getApplicationID();
        $ApplicationName = 'Simon Payments Gateway';
        $ApplicationVersion = '1';

        $CAVV = '';
        $XID = '';
        $KeySerialNumber = '';
        $EncryptedTrack1Data = '';
        $EncryptedTrack2Data = '';
        $EncryptedCardData = '';
        $CardDataKeySerialNumber = '';
        $AVSResponseCode = '';
        $CVVResponseCode = '';
        $CAVVResponseCode = '';
        $CardLogo = '';
        $GiftCardSecurityCode = '';
        $AlternateCardNumber1 = '';
        $AlternateCardNumber2 = '';
        $AlternateCardNumber3 = '';
        $SecondaryCardNumber = '';
        $ClerkNumber = '';
        $ShiftID = '';
        $OriginalAuthorizedAmount = '';
        $TotalAuthorizedAmount = '';
        $SalesTaxAmount = '';
        $TipAmount = '';
        $DuplicateCheckDisableFlag = 'False';
        $DuplicateOverrideFlag = 'False';
        $TransactionStatus = '';
        $TransactionStatusCode = '';
        $HostTransactionID = '';
        $PartialApprovedFlag = 'True';
        $ApprovedAmount = '';
        $EMVEncryptionFormat = 'Default';


        if(strtolower($OrderRow->getEntryMode()) === 'swipe' && $MagneprintData) { // Card Present
            $CardholderPresentCode = 'Present'; // UseDefault or Unknown or Present or NotPresent or MailOrder or PhoneOrder or StandingAuth or ECommerce;
            $CardInputCode = 'MagstripeRead'; // UseDefault or Unknown or MagstripeRead or ContactlessMagstripeRead or ManualKeyed or ManualKeyedMagstripeFailure or ChipRead or ContactlessChipRead or ManualKeyedChipReadFailure or MagstripeReadChipReadFailure;
            $CardPresentCode = 'Present'; // UseDefault or Unknown or Present or NotPresent;
            $TerminalCapabilityCode = 'MagstripeReader'; // UseDefault or Unknown or NoTerminal or MagstripeReader or ContactlessMagstripeReader or KeyEntered or ChipReader or ContactlessChipReader
            $TerminalEnvironmentCode = 'LocalAttended'; // UseDefault or NoTerminal or LocalAttended or LocalUnattended or RemoteAttended or RemoteUnattended or ECommerce
            $TerminalType = 'PointOfSale'; // Unknown or PointOfSale or ECommerce or MOTO or FuelPump or ATM or Voice
            $MarketCode = 'Retail'; // Default or AutoRental or DirectMarketing or ECommerce or FoodRestaurant or HotelLodging or Petroleum or Retail or QSR;
            $CardNumber = '';
        } else {
            $CardholderPresentCode = 'ECommerce'; // UseDefault or Unknown or Present or NotPresent or MailOrder or PhoneOrder or StandingAuth or ECommerce;
            $CardInputCode = 'ManualKeyed'; // UseDefault or Unknown or MagstripeRead or ContactlessMagstripeRead or ManualKeyed or ManualKeyedMagstripeFailure or ChipRead or ContactlessChipRead or ManualKeyedChipReadFailure or MagstripeReadChipReadFailure;
            $CardPresentCode = 'NotPresent'; // UseDefault or Unknown or Present or NotPresent;
            $TerminalCapabilityCode = 'KeyEntered'; // UseDefault or Unknown or NoTerminal or MagstripeReader or ContactlessMagstripeReader or KeyEntered or ChipReader or ContactlessChipReader
            $TerminalEnvironmentCode = 'ECommerce'; // UseDefault or NoTerminal or LocalAttended or LocalUnattended or RemoteAttended or RemoteUnattended or ECommerce
            $TerminalType = 'ECommerce'; // Unknown or PointOfSale or ECommerce or MOTO or FuelPump or ATM or Voice
            $MarketCode = 'ECommerce'; // Default or AutoRental or DirectMarketing or ECommerce or FoodRestaurant or HotelLodging or Petroleum or Retail or QSR;
            $MagneprintData = '';
            $MotoECICode = 'NonAuthenticatedSecureECommerceTransaction'; // UseDefault or NotUsed or Single or Recurring or Installment or SecureECommerce or NonAuthenticatedSecureTransaction or NonAuthenticatedSecureECommerceTransaction or NonSecureECommerceTransaction
        }

//        if(keyed) {}

        $Action = 'CreditCardReversal';

        $request = <<<PHP
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
      <terminal>
        <TerminalID>{$TerminalID}</TerminalID>
        <TerminalType>{$TerminalType}</TerminalType>
        <CardPresentCode>{$CardPresentCode}</CardPresentCode>
        <CardholderPresentCode>{$CardholderPresentCode}</CardholderPresentCode>
        <CardInputCode>{$CardInputCode}</CardInputCode>
        <CVVPresenceCode>{$CVVPresenceCode}</CVVPresenceCode>
        <TerminalCapabilityCode>{$TerminalCapabilityCode}</TerminalCapabilityCode>
        <TerminalEnvironmentCode>{$TerminalEnvironmentCode}</TerminalEnvironmentCode>
        <MotoECICode>{$MotoECICode}</MotoECICode>
        <CVVResponseType>{$CVVResponseType}</CVVResponseType>
        <ConsentCode>{$ConsentCode}</ConsentCode>
        <TerminalSerialNumber>{$TerminalSerialNumber}</TerminalSerialNumber>
        <TerminalEncryptionFormat>{$TerminalEncryptionFormat}</TerminalEncryptionFormat>
        <LaneNumber>{$LaneNumber}</LaneNumber>
        <Model>{$Model}</Model>
        <EMVKernelVersion>{$EMVKernelVersion}</EMVKernelVersion>
      </terminal>
      <card>
        <Track1Data>{$Track1Data}</Track1Data>
        <Track2Data>{$Track2Data}</Track2Data>
        <Track3Data>{$Track3Data}</Track3Data>
        <MagneprintData>{$MagneprintData}</MagneprintData>
        <CardNumber>{$CardNumber}</CardNumber>
        <TruncatedCardNumber>{$TruncatedCardNumber}</TruncatedCardNumber>
        <ExpirationMonth>{$ExpirationMonth}</ExpirationMonth>
        <ExpirationYear>{$ExpirationYear}</ExpirationYear>
        <CardholderName>{$CardholderName}</CardholderName>
        <CVV>{$CVV}</CVV>
        <CAVV>{$CAVV}</CAVV>
        <XID>{$XID}</XID>
        <PINBlock>{$PINBlock}</PINBlock>
        <KeySerialNumber>{$KeySerialNumber}</KeySerialNumber>
        <EncryptedFormat>{$EncryptedFormat}</EncryptedFormat>
        <EncryptedTrack1Data>{$EncryptedTrack1Data}</EncryptedTrack1Data>
        <EncryptedTrack2Data>{$EncryptedTrack2Data}</EncryptedTrack2Data>
        <EncryptedCardData>{$EncryptedCardData}</EncryptedCardData>
        <CardDataKeySerialNumber>{$CardDataKeySerialNumber}</CardDataKeySerialNumber>
        <AVSResponseCode>{$AVSResponseCode}</AVSResponseCode>
        <CVVResponseCode>{$CVVResponseCode}</CVVResponseCode>
        <CAVVResponseCode>{$CAVVResponseCode}</CAVVResponseCode>
        <CardLogo>{$CardLogo}</CardLogo>
        <GiftCardSecurityCode>{$GiftCardSecurityCode}</GiftCardSecurityCode>
        <AlternateCardNumber1>{$AlternateCardNumber1}</AlternateCardNumber1>
        <AlternateCardNumber2>{$AlternateCardNumber2}</AlternateCardNumber2>
        <AlternateCardNumber3>{$AlternateCardNumber3}</AlternateCardNumber3>
        <SecondaryCardNumber>{$SecondaryCardNumber}</SecondaryCardNumber>
      </card>
      <transaction>
        <TransactionID>{$TransactionID}</TransactionID>
        <ClerkNumber>{$ClerkNumber}</ClerkNumber>
        <ShiftID>{$ShiftID}</ShiftID>
        <TransactionAmount>{$TransactionAmount}</TransactionAmount>
        <OriginalAuthorizedAmount>{$OriginalAuthorizedAmount}</OriginalAuthorizedAmount>
        <TotalAuthorizedAmount>{$TotalAuthorizedAmount}</TotalAuthorizedAmount>
        <SalesTaxAmount>{$SalesTaxAmount}</SalesTaxAmount>
        <TipAmount>{$TipAmount}</TipAmount>
        <ReferenceNumber>{$ReferenceNumber}</ReferenceNumber>
        <TicketNumber>{$TicketNumber}</TicketNumber>
        <ReversalType>{$ReversalType}</ReversalType>
        <MarketCode>{$MarketCode}</MarketCode>
        <BillPaymentFlag>{$BillPaymentFlag}</BillPaymentFlag>
        <DuplicateCheckDisableFlag>{$DuplicateCheckDisableFlag}</DuplicateCheckDisableFlag>
        <DuplicateOverrideFlag>{$DuplicateOverrideFlag}</DuplicateOverrideFlag>
        <RecurringFlag>{$DuplicateOverrideFlag}</RecurringFlag>
        <TransactionStatus>{$TransactionStatus}</TransactionStatus>
        <TransactionStatusCode>{$TransactionStatusCode}</TransactionStatusCode>
        <HostTransactionID>{$HostTransactionID}</HostTransactionID>
        <PartialApprovedFlag>{$PartialApprovedFlag}</PartialApprovedFlag>
        <ApprovedAmount>{$ApprovedAmount}</ApprovedAmount>
        <ConvenienceFeeAmount>{$ConvenienceFeeAmount}</ConvenienceFeeAmount>
        <EMVEncryptionFormat>{$EMVEncryptionFormat}</EMVEncryptionFormat>
        <ReversalReason>{$ReversalReason}</ReversalReason>
      </transaction>
      <address>
        <BillingName>{$BillingName}</BillingName>
        <BillingAddress1>{$BillingAddress1}</BillingAddress1>
        <BillingAddress2>{$BillingAddress2}</BillingAddress2>
        <BillingCity>{$BillingCity}</BillingCity>
        <BillingState>{$BillingState}</BillingState>
        <BillingZipcode>{$BillingZipcode}</BillingZipcode>
        <BillingEmail>{$BillingEmail}</BillingEmail>
        <BillingPhone>{$BillingPhone}</BillingPhone>
        <ShippingName>{$ShippingName}</ShippingName>
        <ShippingAddress1>{$ShippingAddress1}</ShippingAddress1>
        <ShippingAddress2>{$ShippingAddress2}</ShippingAddress2>
        <ShippingCity>{$ShippingCity}</ShippingCity>
        <ShippingState>{$ShippingState}</ShippingState>
        <ShippingZipcode>{$ShippingZipcode}</ShippingZipcode>
        <ShippingEmail>{$ShippingEmail}</ShippingEmail>
        <ShippingPhone>{$ShippingPhone}</ShippingPhone>
      </address>
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
PHP;

        return $request;
    }


    public function prepareCreditCardReturnRequest(
        ElementMerchantIdentity $MerchantIdentity,
        OrderRow $OrderRow,
        TransactionRow $AuthorizedTransaction,
        Array $post) {

        $Action = 'CreditCardReturn';
        $TransactionID = $AuthorizedTransaction->getTransactionID();

        $TicketNumber = substr(strtoupper($AuthorizedTransaction->getReferenceNumber()), 0, 6);
        $ReferenceNumber = $OrderRow->getReferenceNumber();

        $TransactionAmount = $OrderRow->getAmount();
        $ConvenienceFeeAmount = $MerchantIdentity->calculateConvenienceFee($OrderRow);
        $ConvenienceFeeAmount = number_format($ConvenienceFeeAmount, 2, '.', '');
        $TransactionAmount = number_format($TransactionAmount, 2, '.', '');


        $AccountID = $MerchantIdentity->getAccountID();
        $AccountToken = $MerchantIdentity->getAccountToken();
        $AcceptorID = $MerchantIdentity->getAcceptorID();
        $NewAccountToken = $MerchantIdentity->getAccountToken();

        if(!$AccountID) throw new IntegrationException("Invalid AccountID");
        if(!$AccountToken) throw new IntegrationException("Invalid AccountToken");
        if(!$AcceptorID) throw new IntegrationException("Invalid AcceptorID");
        if(!$NewAccountToken) throw new IntegrationException("Invalid NewAccountToken");

        $ApplicationID = $MerchantIdentity->getApplicationID();
        $ApplicationName = 'Simon Payments Gateway';
        $ApplicationVersion = '1';

        $CVVResponseType = 'Regular'; // Regular or Extended
        $ConsentCode = 'NotUsed'; // NotUsed or FaceToFace or Phone or Internet
        $TerminalSerialNumber = '';
        $TerminalEncryptionFormat = 'Default'; // Default or Format1 or Format2 or Format3 or Format4 or Format5 or Format6 or Format7
        $LaneNumber = '';
        $Model = '';
        $EMVKernelVersion = '';
        $ReversalType = 'System'; // System or Full or Partial;
        $BillPaymentFlag = 'False'; // False or True
        $ReversalReason = 'Unknown'; // Unknown or RejectedPartialApproval or Timeout or EditError or MACVerifyError or MACSyncError or EncryptionError or SystemError or PossibleFraud or CardRemoval or ChipDecline or TerminalError


        $TerminalID = $MerchantIdentity->getDefaultTerminalID();
        $CVVPresenceCode = 'UseDefault'; // UseDefault or NotProvided or Provided or Illegible or CustomerIllegible;
        $MotoECICode = 'NotUsed'; // UseDefault or NotUsed or Single or Recurring or Installment or SecureECommerce or NonAuthenticatedSecureTransaction or NonAuthenticatedSecureECommerceTransaction or NonSecureECommerceTransaction

        if(strtolower($OrderRow->getEntryMode()) === 'swipe') { // Card Present
            $CardholderPresentCode = 'Present'; // UseDefault or Unknown or Present or NotPresent or MailOrder or PhoneOrder or StandingAuth or ECommerce;
            $CardInputCode = 'MagstripeRead'; // UseDefault or Unknown or MagstripeRead or ContactlessMagstripeRead or ManualKeyed or ManualKeyedMagstripeFailure or ChipRead or ContactlessChipRead or ManualKeyedChipReadFailure or MagstripeReadChipReadFailure;
            $CardPresentCode = 'Present'; // UseDefault or Unknown or Present or NotPresent;
            $TerminalCapabilityCode = 'MagstripeReader'; // UseDefault or Unknown or NoTerminal or MagstripeReader or ContactlessMagstripeReader or KeyEntered or ChipReader or ContactlessChipReader
            $TerminalEnvironmentCode = 'LocalAttended'; // UseDefault or NoTerminal or LocalAttended or LocalUnattended or RemoteAttended or RemoteUnattended or ECommerce
            $TerminalType = 'PointOfSale'; // Unknown or PointOfSale or ECommerce or MOTO or FuelPump or ATM or Voice
            $MarketCode = 'Retail'; // Default or AutoRental or DirectMarketing or ECommerce or FoodRestaurant or HotelLodging or Petroleum or Retail or QSR;
        } else {
            $CardholderPresentCode = 'ECommerce'; // UseDefault or Unknown or Present or NotPresent or MailOrder or PhoneOrder or StandingAuth or ECommerce;
            $CardInputCode = 'ManualKeyed'; // UseDefault or Unknown or MagstripeRead or ContactlessMagstripeRead or ManualKeyed or ManualKeyedMagstripeFailure or ChipRead or ContactlessChipRead or ManualKeyedChipReadFailure or MagstripeReadChipReadFailure;
            $CardPresentCode = 'NotPresent'; // UseDefault or Unknown or Present or NotPresent;
            $TerminalCapabilityCode = 'KeyEntered'; // UseDefault or Unknown or NoTerminal or MagstripeReader or ContactlessMagstripeReader or KeyEntered or ChipReader or ContactlessChipReader
            $TerminalEnvironmentCode = 'ECommerce'; // UseDefault or NoTerminal or LocalAttended or LocalUnattended or RemoteAttended or RemoteUnattended or ECommerce
            $TerminalType = 'ECommerce'; // Unknown or PointOfSale or ECommerce or MOTO or FuelPump or ATM or Voice
            $MarketCode = 'ECommerce'; // Default or AutoRental or DirectMarketing or ECommerce or FoodRestaurant or HotelLodging or Petroleum or Retail or QSR;
            $MotoECICode = 'NonAuthenticatedSecureECommerceTransaction'; // UseDefault or NotUsed or Single or Recurring or Installment or SecureECommerce or NonAuthenticatedSecureTransaction or NonAuthenticatedSecureECommerceTransaction or NonSecureECommerceTransaction
        }


        $ClerkNumber = '';
        $ShiftID = '';
        $OriginalAuthorizedAmount = '';
        $TotalAuthorizedAmount = '';
        $SalesTaxAmount = '';
        $TipAmount = '';
        $DuplicateCheckDisableFlag = 'False';
        $DuplicateOverrideFlag = 'False';
        $TransactionStatus = '';
        $TransactionStatusCode = '';
        $HostTransactionID = '';
        $PartialApprovedFlag = 'True';
        $ApprovedAmount = '';
        $EMVEncryptionFormat = 'Default';


        $request = <<<PHP
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
      <terminal>
        <TerminalID>{$TerminalID}</TerminalID>
        <TerminalType>{$TerminalType}</TerminalType>
        <CardPresentCode>{$CardPresentCode}</CardPresentCode>
        <CardholderPresentCode>{$CardholderPresentCode}</CardholderPresentCode>
        <CardInputCode>{$CardInputCode}</CardInputCode>
        <CVVPresenceCode>{$CVVPresenceCode}</CVVPresenceCode>
        <TerminalCapabilityCode>{$TerminalCapabilityCode}</TerminalCapabilityCode>
        <TerminalEnvironmentCode>{$TerminalEnvironmentCode}</TerminalEnvironmentCode>
        <MotoECICode>{$MotoECICode}</MotoECICode>
        <CVVResponseType>{$CVVResponseType}</CVVResponseType>
        <ConsentCode>{$ConsentCode}</ConsentCode>
        <TerminalSerialNumber>{$TerminalSerialNumber}</TerminalSerialNumber>
        <TerminalEncryptionFormat>{$TerminalEncryptionFormat}</TerminalEncryptionFormat>
        <LaneNumber>{$LaneNumber}</LaneNumber>
        <Model>{$Model}</Model>
        <EMVKernelVersion>{$EMVKernelVersion}</EMVKernelVersion>
      </terminal>
      <transaction>
        <TransactionID>{$TransactionID}</TransactionID>
        <ClerkNumber>{$ClerkNumber}</ClerkNumber>
        <ShiftID>{$ShiftID}</ShiftID>
        <TransactionAmount>{$TransactionAmount}</TransactionAmount>
        <OriginalAuthorizedAmount>{$OriginalAuthorizedAmount}</OriginalAuthorizedAmount>
        <TotalAuthorizedAmount>{$TotalAuthorizedAmount}</TotalAuthorizedAmount>
        <SalesTaxAmount>{$SalesTaxAmount}</SalesTaxAmount>
        <TipAmount>{$TipAmount}</TipAmount>
        <ReferenceNumber>{$ReferenceNumber}</ReferenceNumber>
        <TicketNumber>{$TicketNumber}</TicketNumber>
        <ReversalType>{$ReversalType}</ReversalType>
        <MarketCode>{$MarketCode}</MarketCode>
        <BillPaymentFlag>{$BillPaymentFlag}</BillPaymentFlag>
        <DuplicateCheckDisableFlag>{$DuplicateCheckDisableFlag}</DuplicateCheckDisableFlag>
        <DuplicateOverrideFlag>{$DuplicateOverrideFlag}</DuplicateOverrideFlag>
        <RecurringFlag>{$DuplicateOverrideFlag}</RecurringFlag>
        <TransactionStatus>{$TransactionStatus}</TransactionStatus>
        <TransactionStatusCode>{$TransactionStatusCode}</TransactionStatusCode>
        <HostTransactionID>{$HostTransactionID}</HostTransactionID>
        <PartialApprovedFlag>{$PartialApprovedFlag}</PartialApprovedFlag>
        <ApprovedAmount>{$ApprovedAmount}</ApprovedAmount>
        <ConvenienceFeeAmount>{$ConvenienceFeeAmount}</ConvenienceFeeAmount>
        <EMVEncryptionFormat>{$EMVEncryptionFormat}</EMVEncryptionFormat>
        <ReversalReason>{$ReversalReason}</ReversalReason>
      </transaction>
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
PHP;

        return $request;
    }

    public function prepareCreditCardVoidRequest(
        ElementMerchantIdentity $MerchantIdentity,
        OrderRow $OrderRow,
        TransactionRow $AuthorizedTransaction,
        Array $post) {

        $Action = 'CreditCardVoid';
        $TransactionID = $AuthorizedTransaction->getTransactionID();

        $TicketNumber = substr(strtoupper($AuthorizedTransaction->getReferenceNumber()), 0, 6);
        $ReferenceNumber = $OrderRow->getReferenceNumber();

        $TransactionAmount = $OrderRow->getAmount();
        $ConvenienceFeeAmount = $MerchantIdentity->calculateConvenienceFee($OrderRow);
        $ConvenienceFeeAmount = number_format($ConvenienceFeeAmount, 2, '.', '');
        $TransactionAmount = number_format($TransactionAmount, 2, '.', '');


        $AccountID = $MerchantIdentity->getAccountID();
        $AccountToken = $MerchantIdentity->getAccountToken();
        $AcceptorID = $MerchantIdentity->getAcceptorID();
        $NewAccountToken = $MerchantIdentity->getAccountToken();

        if(!$AccountID) throw new IntegrationException("Invalid AccountID");
        if(!$AccountToken) throw new IntegrationException("Invalid AccountToken");
        if(!$AcceptorID) throw new IntegrationException("Invalid AcceptorID");
        if(!$NewAccountToken) throw new IntegrationException("Invalid NewAccountToken");

        $ApplicationID = $MerchantIdentity->getApplicationID();
        $ApplicationName = 'Simon Payments Gateway';
        $ApplicationVersion = '1';

        $CVVResponseType = 'Regular'; // Regular or Extended
        $ConsentCode = 'NotUsed'; // NotUsed or FaceToFace or Phone or Internet
        $TerminalSerialNumber = '';
        $TerminalEncryptionFormat = 'Default'; // Default or Format1 or Format2 or Format3 or Format4 or Format5 or Format6 or Format7
        $LaneNumber = '';
        $Model = '';
        $EMVKernelVersion = '';
        $ReversalType = 'System'; // System or Full or Partial;
        $BillPaymentFlag = 'False'; // False or True
        $ReversalReason = 'Unknown'; // Unknown or RejectedPartialApproval or Timeout or EditError or MACVerifyError or MACSyncError or EncryptionError or SystemError or PossibleFraud or CardRemoval or ChipDecline or TerminalError

        $TerminalID = $MerchantIdentity->getDefaultTerminalID();
        $CVVPresenceCode = 'UseDefault'; // UseDefault or NotProvided or Provided or Illegible or CustomerIllegible;
        $MotoECICode = 'NotUsed'; // UseDefault or NotUsed or Single or Recurring or Installment or SecureECommerce or NonAuthenticatedSecureTransaction or NonAuthenticatedSecureECommerceTransaction or NonSecureECommerceTransaction


        if(strtolower($OrderRow->getEntryMode()) === 'swipe') { // Card Present
            $CardholderPresentCode = 'Present'; // UseDefault or Unknown or Present or NotPresent or MailOrder or PhoneOrder or StandingAuth or ECommerce;
            $CardInputCode = 'MagstripeRead'; // UseDefault or Unknown or MagstripeRead or ContactlessMagstripeRead or ManualKeyed or ManualKeyedMagstripeFailure or ChipRead or ContactlessChipRead or ManualKeyedChipReadFailure or MagstripeReadChipReadFailure;
            $CardPresentCode = 'Present'; // UseDefault or Unknown or Present or NotPresent;
            $TerminalCapabilityCode = 'MagstripeReader'; // UseDefault or Unknown or NoTerminal or MagstripeReader or ContactlessMagstripeReader or KeyEntered or ChipReader or ContactlessChipReader
            $TerminalEnvironmentCode = 'LocalAttended'; // UseDefault or NoTerminal or LocalAttended or LocalUnattended or RemoteAttended or RemoteUnattended or ECommerce
            $TerminalType = 'PointOfSale'; // Unknown or PointOfSale or ECommerce or MOTO or FuelPump or ATM or Voice
            $MarketCode = 'Retail'; // Default or AutoRental or DirectMarketing or ECommerce or FoodRestaurant or HotelLodging or Petroleum or Retail or QSR;
        } else {
            $CardholderPresentCode = 'ECommerce'; // UseDefault or Unknown or Present or NotPresent or MailOrder or PhoneOrder or StandingAuth or ECommerce;
            $CardInputCode = 'ManualKeyed'; // UseDefault or Unknown or MagstripeRead or ContactlessMagstripeRead or ManualKeyed or ManualKeyedMagstripeFailure or ChipRead or ContactlessChipRead or ManualKeyedChipReadFailure or MagstripeReadChipReadFailure;
            $CardPresentCode = 'NotPresent'; // UseDefault or Unknown or Present or NotPresent;
            $TerminalCapabilityCode = 'KeyEntered'; // UseDefault or Unknown or NoTerminal or MagstripeReader or ContactlessMagstripeReader or KeyEntered or ChipReader or ContactlessChipReader
            $TerminalEnvironmentCode = 'ECommerce'; // UseDefault or NoTerminal or LocalAttended or LocalUnattended or RemoteAttended or RemoteUnattended or ECommerce
            $TerminalType = 'ECommerce'; // Unknown or PointOfSale or ECommerce or MOTO or FuelPump or ATM or Voice
            $MarketCode = 'ECommerce'; // Default or AutoRental or DirectMarketing or ECommerce or FoodRestaurant or HotelLodging or Petroleum or Retail or QSR;
            $MotoECICode = 'NonAuthenticatedSecureECommerceTransaction'; // UseDefault or NotUsed or Single or Recurring or Installment or SecureECommerce or NonAuthenticatedSecureTransaction or NonAuthenticatedSecureECommerceTransaction or NonSecureECommerceTransaction
        }

        $ClerkNumber = '';
        $ShiftID = '';
        $OriginalAuthorizedAmount = '';
        $TotalAuthorizedAmount = '';
        $SalesTaxAmount = '';
        $TipAmount = '';
        $DuplicateCheckDisableFlag = 'False';
        $DuplicateOverrideFlag = 'False';
        $TransactionStatus = '';
        $TransactionStatusCode = '';
        $HostTransactionID = '';
        $PartialApprovedFlag = 'True';
        $ApprovedAmount = '';
        $EMVEncryptionFormat = 'Default';


        $request = <<<PHP
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
      <terminal>
        <TerminalID>{$TerminalID}</TerminalID>
        <TerminalType>{$TerminalType}</TerminalType>
        <CardPresentCode>{$CardPresentCode}</CardPresentCode>
        <CardholderPresentCode>{$CardholderPresentCode}</CardholderPresentCode>
        <CardInputCode>{$CardInputCode}</CardInputCode>
        <CVVPresenceCode>{$CVVPresenceCode}</CVVPresenceCode>
        <TerminalCapabilityCode>{$TerminalCapabilityCode}</TerminalCapabilityCode>
        <TerminalEnvironmentCode>{$TerminalEnvironmentCode}</TerminalEnvironmentCode>
        <MotoECICode>{$MotoECICode}</MotoECICode>
        <CVVResponseType>{$CVVResponseType}</CVVResponseType>
        <ConsentCode>{$ConsentCode}</ConsentCode>
        <TerminalSerialNumber>{$TerminalSerialNumber}</TerminalSerialNumber>
        <TerminalEncryptionFormat>{$TerminalEncryptionFormat}</TerminalEncryptionFormat>
        <LaneNumber>{$LaneNumber}</LaneNumber>
        <Model>{$Model}</Model>
        <EMVKernelVersion>{$EMVKernelVersion}</EMVKernelVersion>
      </terminal>
      <transaction>
        <TransactionID>{$TransactionID}</TransactionID>
        <ClerkNumber>{$ClerkNumber}</ClerkNumber>
        <ShiftID>{$ShiftID}</ShiftID>
        <TransactionAmount>{$TransactionAmount}</TransactionAmount>
        <OriginalAuthorizedAmount>{$OriginalAuthorizedAmount}</OriginalAuthorizedAmount>
        <TotalAuthorizedAmount>{$TotalAuthorizedAmount}</TotalAuthorizedAmount>
        <SalesTaxAmount>{$SalesTaxAmount}</SalesTaxAmount>
        <TipAmount>{$TipAmount}</TipAmount>
        <ReferenceNumber>{$ReferenceNumber}</ReferenceNumber>
        <TicketNumber>{$TicketNumber}</TicketNumber>
        <ReversalType>{$ReversalType}</ReversalType>
        <MarketCode>{$MarketCode}</MarketCode>
        <BillPaymentFlag>{$BillPaymentFlag}</BillPaymentFlag>
        <DuplicateCheckDisableFlag>{$DuplicateCheckDisableFlag}</DuplicateCheckDisableFlag>
        <DuplicateOverrideFlag>{$DuplicateOverrideFlag}</DuplicateOverrideFlag>
        <RecurringFlag>{$DuplicateOverrideFlag}</RecurringFlag>
        <TransactionStatus>{$TransactionStatus}</TransactionStatus>
        <TransactionStatusCode>{$TransactionStatusCode}</TransactionStatusCode>
        <HostTransactionID>{$HostTransactionID}</HostTransactionID>
        <PartialApprovedFlag>{$PartialApprovedFlag}</PartialApprovedFlag>
        <ApprovedAmount>{$ApprovedAmount}</ApprovedAmount>
        <ConvenienceFeeAmount>{$ConvenienceFeeAmount}</ConvenienceFeeAmount>
        <EMVEncryptionFormat>{$EMVEncryptionFormat}</EMVEncryptionFormat>
        <ReversalReason>{$ReversalReason}</ReversalReason>
      </transaction>
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
PHP;

        return $request;
    }

    public function prepareCheckSaleRequest(
        ElementMerchantIdentity $MerchantIdentity,
        TransactionRow $TransactionRow,
        OrderRow $OrderRow,
        Array $post
    ) {
        $AccountID = $MerchantIdentity->getAccountID();
        $AccountToken = $MerchantIdentity->getAccountToken();
        $AcceptorID = $MerchantIdentity->getAcceptorID();
        $NewAccountToken = $MerchantIdentity->getAccountToken(); // ?

        if(!$AccountID) throw new IntegrationException("Invalid AccountID");
        if(!$AccountToken) throw new IntegrationException("Invalid AccountToken");
        if(!$AcceptorID) throw new IntegrationException("Invalid AcceptorID");
        if(!$NewAccountToken) throw new IntegrationException("Invalid NewAccountToken");

        $ApplicationID = $MerchantIdentity->getApplicationID();
        $ApplicationName = 'SimonPayments';
        $ApplicationVersion = '1';

        $TransactionID = $TransactionRow->getTransactionID();

        $TicketNumber = substr(strtoupper($TransactionRow->getReferenceNumber()), 0, 6);
        $ReferenceNumber = $OrderRow->getReferenceNumber();

        $TransactionAmount = $OrderRow->getAmount();
        $ConvenienceFeeAmount = $MerchantIdentity->calculateConvenienceFee($OrderRow);
        $ConvenienceFeeAmount = number_format($ConvenienceFeeAmount, 2, '.', '');
        $TransactionAmount = number_format($TransactionAmount, 2, '.', '');


        $BillingName = $OrderRow->getCardHolderFullName();
        $BillingAddress1 = $OrderRow->getPayeeAddress();
        $BillingAddress2 = $OrderRow->getPayeeAddress2();
        $BillingZipcode = $OrderRow->getPayeeZipCode();
        $BillingCity = $OrderRow->getPayeeCity();
        $BillingState = $OrderRow->getPayeeState();
        $BillingEmail = $OrderRow->getPayeeEmail();
        $BillingPhone = $OrderRow->getPayeePhone();

        $ShippingName = ''; // $BillingName;
        $ShippingAddress1 = ''; // $BillingAddress1;
        $ShippingAddress2 = ''; // $BillingAddress2;
        $ShippingCity = ''; // $BillingCity;
        $ShippingState = ''; // $BillingState;
        $ShippingZipcode = ''; // $BillingZipcode;
        $ShippingEmail = ''; // $BillingEmail;
        $ShippingPhone = ''; // $BillingPhone;


        $TerminalType = 'PointOfSale'; // Unknown or PointOfSale or ECommerce or MOTO or FuelPump or ATM or Voice
        $CardPresentCode = 'UseDefault'; // UseDefault or Unknown or Present or NotPresent;
        $CardholderPresentCode = 'UseDefault'; // UseDefault or Unknown or Present or NotPresent or MailOrder or PhoneOrder or StandingAuth or ECommerce;
        $CardInputCode = 'MagstripeRead'; // UseDefault or Unknown or MagstripeRead or ContactlessMagstripeRead or ManualKeyed or ManualKeyedMagstripeFailure or ChipRead or ContactlessChipRead or ManualKeyedChipReadFailure or MagstripeReadChipReadFailure;
        $CVVPresenceCode = 'UseDefault'; // UseDefault or NotProvided or Provided or Illegible or CustomerIllegible;
        $TerminalCapabilityCode = 'UseDefault'; // UseDefault or Unknown or NoTerminal or MagstripeReader or ContactlessMagstripeReader or KeyEntered or ChipReader or ContactlessChipReader
        $TerminalEnvironmentCode = 'UseDefault'; // UseDefault or NoTerminal or LocalAttended or LocalUnattended or RemoteAttended or RemoteUnattended or ECommerce
        $MotoECICode = 'NotUsed'; // UseDefault or NotUsed or Single or Recurring or Installment or SecureECommerce or NonAuthenticatedSecureTransaction or NonAuthenticatedSecureECommerceTransaction or NonSecureECommerceTransaction

        $CVVResponseType = 'Regular'; // Regular or Extended
        $ConsentCode = 'NotUsed'; // NotUsed or FaceToFace or Phone or Internet
        $TerminalSerialNumber = '';
        $TerminalEncryptionFormat = 'Default'; // Default or Format1 or Format2 or Format3 or Format4 or Format5 or Format6 or Format7
        $LaneNumber = '';
        $Model = '';
        $EMVKernelVersion = '';
        $ReversalType = 'Full'; // System or Full or Partial;
        $MarketCode = $MerchantIdentity->getMarketCode(); // Default or AutoRental or DirectMarketing or ECommerce or FoodRestaurant or HotelLodging or Petroleum or Retail or QSR;
        $BillPaymentFlag = 'False'; // False or True
        $ReversalReason = 'Unknown'; // Unknown or RejectedPartialApproval or Timeout or EditError or MACVerifyError or MACSyncError or EncryptionError or SystemError or PossibleFraud or CardRemoval or ChipDecline or TerminalError

        $ClerkNumber = '';
        $ShiftID = '';
        $OriginalAuthorizedAmount = '';
        $TotalAuthorizedAmount = '';
        $SalesTaxAmount = '';
        $TipAmount = '';
        $DuplicateCheckDisableFlag = 'False';
        $DuplicateOverrideFlag = 'False';
        $TransactionStatus = '';
        $TransactionStatusCode = '';
        $HostTransactionID = '';
        $PartialApprovedFlag = 'True';
        $ApprovedAmount = '';
        $EMVEncryptionFormat = 'Default';


        $TaxIDNumber = '';
        $DriversLicenseNumber = '';
        $DriversLicenseState = '';


        $TruncatedRoutingNumber = '';
        $TruncatedAccountNumber = '';
        $CheckType = $OrderRow->getCheckType();
        $CheckNumber = $OrderRow->getCheckNumber();
        $RoutingNumber = $OrderRow->getCheckRoutingNumber();
        $AccountNumber = $OrderRow->getCheckAccountNumber();
        $DDAAccountType = $OrderRow->getCheckAccountType();

        $Action = 'CheckSale';

            $request = <<<PHP
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
      <terminal>
        <TerminalID>{$ApplicationID}</TerminalID>
        <TerminalType>{$TerminalType}</TerminalType>
        <CardPresentCode>{$CardPresentCode}</CardPresentCode>
        <CardholderPresentCode>{$CardholderPresentCode}</CardholderPresentCode>
        <CardInputCode>{$CardInputCode}</CardInputCode>
        <CVVPresenceCode>{$CVVPresenceCode}</CVVPresenceCode>
        <TerminalCapabilityCode>{$TerminalCapabilityCode}</TerminalCapabilityCode>
        <TerminalEnvironmentCode>{$TerminalEnvironmentCode}</TerminalEnvironmentCode>
        <MotoECICode>{$MotoECICode}</MotoECICode>
        <CVVResponseType>{$CVVResponseType}</CVVResponseType>
        <ConsentCode>{$ConsentCode}</ConsentCode>
        <TerminalSerialNumber>{$TerminalSerialNumber}</TerminalSerialNumber>
        <TerminalEncryptionFormat>{$TerminalEncryptionFormat}</TerminalEncryptionFormat>
        <LaneNumber>{$LaneNumber}</LaneNumber>
        <Model>{$Model}</Model>
        <EMVKernelVersion>{$EMVKernelVersion}</EMVKernelVersion>
      </terminal>
      <demandDepositAccount>
        <DDAAccountType>{$DDAAccountType}</DDAAccountType>
        <AccountNumber>{$AccountNumber}</AccountNumber>
        <RoutingNumber>{$RoutingNumber}</RoutingNumber>
        <CheckNumber>{$CheckNumber}</CheckNumber>
        <CheckType>{$CheckType}</CheckType>
        <TruncatedAccountNumber>{$TruncatedAccountNumber}</TruncatedAccountNumber>
        <TruncatedRoutingNumber>{$TruncatedRoutingNumber}</TruncatedRoutingNumber>
      </demandDepositAccount>
      <transaction>
        <TransactionID>{$TransactionID}</TransactionID>
        <ClerkNumber>{$ClerkNumber}</ClerkNumber>
        <ShiftID>{$ShiftID}</ShiftID>
        <TransactionAmount>{$TransactionAmount}</TransactionAmount>
        <OriginalAuthorizedAmount>{$OriginalAuthorizedAmount}</OriginalAuthorizedAmount>
        <TotalAuthorizedAmount>{$TotalAuthorizedAmount}</TotalAuthorizedAmount>
        <SalesTaxAmount>{$SalesTaxAmount}</SalesTaxAmount>
        <TipAmount>{$TipAmount}</TipAmount>
        <ReferenceNumber>{$ReferenceNumber}</ReferenceNumber>
        <TicketNumber>{$TicketNumber}</TicketNumber>
        <ReversalType>{$ReversalType}</ReversalType>
        <MarketCode>{$MarketCode}</MarketCode>
        <BillPaymentFlag>{$BillPaymentFlag}</BillPaymentFlag>
        <DuplicateCheckDisableFlag>{$DuplicateCheckDisableFlag}</DuplicateCheckDisableFlag>
        <DuplicateOverrideFlag>{$DuplicateOverrideFlag}</DuplicateOverrideFlag>
        <RecurringFlag>{$DuplicateOverrideFlag}</RecurringFlag>
        <TransactionStatus>{$TransactionStatus}</TransactionStatus>
        <TransactionStatusCode>{$TransactionStatusCode}</TransactionStatusCode>
        <HostTransactionID>{$HostTransactionID}</HostTransactionID>
        <PartialApprovedFlag>{$PartialApprovedFlag}</PartialApprovedFlag>
        <ApprovedAmount>{$ApprovedAmount}</ApprovedAmount>
        <ConvenienceFeeAmount>{$ConvenienceFeeAmount}</ConvenienceFeeAmount>
        <EMVEncryptionFormat>{$EMVEncryptionFormat}</EMVEncryptionFormat>
        <ReversalReason>{$ReversalReason}</ReversalReason>
      </transaction>
      <identification>
        <TaxIDNumber>{$TaxIDNumber}</TaxIDNumber>
        <DriversLicenseNumber>{$DriversLicenseNumber}</DriversLicenseNumber>
        <DriversLicenseState>{$DriversLicenseState}</DriversLicenseState>
      </identification>
      <address>
        <BillingName>{$BillingName}</BillingName>
        <BillingAddress1>{$BillingAddress1}</BillingAddress1>
        <BillingAddress2>{$BillingAddress2}</BillingAddress2>
        <BillingCity>{$BillingCity}</BillingCity>
        <BillingState>{$BillingState}</BillingState>
        <BillingZipcode>{$BillingZipcode}</BillingZipcode>
        <BillingEmail>{$BillingEmail}</BillingEmail>
        <BillingPhone>{$BillingPhone}</BillingPhone>
        <ShippingName>{$ShippingName}</ShippingName>
        <ShippingAddress1>{$ShippingAddress1}</ShippingAddress1>
        <ShippingAddress2>{$ShippingAddress2}</ShippingAddress2>
        <ShippingCity>{$ShippingCity}</ShippingCity>
        <ShippingState>{$ShippingState}</ShippingState>
        <ShippingZipcode>{$ShippingZipcode}</ShippingZipcode>
        <ShippingEmail>{$ShippingEmail}</ShippingEmail>
        <ShippingPhone>{$ShippingPhone}</ShippingPhone>
      </address>
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
PHP;

        return $request;
    }

    public function prepareCheckVoidRequest(
        ElementMerchantIdentity $MerchantIdentity,
        OrderRow $OrderRow,
        TransactionRow $AuthorizedTransaction,
        Array $post
    ) {
        $AccountID = $MerchantIdentity->getAccountID();
        $AccountToken = $MerchantIdentity->getAccountToken();
        $AcceptorID = $MerchantIdentity->getAcceptorID();
        $NewAccountToken = $MerchantIdentity->getAccountToken(); // ?

        if(!$AccountID) throw new IntegrationException("Invalid AccountID");
        if(!$AccountToken) throw new IntegrationException("Invalid AccountToken");
        if(!$AcceptorID) throw new IntegrationException("Invalid AcceptorID");
        if(!$NewAccountToken) throw new IntegrationException("Invalid NewAccountToken");

        $ApplicationID = $MerchantIdentity->getApplicationID();
        $ApplicationName = 'SimonPayments';
        $ApplicationVersion = '1';

        $TransactionID = $AuthorizedTransaction->getTransactionID();

        $TicketNumber = substr(strtoupper($AuthorizedTransaction->getReferenceNumber()), 0, 6);
        $ReferenceNumber = $OrderRow->getReferenceNumber();

        $TransactionAmount = $OrderRow->getAmount();
        $ConvenienceFeeAmount = $MerchantIdentity->calculateConvenienceFee($OrderRow);
        $ConvenienceFeeAmount = number_format($ConvenienceFeeAmount, 2, '.', '');
        $TransactionAmount = number_format($TransactionAmount, 2, '.', '');


        $BillingAddress2 = null;
        $BillingCity = null;
        $BillingState = null;


        $TerminalType = 'PointOfSale'; // Unknown or PointOfSale or ECommerce or MOTO or FuelPump or ATM or Voice
        $CardPresentCode = 'UseDefault'; // UseDefault or Unknown or Present or NotPresent;
        $CardholderPresentCode = 'UseDefault'; // UseDefault or Unknown or Present or NotPresent or MailOrder or PhoneOrder or StandingAuth or ECommerce;
        $CardInputCode = 'MagstripeRead'; // UseDefault or Unknown or MagstripeRead or ContactlessMagstripeRead or ManualKeyed or ManualKeyedMagstripeFailure or ChipRead or ContactlessChipRead or ManualKeyedChipReadFailure or MagstripeReadChipReadFailure;
        $CVVPresenceCode = 'UseDefault'; // UseDefault or NotProvided or Provided or Illegible or CustomerIllegible;
        $TerminalCapabilityCode = 'UseDefault'; // UseDefault or Unknown or NoTerminal or MagstripeReader or ContactlessMagstripeReader or KeyEntered or ChipReader or ContactlessChipReader
        $TerminalEnvironmentCode = 'UseDefault'; // UseDefault or NoTerminal or LocalAttended or LocalUnattended or RemoteAttended or RemoteUnattended or ECommerce
        $MotoECICode = 'NotUsed'; // UseDefault or NotUsed or Single or Recurring or Installment or SecureECommerce or NonAuthenticatedSecureTransaction or NonAuthenticatedSecureECommerceTransaction or NonSecureECommerceTransaction

        $CVVResponseType = 'Regular'; // Regular or Extended
        $ConsentCode = 'NotUsed'; // NotUsed or FaceToFace or Phone or Internet
        $TerminalSerialNumber = '';
        $TerminalEncryptionFormat = 'Default'; // Default or Format1 or Format2 or Format3 or Format4 or Format5 or Format6 or Format7
        $LaneNumber = '';
        $Model = '';
        $EMVKernelVersion = '';
        $ReversalType = 'Full'; // System or Full or Partial;
        $MarketCode = $MerchantIdentity->getMarketCode(); // Default or AutoRental or DirectMarketing or ECommerce or FoodRestaurant or HotelLodging or Petroleum or Retail or QSR;
        $BillPaymentFlag = 'False'; // False or True
        $ReversalReason = 'Unknown'; // Unknown or RejectedPartialApproval or Timeout or EditError or MACVerifyError or MACSyncError or EncryptionError or SystemError or PossibleFraud or CardRemoval or ChipDecline or TerminalError

        $ClerkNumber = '';
        $ShiftID = '';
        $OriginalAuthorizedAmount = '';
        $TotalAuthorizedAmount = '';
        $SalesTaxAmount = '';
        $TipAmount = '';
        $DuplicateCheckDisableFlag = 'False';
        $DuplicateOverrideFlag = 'False';
        $TransactionStatus = '';
        $TransactionStatusCode = '';
        $HostTransactionID = '';
        $PartialApprovedFlag = 'True';
        $ApprovedAmount = '';
        $EMVEncryptionFormat = 'Default';

        $Action = 'CheckVoid';

        $request = <<<PHP
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
      <terminal>
        <TerminalID>{$ApplicationID}</TerminalID>
        <TerminalType>{$TerminalType}</TerminalType>
        <CardPresentCode>{$CardPresentCode}</CardPresentCode>
        <CardholderPresentCode>{$CardholderPresentCode}</CardholderPresentCode>
        <CardInputCode>{$CardInputCode}</CardInputCode>
        <CVVPresenceCode>{$CVVPresenceCode}</CVVPresenceCode>
        <TerminalCapabilityCode>{$TerminalCapabilityCode}</TerminalCapabilityCode>
        <TerminalEnvironmentCode>{$TerminalEnvironmentCode}</TerminalEnvironmentCode>
        <MotoECICode>{$MotoECICode}</MotoECICode>
        <CVVResponseType>{$CVVResponseType}</CVVResponseType>
        <ConsentCode>{$ConsentCode}</ConsentCode>
        <TerminalSerialNumber>{$TerminalSerialNumber}</TerminalSerialNumber>
        <TerminalEncryptionFormat>{$TerminalEncryptionFormat}</TerminalEncryptionFormat>
        <LaneNumber>{$LaneNumber}</LaneNumber>
        <Model>{$Model}</Model>
        <EMVKernelVersion>{$EMVKernelVersion}</EMVKernelVersion>
      </terminal>
      <transaction>
        <TransactionID>{$TransactionID}</TransactionID>
        <ClerkNumber>{$ClerkNumber}</ClerkNumber>
        <ShiftID>{$ShiftID}</ShiftID>
        <TransactionAmount>{$TransactionAmount}</TransactionAmount>
        <OriginalAuthorizedAmount>{$OriginalAuthorizedAmount}</OriginalAuthorizedAmount>
        <TotalAuthorizedAmount>{$TotalAuthorizedAmount}</TotalAuthorizedAmount>
        <SalesTaxAmount>{$SalesTaxAmount}</SalesTaxAmount>
        <TipAmount>{$TipAmount}</TipAmount>
        <ReferenceNumber>{$ReferenceNumber}</ReferenceNumber>
        <TicketNumber>{$TicketNumber}</TicketNumber>
        <ReversalType>{$ReversalType}</ReversalType>
        <MarketCode>{$MarketCode}</MarketCode>
        <BillPaymentFlag>{$BillPaymentFlag}</BillPaymentFlag>
        <DuplicateCheckDisableFlag>{$DuplicateCheckDisableFlag}</DuplicateCheckDisableFlag>
        <DuplicateOverrideFlag>{$DuplicateOverrideFlag}</DuplicateOverrideFlag>
        <RecurringFlag>{$DuplicateOverrideFlag}</RecurringFlag>
        <TransactionStatus>{$TransactionStatus}</TransactionStatus>
        <TransactionStatusCode>{$TransactionStatusCode}</TransactionStatusCode>
        <HostTransactionID>{$HostTransactionID}</HostTransactionID>
        <PartialApprovedFlag>{$PartialApprovedFlag}</PartialApprovedFlag>
        <ApprovedAmount>{$ApprovedAmount}</ApprovedAmount>
        <ConvenienceFeeAmount>{$ConvenienceFeeAmount}</ConvenienceFeeAmount>
        <EMVEncryptionFormat>{$EMVEncryptionFormat}</EMVEncryptionFormat>
        <ReversalReason>{$ReversalReason}</ReversalReason>
      </transaction>
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
PHP;

        return $request;
    }

    public function prepareCheckReturnRequest(
        ElementMerchantIdentity $MerchantIdentity,
        OrderRow $OrderRow,
        TransactionRow $AuthorizedTransaction,
        Array $post
    ) {
        $AccountID = $MerchantIdentity->getAccountID();
        $AccountToken = $MerchantIdentity->getAccountToken();
        $AcceptorID = $MerchantIdentity->getAcceptorID();
        $NewAccountToken = $MerchantIdentity->getAccountToken(); // ?

        if(!$AccountID) throw new IntegrationException("Invalid AccountID");
        if(!$AccountToken) throw new IntegrationException("Invalid AccountToken");
        if(!$AcceptorID) throw new IntegrationException("Invalid AcceptorID");
        if(!$NewAccountToken) throw new IntegrationException("Invalid NewAccountToken");

        $ApplicationID = $MerchantIdentity->getApplicationID();
        $ApplicationName = 'SimonPayments';
        $ApplicationVersion = '1';

        $TransactionID = $AuthorizedTransaction->getTransactionID();

        $TicketNumber = substr(strtoupper($AuthorizedTransaction->getReferenceNumber()), 0, 6);
        $ReferenceNumber = $OrderRow->getReferenceNumber();

        $TransactionAmount = $OrderRow->getAmount();
        $ConvenienceFeeAmount = $MerchantIdentity->calculateConvenienceFee($OrderRow);
        $ConvenienceFeeAmount = number_format($ConvenienceFeeAmount, 2, '.', '');
        $TransactionAmount = number_format($TransactionAmount, 2, '.', '');


        $BillingAddress2 = null;
        $BillingCity = null;
        $BillingState = null;


        $TerminalType = 'PointOfSale'; // Unknown or PointOfSale or ECommerce or MOTO or FuelPump or ATM or Voice
        $CardPresentCode = 'UseDefault'; // UseDefault or Unknown or Present or NotPresent;
        $CardholderPresentCode = 'UseDefault'; // UseDefault or Unknown or Present or NotPresent or MailOrder or PhoneOrder or StandingAuth or ECommerce;
        $CardInputCode = 'MagstripeRead'; // UseDefault or Unknown or MagstripeRead or ContactlessMagstripeRead or ManualKeyed or ManualKeyedMagstripeFailure or ChipRead or ContactlessChipRead or ManualKeyedChipReadFailure or MagstripeReadChipReadFailure;
        $CVVPresenceCode = 'UseDefault'; // UseDefault or NotProvided or Provided or Illegible or CustomerIllegible;
        $TerminalCapabilityCode = 'UseDefault'; // UseDefault or Unknown or NoTerminal or MagstripeReader or ContactlessMagstripeReader or KeyEntered or ChipReader or ContactlessChipReader
        $TerminalEnvironmentCode = 'UseDefault'; // UseDefault or NoTerminal or LocalAttended or LocalUnattended or RemoteAttended or RemoteUnattended or ECommerce
        $MotoECICode = 'NotUsed'; // UseDefault or NotUsed or Single or Recurring or Installment or SecureECommerce or NonAuthenticatedSecureTransaction or NonAuthenticatedSecureECommerceTransaction or NonSecureECommerceTransaction

        $CVVResponseType = 'Regular'; // Regular or Extended
        $ConsentCode = 'NotUsed'; // NotUsed or FaceToFace or Phone or Internet
        $TerminalSerialNumber = '';
        $TerminalEncryptionFormat = 'Default'; // Default or Format1 or Format2 or Format3 or Format4 or Format5 or Format6 or Format7
        $LaneNumber = '';
        $Model = '';
        $EMVKernelVersion = '';
        $ReversalType = 'Full'; // System or Full or Partial;
        $MarketCode = $MerchantIdentity->getMarketCode(); // Default or AutoRental or DirectMarketing or ECommerce or FoodRestaurant or HotelLodging or Petroleum or Retail or QSR;
        $BillPaymentFlag = 'False'; // False or True
        $ReversalReason = 'Unknown'; // Unknown or RejectedPartialApproval or Timeout or EditError or MACVerifyError or MACSyncError or EncryptionError or SystemError or PossibleFraud or CardRemoval or ChipDecline or TerminalError

        $ClerkNumber = '';
        $ShiftID = '';
        $OriginalAuthorizedAmount = '';
        $TotalAuthorizedAmount = '';
        $SalesTaxAmount = '';
        $TipAmount = '';
        $DuplicateCheckDisableFlag = 'False';
        $DuplicateOverrideFlag = 'False';
        $TransactionStatus = '';
        $TransactionStatusCode = '';
        $HostTransactionID = '';
        $PartialApprovedFlag = 'True';
        $ApprovedAmount = '';
        $EMVEncryptionFormat = 'Default';

        $Action = 'CheckReturn';

        $request = <<<PHP
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
      <terminal>
        <TerminalID>{$ApplicationID}</TerminalID>
        <TerminalType>{$TerminalType}</TerminalType>
        <CardPresentCode>{$CardPresentCode}</CardPresentCode>
        <CardholderPresentCode>{$CardholderPresentCode}</CardholderPresentCode>
        <CardInputCode>{$CardInputCode}</CardInputCode>
        <CVVPresenceCode>{$CVVPresenceCode}</CVVPresenceCode>
        <TerminalCapabilityCode>{$TerminalCapabilityCode}</TerminalCapabilityCode>
        <TerminalEnvironmentCode>{$TerminalEnvironmentCode}</TerminalEnvironmentCode>
        <MotoECICode>{$MotoECICode}</MotoECICode>
        <CVVResponseType>{$CVVResponseType}</CVVResponseType>
        <ConsentCode>{$ConsentCode}</ConsentCode>
        <TerminalSerialNumber>{$TerminalSerialNumber}</TerminalSerialNumber>
        <TerminalEncryptionFormat>{$TerminalEncryptionFormat}</TerminalEncryptionFormat>
        <LaneNumber>{$LaneNumber}</LaneNumber>
        <Model>{$Model}</Model>
        <EMVKernelVersion>{$EMVKernelVersion}</EMVKernelVersion>
      </terminal>
      <transaction>
        <TransactionID>{$TransactionID}</TransactionID>
        <ClerkNumber>{$ClerkNumber}</ClerkNumber>
        <ShiftID>{$ShiftID}</ShiftID>
        <TransactionAmount>{$TransactionAmount}</TransactionAmount>
        <OriginalAuthorizedAmount>{$OriginalAuthorizedAmount}</OriginalAuthorizedAmount>
        <TotalAuthorizedAmount>{$TotalAuthorizedAmount}</TotalAuthorizedAmount>
        <SalesTaxAmount>{$SalesTaxAmount}</SalesTaxAmount>
        <TipAmount>{$TipAmount}</TipAmount>
        <ReferenceNumber>{$ReferenceNumber}</ReferenceNumber>
        <TicketNumber>{$TicketNumber}</TicketNumber>
        <ReversalType>{$ReversalType}</ReversalType>
        <MarketCode>{$MarketCode}</MarketCode>
        <BillPaymentFlag>{$BillPaymentFlag}</BillPaymentFlag>
        <DuplicateCheckDisableFlag>{$DuplicateCheckDisableFlag}</DuplicateCheckDisableFlag>
        <DuplicateOverrideFlag>{$DuplicateOverrideFlag}</DuplicateOverrideFlag>
        <RecurringFlag>{$DuplicateOverrideFlag}</RecurringFlag>
        <TransactionStatus>{$TransactionStatus}</TransactionStatus>
        <TransactionStatusCode>{$TransactionStatusCode}</TransactionStatusCode>
        <HostTransactionID>{$HostTransactionID}</HostTransactionID>
        <PartialApprovedFlag>{$PartialApprovedFlag}</PartialApprovedFlag>
        <ApprovedAmount>{$ApprovedAmount}</ApprovedAmount>
        <ConvenienceFeeAmount>{$ConvenienceFeeAmount}</ConvenienceFeeAmount>
        <EMVEncryptionFormat>{$EMVEncryptionFormat}</EMVEncryptionFormat>
        <ReversalReason>{$ReversalReason}</ReversalReason>
      </transaction>
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
PHP;

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