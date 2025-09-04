<?php

require_once 'vendor/autoload.php';

use App\Services\Payment\Factories\StripePaymentFactory;
use App\Services\Payment\Factories\PayPalPaymentFactory;

echo "=== Abstract Factory Pattern Demo ===\n\n";

// Test con Stripe
echo "1. Testing Stripe Payment Factory:\n";
$stripeFactory = new StripePaymentFactory('sk_test_123', 'whsec_123');

$stripeGateway = $stripeFactory->createGateway();
$stripeValidator = $stripeFactory->createValidator();
$stripeLogger = $stripeFactory->createLogger();

echo "Provider: " . $stripeFactory->getProviderName() . "\n";

// Dati per Stripe
$stripeData = [
    'card_token' => 'tok_test_123456',
    'currency' => 'USD',
    'amount' => 25.50,
    'customer' => [
        'email' => 'customer@example.com',
        'name' => 'John Doe'
    ]
];

// Validazione
$validation = $stripeValidator->validate($stripeData);
echo "Validation: " . ($validation->valid ? 'PASSED' : 'FAILED') . "\n";
if (!$validation->valid) {
    echo "Errors: " . implode(', ', $validation->errors) . "\n";
}

// Processamento pagamento
$result = $stripeGateway->processPayment(25.50, $stripeData);
echo "Payment: " . ($result->success ? 'SUCCESS' : 'FAILED') . "\n";
echo "Transaction ID: " . $result->transactionId . "\n";
echo "Message: " . $result->message . "\n\n";

// Test con PayPal
echo "2. Testing PayPal Payment Factory:\n";
$paypalFactory = new PayPalPaymentFactory('paypal_client_123', 'paypal_secret_123');

$paypalGateway = $paypalFactory->createGateway();
$paypalValidator = $paypalFactory->createValidator();
$paypalLogger = $paypalFactory->createLogger();

echo "Provider: " . $paypalFactory->getProviderName() . "\n";

// Dati per PayPal
$paypalData = [
    'paypal_order_id' => 'PAYID-123456789',
    'currency' => 'USD',
    'amount' => 25.50,
    'customer' => [
        'email' => 'customer@example.com'
    ]
];

// Validazione
$validation = $paypalValidator->validate($paypalData);
echo "Validation: " . ($validation->valid ? 'PASSED' : 'FAILED') . "\n";
if (!$validation->valid) {
    echo "Errors: " . implode(', ', $validation->errors) . "\n";
}

// Processamento pagamento
$result = $paypalGateway->processPayment(25.50, $paypalData);
echo "Payment: " . ($result->success ? 'SUCCESS' : 'FAILED') . "\n";
echo "Transaction ID: " . $result->transactionId . "\n";
echo "Message: " . $result->message . "\n\n";

// Test di incompatibilità
echo "3. Testing Cross-Provider Incompatibility:\n";
echo "Trying to use Stripe data with PayPal gateway:\n";

$result = $paypalGateway->processPayment(25.50, $stripeData);
echo "Result: " . ($result->success ? 'SUCCESS' : 'FAILED') . "\n";
echo "Message: " . $result->message . "\n\n";

echo "=== Demo Complete ===\n";

