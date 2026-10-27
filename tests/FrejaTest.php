<?php

declare(strict_types=1);

namespace Identisure\Client\Tests;

use GuzzleHttp\Psr7\HttpFactory;
use Identisure\Client\Endpoints\FrejaEndpoint;
use Identisure\Client\Models\Freja\FrejaAuthRequest;
use Identisure\Client\Models\Freja\FrejaCompleted;
use Identisure\Client\Models\Freja\FrejaPending;
use Identisure\Client\Transport;
use PHPUnit\Framework\TestCase;

final class FrejaTest extends TestCase
{
    private MockHttpClient $http;
    private FrejaEndpoint $endpoint;

    protected function setUp(): void
    {
        $this->http = new MockHttpClient();
        $factory = new HttpFactory();
        $transport = new Transport($this->http, $factory, $factory, 'https://x.test', 'cid', 'sec');
        $this->endpoint = new FrejaEndpoint($transport);
    }

    public function testAuthPending(): void
    {
        $this->http->enqueue(200, [
            'status' => 'PENDING',
            'id' => 'f1',
            'autoStartToken' => 'tok',
            'qrData' => 'qr',
        ]);

        $result = $this->endpoint->auth(new FrejaAuthRequest());
        self::assertInstanceOf(FrejaPending::class, $result);
        self::assertSame('qr', $result->qrData);
    }

    public function testAuthCompleted(): void
    {
        $this->http->enqueue(200, [
            'status' => 'COMPLETED',
            'id' => 'f1',
            'ssn' => '199001011234',
            'country' => 'SE',
            'name' => 'Test',
            'givenName' => 'T',
            'surname' => 'est',
        ]);

        $result = $this->endpoint->authStatus('f1');
        self::assertInstanceOf(FrejaCompleted::class, $result);
        self::assertSame('SE', $result->country);
    }
}
