<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/21/2016
 * Time: 7:29 PM
 */
namespace Integration\Element;

use Integration\Model\AbstractMerchantIdentity;
use Integration\Model\IntegrationRow;
use Order\Model\OrderRow;
use Transaction\Model\TransactionRow;

class ElementAPIUtil {

    /**
     * @param ElementMerchantIdentity|AbstractMerchantIdentity $MerchantIdentity
     * @param TransactionRow $TransactionRow
     * @param OrderRow $OrderRow
     * @param array $post
     * @return string
     */
    public function prepareCreditCardSaleRequest(
        ElementMerchantIdentity $MerchantIdentity,
        TransactionRow $TransactionRow,
        OrderRow $OrderRow,
        Array $post
    ) {
        $CVV = @$post['cvv'];
        $PINBlock = @$post['pin'];

        $MagneprintData = $OrderRow->getCardTrack();
        $CardholderName = $OrderRow->getCardHolderFullName();
        $TransactionID = $TransactionRow->getTransactionID();

        $AccountID = $MerchantIdentity->getAccountID();
        $AccountToken = $MerchantIdentity->getAccountToken();
        $AcceptorID = $MerchantIdentity->getAcceptorID();
        $NewAccountToken = $MerchantIdentity->getAccountToken(); // ?

        $CardNumber = $OrderRow->getCardNumber();
        $TruncatedCardNumber = substr($CardNumber, -4, 4);
        $ExpirationMonth = $OrderRow->getCardExpMonth();
        $ExpirationYear = $OrderRow->getCardExpYear();

        $TransactionAmount = $OrderRow->getAmount();
        $ConvenienceFeeAmount = $MerchantIdentity->calculateServiceFee($OrderRow);

        $BillingName = $OrderRow->getCardHolderFullName();
        $BillingAddress1 = null;
        $BillingAddress2 = null;
        $BillingCity = null;
        $BillingState = null;
        $BillingZipcode = $OrderRow->getPayeeZipCode();
        $BillingEmail = $OrderRow->getPayeeEmail();
        $BillingPhone = $OrderRow->getPayeePhone();

        $ShippingName = $BillingName;
        $ShippingAddress1 = $BillingAddress1;
        $ShippingAddress2 = $BillingAddress2;
        $ShippingCity = $BillingCity;
        $ShippingState = $BillingState;
        $ShippingZipcode = $BillingZipcode;
        $ShippingEmail = $BillingEmail;
        $ShippingPhone = $BillingPhone;


        $TerminalType = 'PointOfSale'; // Unknown or PointOfSale or ECommerce or MOTO or FuelPump or ATM or Voice
        $CardPresentCode = 'UseDefault'; // UseDefault or Unknown or Present or NotPresent;
        $CardholderPresentCode = 'UseDefault'; // UseDefault or Unknown or Present or NotPresent or MailOrder or PhoneOrder or StandingAuth or ECommerce;
        $CardInputCode = 'MagstripeRead'; // UseDefault or Unknown or MagstripeRead or ContactlessMagstripeRead or ManualKeyed or ManualKeyedMagstripeFailure or ChipRead or ContactlessChipRead or ManualKeyedChipReadFailure or MagstripeReadChipReadFailure;
        $CVVPresenceCode = 'UseDefault'; // UseDefault or NotProvided or Provided or Illegible or CustomerIllegible;
        $TerminalCapabilityCode = 'UseDefault'; // UseDefault or Unknown or NoTerminal or MagstripeReader or ContactlessMagstripeReader or KeyEntered or ChipReader or ContactlessChipReader
        $TerminalEnvironmentCode = 'UseDefault'; // UseDefault or NoTerminal or LocalAttended or LocalUnattended or RemoteAttended or RemoteUnattended or ECommerce
        $MotoECICode = 'UseDefault'; // UseDefault or NotUsed or Single or Recurring or Installment or SecureECommerce or NonAuthenticatedSecureTransaction or NonAuthenticatedSecureECommerceTransaction or NonSecureECommerceTransaction

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
        $MarketCode = 'Default'; // Default or AutoRental or DirectMarketing or ECommerce or FoodRestaurant or HotelLodging or Petroleum or Retail or QSR;
        $BillPaymentFlag = 'False'; // False or True
        $ReversalReason = 'Unknown'; // Unknown or RejectedPartialApproval or Timeout or EditError or MACVerifyError or MACSyncError or EncryptionError or SystemError or PossibleFraud or CardRemoval or ChipDecline or TerminalError


        $ApplicationID = $MerchantIdentity->getApplicationID();
        $ApplicationName = 'Express';
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
        $ApprovalNumber = '';
        $ReferenceNumber = '';
        $TicketNumber = '';
        $AcquirerData = '';
        $CashBackAmount = '';
        $DuplicateCheckDisableFlag = 'False';
        $DuplicateOverrideFlag = 'False';
        $CommercialCardCustomerCode = '';
        $ProcessorName = '';
        $TransactionStatus = '';
        $TransactionStatusCode = '';
        $HostTransactionID = '';
        $TransactionSetupID = '';
        $MerchantVerificationValue = '';
        $PartialApprovedFlag = 'False';
        $ApprovedAmount = '';
        $CommercialCardResponseCode = '';
        $BalanceAmount = '';
        $BalanceCurrencyCode = '';
        $GiftCardStatusCode = '';
        $BillPayerAccountNumber = '';
        $GiftCardBalanceTransferCode = '';
        $EMVEncryptionFormat = 'Default';

        $Action = 'CreditCardSale';
        if($PINBlock)
            $Action = 'DebitCardSale';

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
        <ApprovalNumber>{$ApprovalNumber}</ApprovalNumber>
        <ReferenceNumber>{$ReferenceNumber}</ReferenceNumber>
        <TicketNumber>{$TicketNumber}</TicketNumber>
        <ReversalType>{$ReversalType}</ReversalType>
        <MarketCode>{$MarketCode}</MarketCode>
        <AcquirerData>{$AcquirerData}</AcquirerData>
        <CashBackAmount>{$CashBackAmount}</CashBackAmount>
        <BillPaymentFlag>{$BillPaymentFlag}</BillPaymentFlag>
        <DuplicateCheckDisableFlag>{$DuplicateCheckDisableFlag}</DuplicateCheckDisableFlag>
        <DuplicateOverrideFlag>{$DuplicateOverrideFlag}</DuplicateOverrideFlag>
        <RecurringFlag>{$DuplicateOverrideFlag}</RecurringFlag>
        <CommercialCardCustomerCode>{$CommercialCardCustomerCode}</CommercialCardCustomerCode>
        <ProcessorName>{$ProcessorName}</ProcessorName>
        <TransactionStatus>{$TransactionStatus}</TransactionStatus>
        <TransactionStatusCode>{$TransactionStatusCode}</TransactionStatusCode>
        <HostTransactionID>{$HostTransactionID}</HostTransactionID>
        <TransactionSetupID>{$TransactionSetupID}</TransactionSetupID>
        <MerchantVerificationValue>{$MerchantVerificationValue}</MerchantVerificationValue>
        <PartialApprovedFlag>{$PartialApprovedFlag}</PartialApprovedFlag>
        <ApprovedAmount>{$ApprovedAmount}</ApprovedAmount>
        <CommercialCardResponseCode>{$CommercialCardResponseCode}</CommercialCardResponseCode>
        <BalanceAmount>{$BalanceAmount}</BalanceAmount>
        <BalanceCurrencyCode>{$BalanceCurrencyCode}</BalanceCurrencyCode>
        <ConvenienceFeeAmount>{$ConvenienceFeeAmount}</ConvenienceFeeAmount>
        <GiftCardStatusCode>{$GiftCardStatusCode}</GiftCardStatusCode>
        <BillPayerAccountNumber>{$BillPayerAccountNumber}</BillPayerAccountNumber>
        <GiftCardBalanceTransferCode>{$GiftCardBalanceTransferCode}</GiftCardBalanceTransferCode>
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


    /**
     * @param ElementMerchantIdentity|AbstractMerchantIdentity $MerchantIdentity
     * @param TransactionRow $TransactionRow
     * @param OrderRow $OrderRow
     * @param array $post
     * @return string
     */
    public function prepareCheckSaleRequest(
        ElementMerchantIdentity $MerchantIdentity,
        TransactionRow $TransactionRow,
        OrderRow $OrderRow,
        Array $post
    ) {
        $CVV = @$post['cvv'];
        $PINBlock = @$post['pin'];

        $MagneprintData = $OrderRow->getCardTrack();
        $CardholderName = $OrderRow->getCardHolderFullName();

        $AccountID = $MerchantIdentity->getAccountID();
        $AccountToken = $MerchantIdentity->getAccountToken();
        $AcceptorID = $MerchantIdentity->getAcceptorID();
        $NewAccountToken = $MerchantIdentity->getAccountToken(); // ?

        $ApplicationID = $MerchantIdentity->getApplicationID();
        $ApplicationName = 'SimonPayments';
        $ApplicationVersion = '1';

        $TransactionID = $TransactionRow->getTransactionID();
        $ReferenceNumber = '';
        $TicketNumber = '';


        $CardNumber = $OrderRow->getCardNumber();
        $TruncatedCardNumber = substr($CardNumber, -4, 4);
        $ExpirationMonth = $OrderRow->getCardExpMonth();
        $ExpirationYear = $OrderRow->getCardExpYear();

        $TransactionAmount = $OrderRow->getAmount();
        $ConvenienceFeeAmount = $MerchantIdentity->calculateServiceFee($OrderRow);

        $BillingName = $OrderRow->getCardHolderFullName();
        $BillingAddress1 = null;
        $BillingAddress2 = null;
        $BillingCity = null;
        $BillingState = null;
        $BillingZipcode = $OrderRow->getPayeeZipCode();
        $BillingEmail = $OrderRow->getPayeeEmail();
        $BillingPhone = $OrderRow->getPayeePhone();

        $ShippingName = $BillingName;
        $ShippingAddress1 = $BillingAddress1;
        $ShippingAddress2 = $BillingAddress2;
        $ShippingCity = $BillingCity;
        $ShippingState = $BillingState;
        $ShippingZipcode = $BillingZipcode;
        $ShippingEmail = $BillingEmail;
        $ShippingPhone = $BillingPhone;


        $TerminalType = 'PointOfSale'; // Unknown or PointOfSale or ECommerce or MOTO or FuelPump or ATM or Voice
        $CardPresentCode = 'UseDefault'; // UseDefault or Unknown or Present or NotPresent;
        $CardholderPresentCode = 'UseDefault'; // UseDefault or Unknown or Present or NotPresent or MailOrder or PhoneOrder or StandingAuth or ECommerce;
        $CardInputCode = 'MagstripeRead'; // UseDefault or Unknown or MagstripeRead or ContactlessMagstripeRead or ManualKeyed or ManualKeyedMagstripeFailure or ChipRead or ContactlessChipRead or ManualKeyedChipReadFailure or MagstripeReadChipReadFailure;
        $CVVPresenceCode = 'UseDefault'; // UseDefault or NotProvided or Provided or Illegible or CustomerIllegible;
        $TerminalCapabilityCode = 'UseDefault'; // UseDefault or Unknown or NoTerminal or MagstripeReader or ContactlessMagstripeReader or KeyEntered or ChipReader or ContactlessChipReader
        $TerminalEnvironmentCode = 'UseDefault'; // UseDefault or NoTerminal or LocalAttended or LocalUnattended or RemoteAttended or RemoteUnattended or ECommerce
        $MotoECICode = 'UseDefault'; // UseDefault or NotUsed or Single or Recurring or Installment or SecureECommerce or NonAuthenticatedSecureTransaction or NonAuthenticatedSecureECommerceTransaction or NonSecureECommerceTransaction

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
        $MarketCode = 'Default'; // Default or AutoRental or DirectMarketing or ECommerce or FoodRestaurant or HotelLodging or Petroleum or Retail or QSR;
        $BillPaymentFlag = 'False'; // False or True
        $ReversalReason = 'Unknown'; // Unknown or RejectedPartialApproval or Timeout or EditError or MACVerifyError or MACSyncError or EncryptionError or SystemError or PossibleFraud or CardRemoval or ChipDecline or TerminalError



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
        $ApprovalNumber = '';
        $AcquirerData = '';
        $CashBackAmount = '';
        $DuplicateCheckDisableFlag = 'False';
        $DuplicateOverrideFlag = 'False';
        $CommercialCardCustomerCode = '';
        $ProcessorName = '';
        $TransactionStatus = '';
        $TransactionStatusCode = '';
        $HostTransactionID = '';
        $TransactionSetupID = '';
        $MerchantVerificationValue = '';
        $PartialApprovedFlag = 'False';
        $ApprovedAmount = '';
        $CommercialCardResponseCode = '';
        $BalanceAmount = '';
        $BalanceCurrencyCode = '';
        $GiftCardStatusCode = '';
        $BillPayerAccountNumber = '';
        $GiftCardBalanceTransferCode = '';
        $EMVEncryptionFormat = 'Default';

        $Action = 'CheckSale';

        $BirthDate = '';
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

        $request = <<<PHP
<?xml version="1.0" encoding="utf-8"?>
<soap12:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">
  <soap12:Body>
    <{$Action} Sale xmlns="https://transaction.elementexpress.com">
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
        <ApprovalNumber>{$ApprovalNumber}</ApprovalNumber>
        <ReferenceNumber>{$ReferenceNumber}</ReferenceNumber>
        <TicketNumber>{$TicketNumber}</TicketNumber>
        <ReversalType>{$ReversalType}</ReversalType>
        <MarketCode>{$MarketCode}</MarketCode>
        <AcquirerData>{$AcquirerData}</AcquirerData>
        <CashBackAmount>{$CashBackAmount}</CashBackAmount>
        <BillPaymentFlag>{$BillPaymentFlag}</BillPaymentFlag>
        <DuplicateCheckDisableFlag>{$DuplicateCheckDisableFlag}</DuplicateCheckDisableFlag>
        <DuplicateOverrideFlag>{$DuplicateOverrideFlag}</DuplicateOverrideFlag>
        <RecurringFlag>{$DuplicateOverrideFlag}</RecurringFlag>
        <CommercialCardCustomerCode>{$CommercialCardCustomerCode}</CommercialCardCustomerCode>
        <ProcessorName>{$ProcessorName}</ProcessorName>
        <TransactionStatus>{$TransactionStatus}</TransactionStatus>
        <TransactionStatusCode>{$TransactionStatusCode}</TransactionStatusCode>
        <HostTransactionID>{$HostTransactionID}</HostTransactionID>
        <TransactionSetupID>{$TransactionSetupID}</TransactionSetupID>
        <MerchantVerificationValue>{$MerchantVerificationValue}</MerchantVerificationValue>
        <PartialApprovedFlag>{$PartialApprovedFlag}</PartialApprovedFlag>
        <ApprovedAmount>{$ApprovedAmount}</ApprovedAmount>
        <CommercialCardResponseCode>{$CommercialCardResponseCode}</CommercialCardResponseCode>
        <BalanceAmount>{$BalanceAmount}</BalanceAmount>
        <BalanceCurrencyCode>{$BalanceCurrencyCode}</BalanceCurrencyCode>
        <ConvenienceFeeAmount>{$ConvenienceFeeAmount}</ConvenienceFeeAmount>
        <GiftCardStatusCode>{$GiftCardStatusCode}</GiftCardStatusCode>
        <BillPayerAccountNumber>{$BillPayerAccountNumber}</BillPayerAccountNumber>
        <GiftCardBalanceTransferCode>{$GiftCardBalanceTransferCode}</GiftCardBalanceTransferCode>
        <EMVEncryptionFormat>{$EMVEncryptionFormat}</EMVEncryptionFormat>
        <ReversalReason>{$ReversalReason}</ReversalReason>
      </transaction>
      <identification>
        <TaxIDNumber>{$TaxIDNumber}</TaxIDNumber>
        <DriversLicenseNumber>{$DriversLicenseNumber}</DriversLicenseNumber>
        <DriversLicenseState>{$DriversLicenseState}</DriversLicenseState>
        <BirthDate>{$BirthDate}</BirthDate>
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

}