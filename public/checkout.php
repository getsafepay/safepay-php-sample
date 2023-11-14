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

/* 
  A payment usually requires a customer. Safepay allows both user and guest
  checkout flows and gives the developer the flexibility to control which 
  flow to follow. 

  If your app has user accounts, we recommend creating a Safepay Customer and 
  linking the Safepay Customer `token` with your own user. To create a Safepay
  Customer call the `create  function on the Customer service and pass in the 
  required parameters. To see the full list of parameters required while creating
  a customer, please consult our API documentation available here:
  https://apidocs.getsafepay.com/#67d144ef-fad9-43ea-8595-6f710f203305
*/
try {
  $customer = $safepay->customer->create([
    "first_name" => "Hassan",
    "last_name" => "Zaidi",
    "email" => "hzaidi@getsafepay.com",
    "phone_number" => "+923331234567",
    "country" => "PK"
  ]);

  echo $customer->token;
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

/* 
  If you have already created a customer before and have associated 
  their `token` with your customer, you can perform the following 
  actions. Note that error handling has been ommitted for brevity.
  1. Retrieve
  2. Update
  3. Delete
*/
// Retrieve the customer using their token
$customer = $safepay->customer->retrieve("cus_dbbb1507-fc24-424a-8f8e-5ba25142c6b5");
// Update the customer
$customer = $safepay->customer->update($customer->token, [
  "first_name" => "Ziyad",
  "last_name" => "Parekh"
]);
// Delete the customer once you no longer require it
$response = $safepay->customer->delete($customer->token);

/* 
  Customers can also have saved payment methods that can be used
  to make purchases. Saving a payment method to a customer's wallet
  allows them to make payments quickly without having to re-enter 
  their information each time. To enable your customer's to create 
  payment methods, please refer to our embedded checkout integration 
  guide. After a customer has saved payment methods to their wallet,
  the SDK allows you to perform certain actions with them. Note that 
  error handling has been ommitted for brevity.
  1. Retrieve a single payment method
  2. List all payment methods
  3. Delete a payment method
  To read more about Payment Methods, please checkout our API documentation 
  over here: https://apidocs.getsafepay.com/#32303749-a3cc-446d-8866-c17915d11f6b
*/
// List all payment methods
$paymentMethods = $safepay->payment_method->all($customer->token);
// Once you have a collection of paymentMethods, you can also index
// into to find a specific payment method
if ($paymentMethods->count() > 0) {
  $paymentMethod = $paymentMethods->wallet[0];
}

// Otherwise, if you have the `token` of a payment method,
// you can use the `retreive` method to fetch it
$paymentMethod = $safepay->payment_method->retrieve($customer->token, $paymentMethod->token);

// If your customer wants to delete their saved payment method, you can 
// call the `delete` method to remove it from their wallet
$response = $safepay->payment_method->delete($customer->token, $paymentMethod->token);

/* 
  Now that you have all the necessary resources available, its time to create a transaction.
  A transaction, at the very basic level, involves a customer, their payment method and the 
  amount (in a supported currency) that they will pay. To initiate a transaction, you must 
  first `setup` a payment session.
 
  To Create an Order session call the `setup` function
  on the Order service and pass in the required parameters.
  To see the full list of parameters to initialize different
  payment modes please consult our API documentation available 
  here: https://apidocs.getsafepay.com/#7fcfa13f-bf41-4b86-80c6-5ca178d80baa
*/

try {
  $session = $safepay->order->setup([
    "user" => $customer->token,
    "merchant_api_key" => "sec_301fc73e-3b41-4b26-a1df-43f8e047a94a",
    "intent" => "CYBERSOURCE",
    "mode" => "unscheduled_cof",
    "currency" => "PKR",
    "amount" => 600000 // in the lowest denomination
  ]);

  echo $session->tracker->token;
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

/* 
  You can optionally associate a third-party order ID with 
  your payment session for easy reconcilliation. This can be 
  done prior to the payment or after it. Once again, error handling
  has been ommitted for brevity
*/
$session = $safepay->order->metadata($session->tracker->token, [
  "data" => [
    "source" => "alfatah-app",
    "order_id" => "SHOPIFY-123456"
  ]
]);

/* 
  Once you're ready to charge the customer, call the `charge` method
  on the Order service to capture the transaction. To charge the saved 
  payment method, you must pass in the token of the saved payment method
  selected by the customer
*/
$tracker = $safepay->order->charge($tracker->token, [
  "payload" => [
    "payment_method" => [
      "tokenized_card" => [
        "token" => $paymentMethod->token
      ],
    ],
  ]
]);

// Refunds & Voids
/* 
  Any transaction less than 24 hours old can be Voided to avoid settlement.
  To Void a transaction, you can call the `void` method on the order service
*/
$session = $safepay->order->void($session->tracker->token);

/* 
  However, if the transaction has already been settled, you must call the 
  `refund` method to start the reversal process.
*/
$session = $safepay->order->refund($session->tracker->token);
