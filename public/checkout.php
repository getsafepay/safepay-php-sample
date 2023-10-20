<?php

require_once '../vendor/autoload.php';
require_once '../secrets.php';

$safepay = new \Safepay\SafepayClient([
  'api_key' => $safepaySecreyKey,
  'api_base' => 'https://sandbox.api.getsafepay.com'
]);
header('Content-Type: application/json');

$YOUR_DOMAIN = 'http://localhost:4242';

try {
  $tracker = $safepay->order->setup([
    "merchant_api_key" => "sec_faed47eb-0044-4968-b7e9-2fb7d7853eb9",
    "intent" => "CYBERSOURCE",
    "mode" => "payment",
    "currency" => "PKR",
    "amount" => 600000 // in the lowest denomination
  ]);

  echo $tracker->tracker->token;
} catch (\Safepay\Exception\InvalidRequestException $e) {
  echo 'Status is:' . $e->getHttpStatus() . '\n';
  echo 'Message is:' . $e->getError() . '\n';
} catch (\Safepay\Exception\AuthenticationException $e) {
  echo 'Status is:' . $e->getHttpStatus() . '\n';
  echo 'Message is:' . $e->getError() . '\n';
} catch (\Safepay\Exception\UnknownApiErrorException $e) {
  echo 'Status is:' . $e->getHttpStatus() . '\n';
  echo 'Message is:' . $e->getError() . '\n';
} catch (Exception $e) {
  // Something else happened, completely unrelated to Safepay
  print_r($e);
}
