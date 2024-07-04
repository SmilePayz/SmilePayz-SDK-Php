<?php
include "ConstantV2.php";

require 'Signature.php';

//url
$payinPathUrl = BASE_URL . "/v2.0/inquiry-balance";
$payinPathTestUrl = BASE_URL_SANDBOX . "/v2.0/inquiry-balance";

//get time
$currentTime = new DateTime('now', new DateTimeZone('UTC'));
$currentTime->setTimezone(new DateTimeZone('Asia/Bangkok'));
$timestamp = $currentTime->format('Y-m-d\TH:i:sP');

echo "timestamp=" . $timestamp . PHP_EOL;


//generate parameter $balanceInquiryReq
$balanceInquiryReq = array(
    'partnerReferenceNo' => "T_" . time(),
    'accountNo' => '21220030202403071031',
    'balanceTypes' => ['balance']
);
$signUtils = new Signature();

//json
$jsonString = $signUtils->minify($balanceInquiryReq);
echo "jsonString=" . $jsonString . PHP_EOL;

//build
$stringToSign = $timestamp . "|" . MERCHANT_SECRET . "|" . $jsonString;
echo "stringToSign=" . $stringToSign . PHP_EOL;

//********** begin signature ***************
$signatureValue = $signUtils->sha256RsaSignature($stringToSign, PRIVATE_KEY);
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
echo "request path =" . $payinPathUrl;

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