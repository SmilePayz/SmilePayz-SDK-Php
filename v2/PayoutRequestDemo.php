<?php
require 'Signature.php';
include "ConstantV2.php";

//url
$payinPathUrl = "https://gateway-test.smilepayz.com/v2.0/disbursement/pay-out";
$payinPathTestUrl = "https://sandbox-gateway-test.smilepayz.com/v2.0/disbursement/pay-out";

echo "=====> step4 : TheSmilePay Payout" . PHP_EOL;

//get time
$currentTime = new DateTime('now', new DateTimeZone('UTC'));
$currentTime->setTimezone(new DateTimeZone('Asia/Bangkok'));
$timestamp = $currentTime->format('Y-m-d\TH:i:sP');

$signUtils = new Signature();

//generate parameter
// just for case. length less than 32
$merchantOrderNo = MERCHANT_ID . $signUtils->uuidv4();

$purpose = "Purpose For Disbursement from PHP SDK";
$paymentMethod = "YES";
$cashAccount = "17385238451";


//$moneyReq
$moneyReq = array(
    'currency' => CURRENCY_INR,
    'amount' => 200
);

//$merchantReq
$merchantReq = array(
    'merchantId' => MERCHANT_ID
);

$additionalParam = array(
    'ifscCode' => "YESB0000097"
);
//$payinReq
$payinReq = array(
    'orderNo' => substr($merchantOrderNo,0,32),
    'purpose' => $purpose,
    'money' => $moneyReq,
    'additionalParam' => $additionalParam,
    'merchant' => $merchantReq,
    'paymentMethod' => $paymentMethod,
    'cashAccount' => $cashAccount,
    'area' => INDIA_CODE,

);

//json
$jsonString = json_encode($payinReq);
echo "jsonString=" . $jsonString . PHP_EOL;

//build
$stringToSign =  $timestamp . "|" . MERCHANT_SECRET . "|" . $jsonString;
echo "stringToSign=" . $stringToSign . PHP_EOL;


//********** begin signature ***************
$signatureValue =  $signUtils->sha256RsaSignature($stringToSign,PRIVATE_KEY);
echo "signatureValue=" . $signatureValue . PHP_EOL;
//********** end signature ***************

//********** begin post ***************

// Create a cURL handle
$ch = curl_init();

// Set cURL options
curl_setopt($ch, CURLOPT_URL, $payinPathUrl);  // API URL
curl_setopt($ch, CURLOPT_POST, true);  // POST
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonString);  // JSON Data
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'X-TIMESTAMP: ' . $timestamp,
    'X-SIGNATURE: ' . $signatureValue,
    'X-PARTNER-ID: ' . MERCHANT_ID,
));

// Execute the request and get the response
$response = curl_exec($ch);
echo $response . PHP_EOL;

// Check for errors
if ($response === false) {
    echo 'cURL error: ' . curl_error($ch);
} else {
    // Process response result
    echo PHP_EOL;
    echo "response=" . $response . PHP_EOL;
}

// Close cURL handle
curl_close($ch);

echo "=====> Now. You get the AccessToken. So you can access other TheSmilePay API" . PHP_EOL;