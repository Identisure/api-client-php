<?php

declare(strict_types=1);

namespace Idkollen\Client\Tests;

use GuzzleHttp\Psr7\HttpFactory;
use Idkollen\Client\Endpoints\FtnEndpoint;
use Idkollen\Client\Models\AgeVerification\AgeVerificationCompleted;
use Idkollen\Client\Models\AgeVerification\AgeVerificationRequest;
use Idkollen\Client\Models\Ftn\FtnAuthRequest;
use Idkollen\Client\Models\Ftn\FtnPending;
use Idkollen\Client\Transport;
use PHPUnit\Framework\TestCase;

final class FtnTest extends TestCase
{
    private MockHttpClient $http;
    private FtnEndpoint $endpoint;

    protected function setUp(): void
    {
        $this->http = new MockHttpClient();
        $factory = new HttpFactory();
        $transport = new Transport($this->http, $factory, $factory, 'https://x.test', 'cid', 'sec');
        $this->endpoint = new FtnEndpoint($transport);
    }

    public function testAuthPending(): void
    {
        $this->http->enqueue(200, [
            'status' => 'PENDING',
            'id' => 'f1',
            'url' => 'https://login',
        ]);
        $result = $this->endpoint->auth(new FtnAuthRequest());
        self::assertInstanceOf(FtnPending::class, $result);
        self::assertSame('https://login', $result->url);
    }

    public function testAgeVerificationCompleted(): void
    {
        $this->http->enqueue(200, [
            'status' => 'COMPLETED',
            'id' => 'av1',
            'ageVerified' => true,
        ]);
        $result = $this->endpoint->ageVerification(new AgeVerificationRequest(minAge: 18));
        self::assertInstanceOf(AgeVerificationCompleted::class, $result);
        self::assertTrue($result->ageVerified);
    }
}
