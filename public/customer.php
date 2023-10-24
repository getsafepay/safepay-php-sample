<?php

require_once '../vendor/autoload.php';
require_once '../secrets.php';

$safepay = new \Safepay\SafepayClient([
  'api_key' => $safepaySecreyKey,
  'api_base' => 'https://dev.api.getsafepay.com'
]);
header('Content-Type: application/json');

// Create a customer
// Error handling has been ommitted by default but you can 
// use the above try-catch pattern to handle errors
// $customer = $safepay->customer->create([
//   "first_name" => "Hassan",
//   "last_name" => "Zaidi",
//   "email" => "hzaidi@getsafepay.com",
//   "phone_number" => "+923331234567",
//   "country" => "PK",
//   // By default all customers are created as guests
//   // unless it is explicitly specified to be false
//   "is_guest" => true
// ]);

// $customer = $safepay->customer->retrieve("cus_dbbb1507-fc24-424a-8f8e-5ba25142c6b5");
// // This is the token that should be saved in your DB to link 
// // your customer with the one created on Safepay
// echo $customer->token . '\n';
// echo $customer->first_name . '\n';
// echo $customer->last_name . '\n';

// $customer = $safepay->customer->update($customer->token, [
//   "first_name" => "Ziyad",
//   "last_name" => "Parekh"
// ]);

// echo $customer->token . '\n';
// echo $customer->first_name . '\n';
// echo $customer->last_name . '\n';

// $response = $safepay->customer->delete($customer->token);
// echo $response->deleted;

try {
  $customer = $safepay->customer->retrieve("cus_c203e1b4-8a2d-43a5-bdb7-9b1132c52f26");
  print_r($customer);
} catch (\Safepay\Exception\InvalidRequestException $e) {
  echo 'Status is:' . $e->getHttpStatus() . '\n';
  echo 'Message is:' . $e->getError() . '\n';
} catch (Exception $e) {
  // Something else happened, completely unrelated to Safepay
  print_r($e);
}

try {
  $paymentMethods = $safepay->paymentMethod->all($customer->token);
  print_r($paymentMethods);
} catch (Exception $e) {
  // Something else happened, completely unrelated to Safepay
  print_r($e);
}

try {
  $list = $safepay->customer->all([
    'limit' => 20,
    'page' => 1
  ]);
  print_r($list->customers->offsetGet('0')->token);
} catch (Exception $e) {
  // Something else happened, completely unrelated to Safepay
  print_r($e);
}
