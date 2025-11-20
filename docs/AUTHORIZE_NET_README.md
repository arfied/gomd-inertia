# Authorize.net API Integration

This directory contains a JSON-based API integration for Authorize.net payment gateway. The implementation uses direct JSON requests and responses instead of relying on the Authorize.net SDK.

## Overview

The integration is organized into several service classes with a focus on clean architecture, dependency injection, and comprehensive error handling:

### Core Services
1. `AuthorizeNetApi.php` - Core API communication class that handles sending requests to Authorize.net and processing responses
2. `CustomerProfileService.php` - Manages customer profiles (create, get, update, delete)
3. `PaymentProfileService.php` - Manages payment profiles/credit cards (create, get, update, delete, validate)
4. `TransactionService.php` - Handles payment transactions (process, refund, void, get details)
5. `AuthorizeNetService.php` - Facade class that provides a simplified interface for common operations
6. `AchPaymentService.php` - Handles ACH (bank account) payment processing
7. `AchVerificationService.php` - Manages ACH verification through micro-deposits

### Helper Classes
- `Exceptions/` - Custom exception hierarchy for better error handling
- `Logging/StructuredLogger.php` - Consistent structured logging across all services
- `Responses/ResponseParser.php` - Centralized response parsing logic
- `Validation/PaymentValidator.php` - Input validation for payment data
- `TransactionRecorder.php` - Helper for recording transactions to the database

## Architecture & Design Improvements

### Dependency Injection
All services use constructor-based dependency injection, making them easily testable and loosely coupled:

```php
// Services are automatically resolved from the container
$service = app(AuthorizeNetService::class);

// Or manually inject dependencies
$api = new AuthorizeNetApi();
$customerService = new CustomerProfileService($api);
```

### Service Registration
Services are registered in `AppServiceProvider` as singletons for efficient resource usage:

```php
// app/Providers/AppServiceProvider.php
$this->app->singleton(AuthorizeNetApi::class);
$this->app->singleton(AuthorizeNetService::class);
// ... other services
```

### Response Parsing
The `ResponseParser` class centralizes response parsing logic, reducing code duplication:

```php
use App\Services\AuthorizeNet\Responses\ResponseParser;

$customerId = ResponseParser::extractCustomerProfileId($response);
$paymentProfileId = ResponseParser::extractPaymentProfileId($response);
$transactionId = ResponseParser::extractTransactionId($response);
```

### Transaction Recording
The `TransactionRecorder` helper class handles database transaction recording with consistent error handling:

```php
use App\Services\AuthorizeNet\TransactionRecorder;

TransactionRecorder::recordTransaction(
    userId: $user->id,
    amount: 99.99,
    transactionId: 'trans_123',
    paymentMethod: 'credit_card',
    status: 'success',
    subscriptionId: $subscription->id
);
```

## Configuration

The integration uses the following configuration values from `config/services.php`:

```php
// config/services.php
return [
    // ...
    'authorize_net' => [
        'login_id' => env('AUTHORIZE_NET_LOGIN_ID'),
        'transaction_key' => env('AUTHORIZE_NET_TRANSACTION_KEY'),
        'sandbox' => env('AUTHORIZE_NET_SANDBOX', true),
    ],
    // ...
];
```

Make sure to add these values to your `.env` file:

```
AUTHORIZE_NET_LOGIN_ID=your_login_id
AUTHORIZE_NET_TRANSACTION_KEY=your_transaction_key
AUTHORIZE_NET_SANDBOX=true  # Set to false for production
```

## Recommended Payment Flow

The recommended approach for processing payments is to use **customer profiles and payment profiles**:

1. **Create a customer profile** - Store customer information on Authorize.net
2. **Create a payment profile** - Store the credit card or bank account
3. **Process transactions** - Use the stored profile IDs for all transactions

This approach is recommended because:
- ✅ Card details are stored securely on Authorize.net servers
- ✅ No need to send raw card data with every transaction
- ✅ Complies with PCI DSS requirements
- ✅ Enables recurring/subscription payments
- ✅ Allows refunds without card details

## Usage Examples

### Customer Profile Management

```php
use App\Services\AuthorizeNet\AuthorizeNetService;

// Create a new instance of the service
$authorizeNet = new AuthorizeNetService();

// Get or create a customer profile
$profileResult = $authorizeNet->getOrCreateCustomerProfile($user);

if ($profileResult['success']) {
    $customerProfileId = $profileResult['profile_id'];
    // Store or use the customer profile ID
} else {
    // Handle error
    $errorMessage = $profileResult['message'];
}
```

### Adding a Credit Card

```php
use App\Services\AuthorizeNet\AuthorizeNetService;

// Create a new instance of the service
$authorizeNet = new AuthorizeNetService();

// Add a credit card
$cardResult = $authorizeNet->addCreditCard(
    $user,
    '4111111111111111', // Card number
    '12',               // Expiration month
    '2025',             // Expiration year
    '123'               // CVV
);

if ($cardResult['success']) {
    // Card added successfully
    $paymentProfileId = $cardResult['payment_profile_id'];
    $lastFour = $cardResult['last_four'];
    $brand = $cardResult['brand'];

    // Store the payment profile ID and card details in your database
} else {
    // Handle error
    $errorMessage = $cardResult['message'];
}
```

### Processing a Transaction

```php
use App\Services\AuthorizeNet\AuthorizeNetService;

// Create a new instance of the service
$authorizeNet = new AuthorizeNetService();

// Process a transaction
$transactionResult = $authorizeNet->processTransaction(
    99.99,                  // Amount
    $customerProfileId,     // Customer profile ID
    $paymentProfileId,      // Payment profile ID
    'Monthly subscription'  // Description
);

if ($transactionResult['success']) {
    // Transaction processed successfully
    $transactionId = $transactionResult['transaction_id'];
    $authCode = $transactionResult['auth_code'];

    // Store the transaction details in your database
} else {
    // Handle error
    $errorMessage = $transactionResult['message'];
}
```

### Refunding a Transaction

```php
use App\Services\AuthorizeNet\AuthorizeNetService;

// Create a new instance of the service
$authorizeNet = new AuthorizeNetService();

// Refund a transaction
$refundResult = $authorizeNet->refundTransaction(
    $transactionId,     // Original transaction ID
    99.99,              // Amount to refund
    '1111',             // Last 4 digits of card
    '2025-12'           // Expiration date (YYYY-MM)
);

if ($refundResult['success']) {
    // Refund processed successfully
    $refundTransactionId = $refundResult['transaction_id'];

    // Store the refund details in your database
} else {
    // Handle error
    $errorMessage = $refundResult['message'];
}
```

### Voiding a Transaction

```php
use App\Services\AuthorizeNet\AuthorizeNetService;

// Create a new instance of the service
$authorizeNet = new AuthorizeNetService();

// Void a transaction
$voidResult = $authorizeNet->voidTransaction($transactionId);

if ($voidResult['success']) {
    // Transaction voided successfully
    $voidTransactionId = $voidResult['transaction_id'];

    // Update the transaction status in your database
} else {
    // Handle error
    $errorMessage = $voidResult['message'];
}
```

## Advanced Usage

For more advanced use cases, you can use the individual service classes directly:

```php
use App\Services\AuthorizeNet\AuthorizeNetApi;
use App\Services\AuthorizeNet\CustomerProfileService;
use App\Services\AuthorizeNet\PaymentProfileService;
use App\Services\AuthorizeNet\TransactionService;

// Create the API instance
$api = new AuthorizeNetApi();

// Create service instances
$customerProfileService = new CustomerProfileService($api);
$paymentProfileService = new PaymentProfileService($api);
$transactionService = new TransactionService($api);

// Use the services directly
$profileId = $customerProfileService->createCustomerProfile($user);
$paymentProfileId = $paymentProfileService->createPaymentProfile($profileId, $cardNumber, $expirationMonth, $expirationYear, $cvv, $user);
$transactionResult = $transactionService->processProfileTransaction($amount, $profileId, $paymentProfileId);
```

## Error Handling

The service uses a custom exception hierarchy for better error handling:

### Exception Types

- **`AuthorizeNetException`** - Base exception with error code and details support
- **`ApiException`** - For API-specific errors with helper methods:
  - `isDuplicateProfile()` - Check if error is duplicate profile
  - `isProfileNotFound()` - Check if profile doesn't exist
  - `isValidationError()` - Check if validation failed
- **`ValidationException`** - For input validation failures
- **`TransactionException`** - For transaction-specific failures

### Usage

```php
use App\Services\AuthorizeNet\Exceptions\ApiException;
use App\Services\AuthorizeNet\Exceptions\ValidationException;

try {
    $result = $authorizeNet->processTransaction($amount, $customerProfileId, $paymentProfileId);
} catch (ValidationException $e) {
    // Handle validation errors
    Log::warning('Validation failed: ' . $e->getMessage());
    return back()->withErrors($e->getDetails());
} catch (ApiException $e) {
    if ($e->isDuplicateProfile()) {
        // Handle duplicate profile
    } elseif ($e->isProfileNotFound()) {
        // Handle profile not found
    }
    Log::error('API error: ' . $e->getErrorCode() . ' - ' . $e->getMessage());
} catch (\Exception $e) {
    // Handle other errors
    Log::error('Unexpected error: ' . $e->getMessage());
}
```

## Input Validation

The `PaymentValidator` class provides comprehensive validation for payment data:

```php
use App\Services\AuthorizeNet\Validation\PaymentValidator;

// Validate credit card
if (!PaymentValidator::validateCreditCard('4111111111111111')) {
    throw new ValidationException('Invalid credit card number');
}

// Validate expiration date
if (!PaymentValidator::validateExpirationDate('12', '2025')) {
    throw new ValidationException('Invalid expiration date');
}

// Validate CVV
if (!PaymentValidator::validateCvv('123')) {
    throw new ValidationException('Invalid CVV');
}

// Validate routing number (US)
if (!PaymentValidator::validateRoutingNumber('021000021')) {
    throw new ValidationException('Invalid routing number');
}

// Mask sensitive data
$maskedCard = PaymentValidator::maskCardNumber('4111111111111111'); // 4111****1111
```

## Logging

All API requests and responses are logged using the `StructuredLogger` class for consistent, structured logging:

```php
use App\Services\AuthorizeNet\Logging\StructuredLogger;

// Logs are automatically recorded for:
// - API requests and responses
// - Customer profile operations
// - Payment profile operations
// - Transaction operations
// - Validation errors
```

Sensitive data (credit card numbers, transaction keys) is automatically masked in logs for security.

## Testing

Comprehensive unit tests are included for all components:

### Test Files
- `tests/Unit/PaymentValidatorTest.php` - 14 tests for payment validation
- `tests/Unit/ResponseParserTest.php` - 12 tests for response parsing
- `tests/Unit/AuthorizeNetExceptionsTest.php` - 11 tests for exception handling

### Running Tests

```bash
# Run all AuthorizeNet tests
php artisan test tests/Unit/PaymentValidatorTest.php tests/Unit/ResponseParserTest.php tests/Unit/AuthorizeNetExceptionsTest.php

# Run specific test file
php artisan test tests/Unit/PaymentValidatorTest.php

# Run with coverage
php artisan test tests/Unit/ --coverage
```

### Test Coverage
- ✅ 37 tests passing
- ✅ 76 assertions
- ✅ Payment validation (Luhn algorithm, expiration dates, CVV, routing numbers)
- ✅ Response parsing (profile IDs, transaction IDs, error messages)
- ✅ Exception handling and error codes

## Best Practices

1. **Always use dependency injection** - Services should be injected rather than instantiated directly
2. **Validate input** - Use `PaymentValidator` to validate payment data before sending to API
3. **Handle exceptions** - Catch specific exception types for better error handling
4. **Log operations** - Structured logging is automatic, but you can add custom context
5. **Mask sensitive data** - Never log raw credit card numbers or transaction keys
6. **Use the facade** - For simple operations, use `AuthorizeNetService` instead of individual services
7. **Test thoroughly** - Write tests for any custom payment logic
