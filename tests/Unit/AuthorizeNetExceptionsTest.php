<?php

namespace Tests\Unit;

use App\Services\AuthorizeNet\Exceptions\ApiException;
use App\Services\AuthorizeNet\Exceptions\AuthorizeNetException;
use App\Services\AuthorizeNet\Exceptions\TransactionException;
use App\Services\AuthorizeNet\Exceptions\ValidationException;
use PHPUnit\Framework\TestCase;

class AuthorizeNetExceptionsTest extends TestCase
{
    /**
     * Test AuthorizeNetException with error code and details
     */
    public function test_authorize_net_exception_with_error_code()
    {
        $exception = new AuthorizeNetException(
            'Test error',
            'E00001',
            ['field' => 'value']
        );

        $this->assertEquals('Test error', $exception->getMessage());
        $this->assertEquals('E00001', $exception->getErrorCode());
        $this->assertEquals(['field' => 'value'], $exception->getDetails());
    }

    /**
     * Test AuthorizeNetException withDetails method
     */
    public function test_authorize_net_exception_with_details()
    {
        $exception = new AuthorizeNetException('Test error', 'E00001');
        $exception->withDetails(['key' => 'value']);

        $this->assertEquals(['key' => 'value'], $exception->getDetails());
    }

    /**
     * Test ApiException constants
     */
    public function test_api_exception_constants()
    {
        $this->assertEquals('E00039', ApiException::DUPLICATE_PROFILE);
        $this->assertEquals('E00040', ApiException::PROFILE_NOT_FOUND);
        $this->assertEquals('E00001', ApiException::INVALID_RESPONSE);
        $this->assertEquals('E00114', ApiException::VALIDATION_ERROR);
    }

    /**
     * Test ApiException isDuplicateProfile method
     */
    public function test_api_exception_is_duplicate_profile()
    {
        $exception = new ApiException('Duplicate profile', ApiException::DUPLICATE_PROFILE);

        $this->assertTrue($exception->isDuplicateProfile());
        $this->assertFalse($exception->isProfileNotFound());
    }

    /**
     * Test ApiException isProfileNotFound method
     */
    public function test_api_exception_is_profile_not_found()
    {
        $exception = new ApiException('Profile not found', ApiException::PROFILE_NOT_FOUND);

        $this->assertTrue($exception->isProfileNotFound());
        $this->assertFalse($exception->isDuplicateProfile());
    }

    /**
     * Test ApiException isValidationError method
     */
    public function test_api_exception_is_validation_error()
    {
        $exception = new ApiException('Validation error', ApiException::VALIDATION_ERROR);

        $this->assertTrue($exception->isValidationError());
    }

    /**
     * Test ApiException fromApiResponse method
     */
    public function test_api_exception_from_api_response()
    {
        $response = [
            'messages' => [
                'resultCode' => 'Error',
                'message' => [
                    ['code' => 'E00001', 'text' => 'Invalid request']
                ]
            ]
        ];

        $exception = ApiException::fromApiResponse($response);

        $this->assertInstanceOf(ApiException::class, $exception);
        $this->assertEquals('E00001', $exception->getErrorCode());
    }

    /**
     * Test ValidationException
     */
    public function test_validation_exception()
    {
        $exception = new ValidationException('Validation failed');
        $exception->addError('field1', 'Invalid value');
        $exception->addError('field2', 'Required field');

        $errors = $exception->getErrors();
        $this->assertCount(2, $errors);
        $this->assertEquals('Invalid value', $errors['field1']);
        $this->assertEquals('Required field', $errors['field2']);
    }

    /**
     * Test TransactionException
     */
    public function test_transaction_exception()
    {
        $response = [
            'transactionResponse' => [
                'responseCode' => '2',
                'errors' => [
                    ['errorText' => 'Transaction declined']
                ]
            ]
        ];

        $exception = TransactionException::fromTransactionResponse($response);

        $this->assertInstanceOf(TransactionException::class, $exception);
        $this->assertEquals($response, $exception->getTransactionResponse());
    }

    /**
     * Test TransactionException getTransactionResponse method
     */
    public function test_transaction_exception_get_response()
    {
        $response = ['test' => 'data'];
        $exception = TransactionException::fromTransactionResponse($response);

        // Verify response is stored
        $this->assertIsArray($exception->getTransactionResponse());
        $this->assertEquals($response, $exception->getTransactionResponse());
    }

    /**
     * Test exception inheritance
     */
    public function test_exception_inheritance()
    {
        $apiException = new ApiException('API error', 'E00001');
        $this->assertInstanceOf(AuthorizeNetException::class, $apiException);
        $this->assertInstanceOf(\Exception::class, $apiException);

        $transactionException = new TransactionException('Transaction error');
        $this->assertInstanceOf(AuthorizeNetException::class, $transactionException);
        $this->assertInstanceOf(\Exception::class, $transactionException);
    }
}

