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
	'');

Get_Temp_Token($Args);

function Get_Temp_Token($Arguments)
{
	/*
	*Required Parameter
	**Optional Parameter 
	$Arguments[4]
	$Arguments[0] = *Authentication Token
	$Arguments[1] = *Biller ID
	$Arguments[2] = Payer ID You must pass a payer ID to the URL and NOT a name. You may consider a universal payer for temp tokens
	$Arguments[3] = *Time To Live
	This is not an exhaustive list of optional information that can be passed to the API See the API Documentation for additional parameters that may be passed
	*/

	//Change this URL to point to Production by chaning it to https://api.propay.com/... instead of https://xmltestapi.propay.com/....
	$url = "https://xmltestapi.propay.com/ProtectPay/Payers/" . $Arguments[2] . "/TempTokens/?durationSeconds=" . $Arguments[3];
	$Auth_Header = "Basic " . base64_encode($Arguments[1] . ":" . $Arguments[0]);
	$HTTP_Verb = "GET";
	Submit_Request($url, $HTTP_Verb, $Auth_Header, null);
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
			$credential_id = $response->CredentialId;
			$payer_id = $response->PayerId;
			$temp_token = $response->TempToken;
			$result .= "\nTransaction Results:";
			$result .= "\nCredential ID: " . $credential_id;
			$result .= "\nPayer ID: " . $payer_id;
			$result .= "\nTemp Token: " . $temp_token;
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