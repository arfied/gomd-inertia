<?php

namespace Tests\Unit;

use App\Services\AuthorizeNet\Responses\ResponseParser;
use PHPUnit\Framework\TestCase;

class ResponseParserTest extends TestCase
{
    /**
     * Test extracting customer profile ID from response
     */
    public function test_extract_customer_profile_id()
    {
        $response = [
            'customerProfileId' => '12345',
            'messages' => ['resultCode' => 'Ok']
        ];
        
        $this->assertEquals('12345', ResponseParser::extractCustomerProfileId($response));
    }

    /**
     * Test extracting customer profile ID from message
     */
    public function test_extract_customer_profile_id_from_message()
    {
        $response = [
            'messages' => [
                'message' => [
                    ['text' => 'Successful operation. ID: 67890']
                ]
            ]
        ];
        
        $this->assertEquals('67890', ResponseParser::extractCustomerProfileId($response));
    }

    /**
     * Test extracting payment profile ID from response
     */
    public function test_extract_payment_profile_id()
    {
        $response = [
            'customerPaymentProfileId' => '54321',
            'messages' => ['resultCode' => 'Ok']
        ];
        
        $this->assertEquals('54321', ResponseParser::extractPaymentProfileId($response));
    }

    /**
     * Test extracting transaction ID from response
     */
    public function test_extract_transaction_id()
    {
        $response = [
            'transactionResponse' => [
                'transId' => 'trans123456'
            ]
        ];
        
        $this->assertEquals('trans123456', ResponseParser::extractTransactionId($response));
    }

    /**
     * Test extracting auth code from response
     */
    public function test_extract_auth_code()
    {
        $response = [
            'transactionResponse' => [
                'authCode' => 'AUTH123'
            ]
        ];
        
        $this->assertEquals('AUTH123', ResponseParser::extractAuthCode($response));
    }

    /**
     * Test extracting transaction error message
     */
    public function test_extract_transaction_error_message()
    {
        $response = [
            'errors' => [
                ['errorText' => 'Invalid credit card']
            ]
        ];
        
        $this->assertEquals('Invalid credit card', ResponseParser::extractTransactionErrorMessage($response));
    }

    /**
     * Test extracting transaction error message with no errors
     */
    public function test_extract_transaction_error_message_no_errors()
    {
        $response = [];
        
        $this->assertEquals('Unknown error', ResponseParser::extractTransactionErrorMessage($response));
    }

    /**
     * Test checking if response indicates success
     */
    public function test_is_success()
    {
        $response = [
            'transactionResponse' => [
                'responseCode' => '1'
            ]
        ];
        
        $this->assertTrue(ResponseParser::isSuccess($response));
    }

    /**
     * Test checking if response indicates failure
     */
    public function test_is_not_success()
    {
        $response = [
            'transactionResponse' => [
                'responseCode' => '2'
            ]
        ];
        
        $this->assertFalse(ResponseParser::isSuccess($response));
    }

    /**
     * Test extracting profile data from response
     */
    public function test_extract_profile_data()
    {
        $profileData = [
            'merchantCustomerId' => 'M_123',
            'description' => 'Test Profile'
        ];
        
        $response = [
            'profile' => $profileData
        ];
        
        $this->assertEquals($profileData, ResponseParser::extractProfileData($response));
    }

    /**
     * Test extracting transaction data from response
     */
    public function test_extract_transaction_data()
    {
        $transactionData = [
            'transId' => 'trans123',
            'amount' => '99.99'
        ];
        
        $response = [
            'transaction' => $transactionData
        ];
        
        $this->assertEquals($transactionData, ResponseParser::extractTransactionData($response));
    }

    /**
     * Test extracting null when data is missing
     */
    public function test_extract_null_when_missing()
    {
        $response = [];
        
        $this->assertNull(ResponseParser::extractProfileData($response));
        $this->assertNull(ResponseParser::extractTransactionData($response));
        $this->assertNull(ResponseParser::extractTransactionId($response));
        $this->assertNull(ResponseParser::extractAuthCode($response));
    }
}

