<?php

declare(strict_types=1);

namespace Identisure\Client\Tests;

use GuzzleHttp\Psr7\HttpFactory;
use Identisure\Client\Endpoints\BankIdNoEndpoint;
use Identisure\Client\Models\BankIdNo\BankIdNoAuthRequest;
use Identisure\Client\Models\BankIdNo\BankIdNoCompleted;
use Identisure\Client\Models\BankIdNo\BankIdNoPending;
use Identisure\Client\Transport;
use PHPUnit\Framework\TestCase;

final class BankIdNoTest extends TestCase
{
    private MockHttpClient $http;
    private BankIdNoEndpoint $endpoint;

    protected function setUp(): void
    {
        $this->http = new MockHttpClient();
        $factory = new HttpFactory();
        $transport = new Transport($this->http, $factory, $factory, 'https://x.test', 'cid', 'sec');
        $this->endpoint = new BankIdNoEndpoint($transport);
    }

    public function testAuthPending(): void
    {
        $this->http->enqueue(200, ['status' => 'PENDING', 'id' => 'no1', 'url' => 'https://login']);
        $result = $this->endpoint->auth(new BankIdNoAuthRequest(requestSsn: true));
        self::assertInstanceOf(BankIdNoPending::class, $result);
        self::assertSame('https://login', $result->url);
    }

    public function testAuthCompletedWithSignedDocuments(): void
    {
        $this->http->enqueue(200, [
            'status' => 'COMPLETED',
            'id' => 'no1',
            'ssn' => '01010100000',
            'name' => 'Ola Nordmann',
            'givenName' => 'Ola',
            'surname' => 'Nordmann',
            'signedDocuments' => [
                ['id' => 'd1', 'hash' => 'h1'],
                ['id' => 'd2', 'hash' => 'h2'],
            ],
        ]);

        $result = $this->endpoint->authStatus('no1');
        self::assertInstanceOf(BankIdNoCompleted::class, $result);
        self::assertCount(2, $result->signedDocuments);
        self::assertSame('d1', $result->signedDocuments[0]->id);
    }
}
