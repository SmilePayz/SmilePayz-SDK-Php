<?php
require 'Signature.php';
include "ConstantV2.php";


//url
//production
$requestPath = BASE_URL . "/v2.0/inquiry-status";
$merchantId = MERCHANT_ID;
$merchantSecret = MERCHANT_SECRET;

//sandbox
//$requestPath = BASE_URL_SANDBOX . "/v2.0/inquiry-status";
//$merchantId = MERCHANT_ID_SANDBOX;
//$merchantSecret = MERCHANT_SECRET_SANDBOX;


//get time
$currentTime = new DateTime('now', new DateTimeZone('UTC'));
$currentTime->setTimezone(new DateTimeZone('Asia/Bangkok'));
$timestamp = $currentTime->format('Y-m-d\TH:i:sP');

echo "timestamp=" . $timestamp . PHP_EOL;

//generate parameter $inquiryOrderStatusReq
$inquiryOrderStatusReq = array(
    'tradeType' => TRADE_TYPE_PAY_OUT,
    'tradeNo' => '112200182402261848252600',
    'orderNo' => 'D_1708948105016'
);
$signUtils = new Signature();

//json
$jsonString = $signUtils->minify($inquiryOrderStatusReq);
echo "jsonString=" . $jsonString . PHP_EOL;

//build
$stringToSign =  $timestamp . "|" . $merchantSecret . "|" . $jsonString;
echo "stringToSign=" . $stringToSign . PHP_EOL;

//********** begin signature ***************
$signatureValue =  $signUtils->sha256RsaSignature($stringToSign,PRIVATE_KEY);
echo "signatureValue=" . $signatureValue . PHP_EOL;
echo "request path=" . $requestPath . PHP_EOL;
//********** end signature ***************


//********** begin post ***************

// Create a cURL handle
$ch = curl_init();

// Set cURL options
curl_setopt($ch, CURLOPT_URL, $requestPath);  // API URL
curl_setopt($ch, CURLOPT_POST, true);  // POST
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonString);  // JSON Data
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'X-TIMESTAMP: ' . $timestamp,
    'X-SIGNATURE: ' . $signatureValue,
    'X-PARTNER-ID: ' . $merchantId,
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

echo "=====>  end " . PHP_EOL;