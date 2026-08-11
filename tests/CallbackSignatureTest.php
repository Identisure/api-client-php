<?php

declare(strict_types=1);

namespace Idkollen\Client\Tests;

use Idkollen\Client\CallbackSignature;
use PHPUnit\Framework\TestCase;

final class CallbackSignatureTest extends TestCase
{
    private const BODY = '{"id":"abc","status":"COMPLETED"}';
    private const SIGNATURE = 'Ag2m3etnW31OP585jvcrvOupK7Vgsrzcq17KxKvkIOs=';
    private const SECRET = 'test-secret';

    public function testValidSignature(): void
    {
        $this->assertTrue(CallbackSignature::verify(self::BODY, self::SIGNATURE, self::SECRET));
    }

    public function testTamperedBody(): void
    {
        $body = '{"id":"abc","status":"FAILED"}';
        $this->assertFalse(CallbackSignature::verify($body, self::SIGNATURE, self::SECRET));
    }

    public function testMalformedSignature(): void
    {
        $this->assertFalse(CallbackSignature::verify(self::BODY, 'not base64!', self::SECRET));
    }
}
