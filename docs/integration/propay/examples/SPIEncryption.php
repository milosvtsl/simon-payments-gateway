<?php

/*
ProPay provides the following code “AS IS.” 
ProPay makes no warranties and ProPay disclaims all warranties and conditions, express, implied or statutory, 
including without limitation the implied warranties of title, non-infringement, merchantability, and fitness for a particular purpose. 
ProPay does not warrant that the code will be uninterrupted or error free, 
nor does ProPay make any warranty as to the performance or any results that may be obtained by use of the code.
*/

$tempToken = '1f25d31c-e8fe-4d68-be73-f7b439bfa0a329e90de6-4e93-4374-8633-22cef77467f5';
$keyValuePair = "This string was AES-128 / CBC encrypted.";

function spiEncrypt($tempToken, $keyValuePair)
{
	$key = hash('MD5', utf8_encode($tempToken), true);
	$iv = $key;
	$settingsCipher = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, padData($keyValuePair), MCRYPT_MODE_CBC, $iv);
	return base64_encode($settingsCipher);
}

function spiDecrypt($tempToken, $responseCipher)
{
	$key = hash('MD5', utf8_encode($tempToken), true);
	$iv = $key;
	$spiResponse = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, base64_decode($responseCipher), MCRYPT_MODE_CBC, $iv);
	return unPadData($spiResponse);
}

function padData($data)
{
	$padding = 16 - (strlen($data) % 16);
  	$data .= str_repeat(chr($padding), $padding); 
  	return $data;
} 

function unPadData($data)
{
	$padding = ord($data[strlen($data) - 1]);
  	return substr($data, 0, -$padding);
}

echo "Ecnrypting Key Value Pair: " . $keyValuePair . "\n";
echo "Using this tempToken: " . $tempToken . "\n";
$settingsCipher = spiEncrypt($tempToken, $keyValuePair);
echo "Settings Cipher: " . $settingsCipher;
echo "\nSimulating API Response by setting responseCipher equal to settingsCipher\n";
$responseCipher = $settingsCipher;
$spiResponse = spiDecrypt($tempToken, $responseCipher);
echo "Unencrypted Response: " . $spiResponse;

?>