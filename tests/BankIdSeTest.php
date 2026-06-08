<?php

declare(strict_types=1);

namespace Idkollen\Client\Tests;

use GuzzleHttp\Psr7\HttpFactory;
use Idkollen\Client\Endpoints\BankIdSeEndpoint;
use Idkollen\Client\Models\BankIdSe\BankIdSeAuthRequest;
use Idkollen\Client\Models\BankIdSe\BankIdSeCompleted;
use Idkollen\Client\Models\BankIdSe\BankIdSeFailed;
use Idkollen\Client\Models\BankIdSe\BankIdSePending;
use Idkollen\Client\Models\BankIdSe\BankIdSePendingPhone;
use Idkollen\Client\Models\BankIdSe\BankIdSePhoneAuthRequest;
use Idkollen\Client\Models\BankIdSe\BankIdSeVerifyRequest;
use Idkollen\Client\Transport;
use PHPUnit\Framework\TestCase;

final class BankIdSeTest extends TestCase
{
    private MockHttpClient $http;
    private BankIdSeEndpoint $endpoint;

    protected function setUp(): void
    {
        $this->http = new MockHttpClient();
        $factory = new HttpFactory();
        $transport = new Transport($this->http, $factory, $factory, 'https://x.test', 'cid', 'sec');
        $this->endpoint = new BankIdSeEndpoint($transport);
    }

    public function testAuthPending(): void
    {
        $this->http->enqueue(200, [
            'status' => 'PENDING',
            'id' => 'abc',
            'refId' => null,
            'autoStartToken' => 'tok',
        ]);

        $result = $this->endpoint->auth(new BankIdSeAuthRequest(ssn: '199001011234'));

        self::assertInstanceOf(BankIdSePending::class, $result);
        self::assertSame('abc', $result->id);
        self::assertSame('tok', $result->autoStartToken);

        $body = (string) $this->http->lastRequest()->getBody();
        self::assertSame(['ssn' => '199001011234'], json_decode($body, true));
    }

    public function testAuthCompleted(): void
    {
        $this->http->enqueue(200, [
            'status' => 'COMPLETED',
            'id' => 'abc',
            'ssn' => '199001011234',
            'name' => 'Test User',
            'givenName' => 'Test',
            'surname' => 'User',
        ]);

        $result = $this->endpoint->auth(new BankIdSeAuthRequest());

        self::assertInstanceOf(BankIdSeCompleted::class, $result);
        self::assertSame('Test User', $result->name);
    }

    public function testAuthFailed(): void
    {
        $this->http->enqueue(200, [
            'status' => 'FAILED',
            'id' => 'abc',
            'error' => 'userCancel',
        ]);

        $result = $this->endpoint->auth(new BankIdSeAuthRequest());

        self::assertInstanceOf(BankIdSeFailed::class, $result);
        self::assertSame('userCancel', $result->error);
    }

    public function testPhoneAuthPending(): void
    {
        $this->http->enqueue(200, [
            'status' => 'PENDING',
            'id' => 'abc',
            'hintCode' => 'outstandingTransaction',
        ]);

        $result = $this->endpoint->phoneAuth(new BankIdSePhoneAuthRequest(
            ssn: '199001011234',
            callInitiator: 'user',
        ));

        self::assertInstanceOf(BankIdSePendingPhone::class, $result);
    }

    public function testVerify(): void
    {
        $this->http->enqueue(200, [
            'ssn' => '199001011234',
            'name' => 'Test User',
            'givenName' => 'Test',
            'surname' => 'User',
            'age' => 34,
            'verifiedAt' => '2026-06-08T10:00:00Z',
        ]);

        $result = $this->endpoint->verify(new BankIdSeVerifyRequest(qrCode: 'qr'));

        self::assertSame(34, $result->age);
        self::assertSame('Test User', $result->name);
    }

    public function testCancelAuthSendsDelete(): void
    {
        $this->http->enqueue(204, '');
        $this->endpoint->cancelAuth('xyz');
        self::assertSame('DELETE', $this->http->lastRequest()->getMethod());
        self::assertSame('/v3/bankid-se/auth/xyz', $this->http->lastRequest()->getUri()->getPath());
    }

    public function testAuthStatusGet(): void
    {
        $this->http->enqueue(200, [
            'status' => 'PENDING',
            'id' => 'xyz',
        ]);
        $this->endpoint->authStatus('xyz');
        self::assertSame('GET', $this->http->lastRequest()->getMethod());
    }
}
