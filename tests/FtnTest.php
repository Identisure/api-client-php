<?php

declare(strict_types=1);

namespace Identisure\Client\Tests;

use GuzzleHttp\Psr7\HttpFactory;
use Identisure\Client\Endpoints\FtnEndpoint;
use Identisure\Client\Models\AgeVerification\AgeVerificationCompleted;
use Identisure\Client\Models\AgeVerification\AgeVerificationRequest;
use Identisure\Client\Models\Ftn\FtnAuthRequest;
use Identisure\Client\Models\Ftn\FtnPending;
use Identisure\Client\Models\Ftn\FtnUnknown;
use Identisure\Client\Transport;
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

    public function testUnknownStatus(): void
    {
        $this->http->enqueue(200, [
            'status' => 'SOMETHING_NEW',
            'id' => 'f1',
        ]);
        $result = $this->endpoint->authStatus('f1');
        self::assertInstanceOf(FtnUnknown::class, $result);
        self::assertSame('SOMETHING_NEW', $result->status);
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
