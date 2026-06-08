<?php

declare(strict_types=1);

namespace Idkollen\Client\Tests;

use GuzzleHttp\Psr7\HttpFactory;
use Idkollen\Client\Endpoints\MitIdEndpoint;
use Idkollen\Client\Models\MitId\MitIdAuthRequest;
use Idkollen\Client\Models\MitId\MitIdCompleted;
use Idkollen\Client\Models\MitId\MitIdPending;
use Idkollen\Client\Transport;
use PHPUnit\Framework\TestCase;

final class MitIdTest extends TestCase
{
    private MockHttpClient $http;
    private MitIdEndpoint $endpoint;

    protected function setUp(): void
    {
        $this->http = new MockHttpClient();
        $factory = new HttpFactory();
        $transport = new Transport($this->http, $factory, $factory, 'https://x.test', 'cid', 'sec');
        $this->endpoint = new MitIdEndpoint($transport);
    }

    public function testAuthPending(): void
    {
        $this->http->enqueue(200, ['status' => 'PENDING', 'id' => 'm1', 'url' => 'https://login']);
        $result = $this->endpoint->auth(new MitIdAuthRequest());
        self::assertInstanceOf(MitIdPending::class, $result);
    }

    public function testSignCompletedWithSignResult(): void
    {
        $this->http->enqueue(200, [
            'status' => 'COMPLETED',
            'id' => 'm1',
            'ssn' => '0101010000',
            'name' => 'Hans',
            'givenName' => 'Hans',
            'surname' => 'H',
            'signResult' => ['checksum' => 'abc123'],
        ]);

        $result = $this->endpoint->signStatus('m1');
        self::assertInstanceOf(MitIdCompleted::class, $result);
        self::assertNotNull($result->signResult);
        self::assertSame('abc123', $result->signResult->checksum);
    }
}
