<?php

require_once '../vendor/autoload.php';
require_once '../secrets.php';

$safepay = new \Safepay\SafepayClient([
  'api_key' => $safepaySecreyKey,
  'api_base' => 'https://dev.api.getsafepay.com'
]);

\Safepay\Safepay::setApiBase("http://localhost");

header('Content-Type: application/json');

// Set up a tracker
try {
  $tracker = $safepay->order->setup([
    "merchant_api_key" => "sec_301fc73e-3b41-4b26-a1df-43f8e047a94a",
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

// Create a customer
// Error handling has been ommitted by default but you can 
// use the above try-catch pattern to handle errors
$customer = $safepay->customer->create([
  "first_name" => "Hassan",
  "last_name" => "Zaidi",
  "email" => "hzaidi@getsafepay.com",
  "phone_number" => "+923331234567",
  "country" => "PK",
  // By default all customers are created as guests
  // unless it is explicitly specified to be false
  "is_guest" => false
]);
// This is the token that should be saved in your DB to link 
// your customer with the one created on Safepay
echo $customer->token;

// Fetch a customer using their token
$token = $customer->token;
$customer = $safepay->customer->retrieve($token);

// Update a customer
// Note:: You cannot update the `is_guest` status for a customer
// after they have been created
$customer = $safepay->customer->update($token, [
  "first_name" => "Ziyad",
  "last_name" => "Parekh",
  "email" => "zparekh@getsafepay.com",
  "phone_number" => "+923331234567",
  "country" => "PK",
]);

echo $customer->first_name . ' ' . $customer->last_name;

// List all customer payment methods
$paymentMethods = \Safepay\Customer::allPaymentMethods($customer->token);

// Check if the customer has any saved payment methods
if (0 === $paymentMethods->count()) {
  throw new Exception("No payment methods", 1);
}

// Index into a specific payment method
$paymentMethod = $paymentMethods->wallet[0];

// Create a tracker with mode unscheduled_cof
$tracker = $safepay->order->setup([
  "merchant_api_key" => "sec_301fc73e-3b41-4b26-a1df-43f8e047a94a",
  "intent" => "CYBERSOURCE",
  "mode" => "unscheduled_cof",
  "user" => $customer->token,
  "currency" => "PKR",
  "amount" => 600000 // in the lowest denomination
]);

// Charge the tracker when you're ready
// charging trackers can work at the object level like so
$tracker = $tracker->charge([
  "payload" => [
    "payment_method" => [
      "tokenized_card" => [
        "token" => $paymentMethod
      ],
    ],
  ]
]);



// Or by using the safepay client (in case you don't have the tracker object on hand)
$tracker = $safepay->order->charge($tracker->token, [
  "payload" => [
    "payment_method" => [
      "tokenized_card" => [
        "token" => $paymentMethod
      ],
    ],
  ]
]);

$tracker = $tracker->metadata([
  "source" => "alfatah-app",
  "order_id" => "SHOPIFY-123456"
]);

// Delete a customer, if you'd like to 
// Note:: Once a customer is deleted, they cannot be used again
$response = $safepay->customer->delete($token);
$response = $safepay->paymentMethod->delete($token);
// this will be true if deletion was successful
echo $response->deleted;
