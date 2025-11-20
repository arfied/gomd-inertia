<?php

namespace Tests\Unit\Domain\Shared;

use App\Domain\Shared\PayloadValidator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class PayloadValidatorTest extends TestCase
{
    public function test_validate_required_fields_success(): void
    {
        $payload = ['name' => 'John', 'email' => 'john@example.com'];
        PayloadValidator::validateRequired($payload, ['name', 'email']);
        $this->assertTrue(true); // No exception thrown
    }

    public function test_validate_required_fields_missing(): void
    {
        $payload = ['name' => 'John'];
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required fields: email');
        PayloadValidator::validateRequired($payload, ['name', 'email']);
    }

    public function test_validate_required_fields_empty_string(): void
    {
        $payload = ['name' => '', 'email' => 'john@example.com'];
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required fields: name');
        PayloadValidator::validateRequired($payload, ['name', 'email']);
    }

    public function test_validate_type_string_success(): void
    {
        $payload = ['name' => 'John'];
        PayloadValidator::validateType($payload, 'name', 'string');
        $this->assertTrue(true);
    }

    public function test_validate_type_string_failure(): void
    {
        $payload = ['age' => 30];
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Field 'age' must be a string");
        PayloadValidator::validateType($payload, 'age', 'string');
    }

    public function test_validate_type_int_success(): void
    {
        $payload = ['age' => 30];
        PayloadValidator::validateType($payload, 'age', 'int');
        $this->assertTrue(true);
    }

    public function test_validate_type_float_success(): void
    {
        $payload = ['price' => 99.99];
        PayloadValidator::validateType($payload, 'price', 'float');
        $this->assertTrue(true);
    }

    public function test_validate_type_array_success(): void
    {
        $payload = ['items' => [1, 2, 3]];
        PayloadValidator::validateType($payload, 'items', 'array');
        $this->assertTrue(true);
    }

    public function test_validate_range_success(): void
    {
        $payload = ['age' => 25];
        PayloadValidator::validateRange($payload, 'age', 18, 65);
        $this->assertTrue(true);
    }

    public function test_validate_range_too_low(): void
    {
        $payload = ['age' => 10];
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must be between 18 and 65');
        PayloadValidator::validateRange($payload, 'age', 18, 65);
    }

    public function test_validate_range_too_high(): void
    {
        $payload = ['age' => 100];
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must be between 18 and 65');
        PayloadValidator::validateRange($payload, 'age', 18, 65);
    }

    public function test_validate_enum_success(): void
    {
        $payload = ['status' => 'active'];
        PayloadValidator::validateEnum($payload, 'status', ['active', 'inactive', 'pending']);
        $this->assertTrue(true);
    }

    public function test_validate_enum_failure(): void
    {
        $payload = ['status' => 'unknown'];
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must be one of: active, inactive, pending');
        PayloadValidator::validateEnum($payload, 'status', ['active', 'inactive', 'pending']);
    }
}

