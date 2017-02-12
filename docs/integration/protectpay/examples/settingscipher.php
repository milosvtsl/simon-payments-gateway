<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
    "http://www.w3.org/TR/html4/loose.dtd">

<html>
<head>
    <title>PHP Mcrypt</title>
</head>
<body>
<h1>PHP Mcrypt</h1>
<?php
/* $myclass = new MyClass();

$tempToken = "05710678";
$settingsCipher = $myclass ->encrypt("a=1&b=2&c=3", $tempToken);
echo (" =[".$settingsCipher."]\n");
$result = $proPay->decrypt($settingsCipher,$tempToken);
echo ("$result=[".$result."]\n"); */

$tempToken = "05710678";
echo "Temp Token: ".$tempToken."<br><br>";
$settingsCipher = encrypt("a=1&b=2&c=3", $tempToken);
echo ("Encrypt = [".$settingsCipher."]\n<br>");
$result = decrypt($settingsCipher,$tempToken);
echo ("Decrypt = [".$result."]\n<br><br>");

$settingsCipher = encrypt2("a=1&b=2&c=3", $tempToken);
echo ("Encrypt2 = [".$settingsCipher."]\n<br>");
$result = decrypt2($settingsCipher,$tempToken);
echo ("Decrypt2 = [".$result."]\n<br><br>");


function encrypt($text, $tempToken ){
    $encodedTempToken = utf8_encode($tempToken);
    $md5Hash = md5($encodedTempToken, TRUE);
    $encodedNameValueString = utf8_encode($text);

    $blocksize = mcrypt_get_block_size('rijndael_128', 'cbc');
    $pad = $blocksize - (strlen($encodedNameValueString) % $blocksize);
    $encodedNameValueString = $encodedNameValueString . str_repeat(chr($pad), $pad);

    $aes128 = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $md5Hash, $encodedNameValueString, MCRYPT_MODE_CBC, $md5Hash);

    return base64_encode($aes128);
    // return $aes128;
}

//this is what i am using now that does not work. i think i am close

function decrypt($text, $tempToken ){
    $encodedTempToken = utf8_encode($tempToken);
    $md5Hash = md5($encodedTempToken, TRUE);
    $text2 = base64_decode($text);
    $str = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $md5Hash, $text2, MCRYPT_MODE_CBC, $md5Hash);
    //$blocksize = mcrypt_get_block_size('rijndael_128', 'cbc');
    //$pad = $blocksize - (strlen($str) % $blocksize);
    //$str = $str . str_repeat(chr($pad), $pad);
    //return $str;
    $block = mcrypt_get_block_size('rijndael_128', 'cbc');
    $pad = ord($str[($len = strlen($str)) - 1]);
    return substr($str, 0, strlen($str) - $pad);

}

function encrypt2($str, $key)
{
    $block = mcrypt_get_block_size('des', 'ecb');
    $pad = $block - (strlen($str) % $block);
    $str .= str_repeat(chr($pad), $pad);

    return mcrypt_encrypt(MCRYPT_DES, $key, $str, MCRYPT_MODE_ECB);
}

function decrypt2($str, $key)
{
    $str = mcrypt_decrypt(MCRYPT_DES, $key, $str, MCRYPT_MODE_ECB);

    $block = mcrypt_get_block_size('des', 'ecb');
    $pad = ord($str[($len = strlen($str)) - 1]);
    return substr($str, 0, strlen($str) - $pad);
}

?>

</body>
</html>