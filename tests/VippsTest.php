<?php

declare(strict_types=1);

namespace Idkollen\Client\Tests;

use GuzzleHttp\Psr7\HttpFactory;
use Idkollen\Client\Endpoints\VippsEndpoint;
use Idkollen\Client\Models\Vipps\VippsAuthRequest;
use Idkollen\Client\Models\Vipps\VippsBackchannelAuthRequest;
use Idkollen\Client\Models\Vipps\VippsPending;
use Idkollen\Client\Transport;
use PHPUnit\Framework\TestCase;

final class VippsTest extends TestCase
{
    private MockHttpClient $http;
    private VippsEndpoint $endpoint;

    protected function setUp(): void
    {
        $this->http = new MockHttpClient();
        $factory = new HttpFactory();
        $transport = new Transport($this->http, $factory, $factory, 'https://x.test', 'cid', 'sec');
        $this->endpoint = new VippsEndpoint($transport);
    }

    public function testAuthPending(): void
    {
        $this->http->enqueue(200, ['status' => 'PENDING', 'id' => 'v1', 'url' => 'https://login']);
        $result = $this->endpoint->auth(new VippsAuthRequest());
        self::assertInstanceOf(VippsPending::class, $result);
    }

    public function testBackchannelAuthSendsPhone(): void
    {
        $this->http->enqueue(200, ['status' => 'PENDING', 'id' => 'v1']);
        $this->endpoint->backchannelAuth(new VippsBackchannelAuthRequest(phone: '+4712345678'));
        $body = json_decode((string) $this->http->lastRequest()->getBody(), true);
        self::assertSame('+4712345678', $body['phone']);
    }
}
