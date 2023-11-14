<?php

require_once '../vendor/autoload.php';
require_once '../secrets.php';

/* 
  Instantiate the Safepay PHP SDK by passing in your 
  API Secret Key and the appropriate base URL to target
  Options for Base URL are:
  1. 'https://dev.api.getsafepay.com' (development environment for beta features)
  2. 'https://sandbox.api.getsafepay.com' (sandbox environment for stable features)
  3. 'https://api.getsafepay.com' (production/live environment)
*/

$safepay = new \Safepay\SafepayClient([
  'api_key' => $safepaySecreyKey,
  'api_base' => 'https://dev.api.getsafepay.com'
]);


header('Content-Type: application/json');

try {
  // You need to generate a tracker with mode 'instrument'
  // to tell Safepay that you wish to set up a tracker to
  // tokenize a customer's card
  $session = $safepay->order->setup([
    "merchant_api_key" => "sec_301fc73e-3b41-4b26-a1df-43f8e047a94a",
    "intent" => "CYBERSOURCE",
    "mode" => "instrument",
    "currency" => "PKR"
  ]);

  // You need to either create a customer or retreive the customer
  // from your backend so you have access to the customer ID
  $customer = $safepay->customer->create([
    "first_name" => "Hassan",
    "last_name" => "Zaidi",
    "email" => "hzaidi@getsafepay.com",
    "phone_number" => "+923331234567",
    "country" => "PK"
  ]);

  // You can optionally create an address object if you have
  // access to the customer's billing details
  $address = $safepay->address->create([
    // required
    "street1" => "3A-2 7th South Street",
    "city" => "Karachi",
    "country" => "PK",
    // optional
    "postal_code" => "75500",
    "state" => "Sindh"
  ]);
  // You need to create a Time Based Authentication token
  $tbt = $safepay->passport->create();

  // Finally, you can create the Checkout URL
  $checkoutURL = \Safepay\Checkout::constructURL([
    "environment" => "development", // one of "development", "sandbox" or "production"
    "tracker" => $session->tracker->token,
    "customer" => $customer->token,
    "tbt" => $tbt->token,
    //"address" => $address->token,
    "source" => "mobile" // important for rendering in a WebView
  ]);
  echo ($checkoutURL);
  return $checkoutURL;
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
