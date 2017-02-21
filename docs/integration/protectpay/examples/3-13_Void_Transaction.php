<?php
/*
ProPay provides the following code “AS IS.” 
ProPay makes no warranties and ProPay disclaims all warranties and conditions, express, implied or statutory, 
including without limitation the implied warranties of title, non-infringement, merchantability, and fitness for a particular purpose. 
ProPay does not warrant that the code will be uninterrupted or error free, 
nor does ProPay make any warranty as to the performance or any results that may be obtained by use of the code.
*/

$Args = array(
	'',
	'', 
	'', 
	'', 
	'', 
	'');

Void_Transaction($Args);

function Void_Transaction($Arguments)
{
	/* 
	*Required Parameter
	**Optional Parameter
	* / ** Optional but at least one is required
	$Arguments[5]
	$Arguments[0] = *Authentication Token
	$Arguments[1] = *Biller ID
	$Arguments[2] = *Merchant Profile ID
	$Arguments[3] = * / **Transaction ID
	$Arguments[4] = * / **Transaction History ID
	$Arguments[5] = **Comment
	This is not an exhaustive list of optional information that can be passed to the API See the API Documentation for additional parameters that may be passed
	*/

	//Change this URL to point to Production by chaning it to https://api.propay.com/... instead of https://xmltestapi.propay.com/....
	$url = "https://xmltestapi.propay.com/ProtectPay/VoidedTransactions/";
	$Auth_Header = "Basic " . base64_encode($Arguments[1] . ":" . $Arguments[0]);
	$HTTP_Verb = "PUT";
	$Payload = json_encode(array(
		"MerchantProfileId" => $Arguments[2],
		"originalTransactionID" => $Arguments[3],
		"TransactionHistoryId" => $Arguments[4],
		"Comment1" => $Arguments[5]));
	Submit_Request($url, $HTTP_Verb, $Auth_Header, $Payload);
}

function Submit_Request($url, $HTTP_Verb, $Auth_Header, $Payload)
{
	/* The HTTP header must include the SOAPAction */	
	$header = array(
	"Content-type: application/json; charset=utf-8",
	"Authorization: " . $Auth_Header,
	);
	
	$json = curl_init();
	curl_setopt($json, CURLOPT_URL, $url);
	curl_setopt($json, CURLOPT_CUSTOMREQUEST, $HTTP_Verb);
	curl_setopt($json, CURLOPT_HTTPHEADER, $header);
	if(isset($Payload))
	{
		curl_setopt($json, CURLOPT_POSTFIELDS, $Payload);
	}	
	curl_setopt($json, CURLOPT_TIMEOUT, 30);
	curl_setopt($json, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($json, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($json, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($json, CURLOPT_HTTPAUTH, CURLAUTH_ANY);

	$response = curl_exec($json);
	$err = curl_error($json);
	curl_close($json);
	/*Call Parse Function for the XML response*/
	Parse_Results($response);

}

function Parse_Results($api_response)
{
	$response = json_decode($api_response);
	if(isset($response->RequestResult->ResultCode))
	{
		$result_code = $response->RequestResult->ResultCode;
		$result_value = $response->RequestResult->ResultValue;
		$result = "Request Results:";
		$result .= "\nResult Code: " . $result_code;
		$result .= "\nResult Value: " . $result_value;
		if($result_code != '00' || $result_value == "FAILURE")
		{
			$result_message = $response->RequestResult->ResultMessage;
			$result .= "\nResult Message: " . $result_message;
			$result .= "\n";
			print_r($result);	
		}
		else
		{
			$authorization_code = $response->Transaction->AuthorizationCode;
			$currency_conversion_rate = $response->Transaction->CurrencyConversionRate;
			$currency_converted_amount = $response->Transaction->CurrencyConvertedAmount; 
			$currency_converted_currency_code =  $response->Transaction->CurrencyConvertedCurrencyCode;
			$transaction_history_id = $response->Transaction->TransactionHistoryId;
			$transaction_id = $response->Transaction->TransactionId;
			$transaction_result = $response->Transaction->TransactionResult;
			$result .= "\nTransaction Details:";
			$result .= "\nAuthorization Code: " . $authorization_code;
			$result .= "\nCurrency Conversion Rate: " . $currency_conversion_rate; 
			$result .= "\nCurrency Converted Amount: " . $currency_converted_amount;
			$result .= "\nConverted Currency Code: " . $currency_converted_currency_code;
			$result .= "\nTransaction Results: " ; 
			$result .= "\nTransaction History ID: " . $transaction_history_id;
			$result .= "\nTransaction ID: " . $transaction_id;
			$result .= "\nTransaction Result: " . $transaction_result ;
			$result .= "\n";		
			print_r($result);
		} 
	}
	else
	{
		print_r($api_response);
	}		
	
}

?>