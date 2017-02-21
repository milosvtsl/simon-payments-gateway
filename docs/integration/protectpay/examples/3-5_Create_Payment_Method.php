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
	'',
	'',
	'',
	'',
	'',
	'',
	'',
	'',
	'',
	'',
	'',
	'',
	'',
	'');

Create_Payment_Method($Args);

function Create_Payment_Method($Arguments)
{
	 /* 
	*Required Parameter
	**Optional Parameter
	$arguments[18]
	$arguments[0] = *Authentication Token
	$arguments[1] = *Biller ID
	$arguments[2] = **Account Name
	$arguments[3] = *Account Number
	$arguments[4] = **Address Line 1
	$arguments[5] = **Address Line 2
	$arguments[6] = **City
	$arguments[7] = **Country
	$arguments[8] = **Email
	$arguments[9] = **State
	$arguments[10] = **Phone Number
	$arguments[11] = **Postal Code
	$arguments[12] = *Description
	$arguments[13] = *Expiration Month
	$arguments[14] = *Expiration Year
	$arguments[15] = *Payer Account Id
	$arguments[16] = *Payment Method Type (Visa, MasterCard, etc)
	$arguments[17] = **Payment Priority
	$arguments[18] = **Protected
	This is not an exhaustive list of optional information that can be passed to the API See the API Documentation for additional parameters that may be passed
	*/

	//Change this URL to point to Production by chaning it to https://api.propay.com/... instead of https://xmltestapi.propay.com/....
	$url = "https://xmltestapi.propay.com/ProtectPay/Payers/" . $Arguments[15] . "/PaymentMethods/";
	$Auth_Header = "Basic " . base64_encode($Arguments[1] . ":" . $Arguments[0]);
	$HTTP_Verb = "PUT";
	$Payload = json_encode(array(
		"AccountName" => $Arguments[2],
		"AccountNumber" => $Arguments[3],
		"Address1" => $Arguments[4],
		"Address2" => $Arguments[5],
		"City" => $Arguments[6],
		"Country" => $Arguments[7],
		"Email" => $Arguments[8],
		"State" => $Arguments[9],
		"TelephoneNumber" => $Arguments[10],
		"ZipCode" => $Arguments[11],
		"Description" => $Arguments[12],
		"ExpirationDate" => $Arguments[13] . $Arguments[14],
		"PayerAccountId" => $Arguments[15],
		"PaymentMethodType" => $Arguments[16],
		"Priority" => $Arguments[17],
		"Protected" => $Arguments[18]));
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
			$payment_method = $response->PaymentMethodId;
			$result .= "\nTransaction Results:";
			$result .= "\nPayment Method ID: " . $payment_method;
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