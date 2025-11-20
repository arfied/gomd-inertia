<?php

namespace Tests\Unit\Domain\Shared;

use App\Domain\Shared\UuidValidator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class UuidValidatorTest extends TestCase
{
    private string $validUuid = '550e8400-e29b-11d4-a716-446655440000'; // v1
    private string $validUuidV4 = '550e8400-e29b-4000-a000-000000000000'; // v4
    private string $invalidUuid = 'not-a-uuid';
    private string $emptyUuid = '';

    public function test_validate_valid_uuid(): void
    {
        UuidValidator::validate($this->validUuid);
        $this->assertTrue(true); // No exception thrown
    }

    public function test_validate_invalid_uuid_format(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid UUID format');
        UuidValidator::validate($this->invalidUuid);
    }

    public function test_validate_empty_uuid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('UUID cannot be empty');
        UuidValidator::validate($this->emptyUuid);
    }

    public function test_validate_v4_valid(): void
    {
        UuidValidator::validateV4($this->validUuidV4);
        $this->assertTrue(true); // No exception thrown
    }

    public function test_validate_v4_invalid_version(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('UUID must be version 4');
        UuidValidator::validateV4($this->validUuid);
    }

    public function test_is_valid_true(): void
    {
        $this->assertTrue(UuidValidator::isValid($this->validUuid));
    }

    public function test_is_valid_false(): void
    {
        $this->assertFalse(UuidValidator::isValid($this->invalidUuid));
    }

    public function test_is_valid_v4_true(): void
    {
        $this->assertTrue(UuidValidator::isValidV4($this->validUuidV4));
    }

    public function test_is_valid_v4_false(): void
    {
        $this->assertFalse(UuidValidator::isValidV4($this->validUuid));
    }

    public function test_uuid_case_insensitive(): void
    {
        $upperUuid = strtoupper($this->validUuid);
        UuidValidator::validate($upperUuid);
        $this->assertTrue(true); // No exception thrown
    }

    public function test_uuid_with_extra_characters(): void
    {
        $this->expectException(InvalidArgumentException::class);
        UuidValidator::validate($this->validUuid . 'x');
    }

    public function test_uuid_too_short(): void
    {
        $this->expectException(InvalidArgumentException::class);
        UuidValidator::validate('550e8400-e29b-41d4-a716');
    }
}

