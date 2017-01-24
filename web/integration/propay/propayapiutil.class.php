<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/21/2016
 * Time: 7:29 PM
 */
namespace Integration\ProPay;

use Integration\Model\Ex\IntegrationException;
use Order\Model\OrderRow;
use Order\Model\TransactionRow;

class ProPayAPIUtil {

    //Change this URL to point to Production by changing it to https://api.propay.com/... instead of https://xmltestapi.propay.com/....
    const POST_URL_PAYERS = "/ProtectPay/Payers/"; // https://xmltestapi.propay.com

    public function executeAPIRequest(
        ProPayMerchantIdentity $MerchantIdentity,
        $api_path,
        Array $args = array(),
        Array $post = array()
    ) {

        $BillerID = $MerchantIdentity->getBillerID();
        $AuthToken = $MerchantIdentity->getAuthToken();


        $url = "https://xmltestapi.propay.com/" . $api_path;
        $Auth_Header = "Basic " . base64_encode($BillerID . ":" . $AuthToken);
        $HTTP_Verb = "PUT";
        $Payload = json_encode($args);

        /* The HTTP header must include the SOAPAction */
        $header = array(
            "Content-type: application/json; charset=utf-8",
            "Authorization: " . $Auth_Header,
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $HTTP_Verb);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $Payload);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);

        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        return $response;
    }

    public function prepareCreditCardSaleRequest(
        ProPayMerchantIdentity $MerchantIdentity,
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
                'PartialApprovedFlag' => 'False',
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

        $request = $this->prepareJSONRequest(
            $MerchantIdentity,
            $Action,
            $args,
            $post
        );

        return $request;
    }
    public function prepareCreditCardReversalRequest(
        ProPayMerchantIdentity $MerchantIdentity,
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
                'PartialApprovedFlag' => 'False',
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

        $request = $this->prepareJSONRequest(
            $MerchantIdentity,
            $Action,
            $args,
            $post
        );
        return $request;
    }

    public function prepareCreditCardReturnRequest(
        ProPayMerchantIdentity $MerchantIdentity,
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
//                'CardholderName' => $OrderRow->getCardHolderFullName(),
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
                'TransactionID' => $AuthorizedTransaction->getTransactionID(),
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
        ProPayMerchantIdentity $MerchantIdentity,
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
        ProPayMerchantIdentity $MerchantIdentity,
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
        ProPayMerchantIdentity $MerchantIdentity,
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

}