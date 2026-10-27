<?php

declare(strict_types=1);

namespace Identisure\Client\Tests;

use GuzzleHttp\Psr7\HttpFactory;
use Identisure\Client\Environment;
use Identisure\Client\IdentisureClientBuilder;
use Identisure\Client\IdentisureException;
use Identisure\Client\Transport;
use PHPUnit\Framework\TestCase;

final class ClientTest extends TestCase
{
    public function testBuilderProducesClient(): void
    {
        $client = (new IdentisureClientBuilder('id', 'secret'))
            ->environment(Environment::Staging)
            ->build();

        self::assertInstanceOf(\Identisure\Client\IdentisureClient::class, $client);
    }

    public function testTransportAttachesBasicAuthAndUserAgent(): void
    {
        $http = new MockHttpClient();
        $http->enqueue(200, ['ok' => true]);
        $factory = new HttpFactory();

        $transport = new Transport(
            httpClient: $http,
            requestFactory: $factory,
            streamFactory: $factory,
            baseUrl: 'https://example.test',
            clientId: 'cid',
            clientSecret: 'sec',
        );
        $transport->get('/v3/ping');

        $req = $http->lastRequest();
        self::assertSame('Basic ' . base64_encode('cid:sec'), $req->getHeaderLine('Authorization'));
        self::assertStringContainsString('identisure-client-php/', $req->getHeaderLine('User-Agent'));
        self::assertSame('https://example.test/v3/ping', (string) $req->getUri());
    }

    public function testNon2xxThrowsIdentisureException(): void
    {
        $http = new MockHttpClient();
        $http->enqueue(400, ['message' => 'bad request']);
        $factory = new HttpFactory();
        $transport = new Transport($http, $factory, $factory, 'https://x.test', 'a', 'b');

        $this->expectException(IdentisureException::class);
        $this->expectExceptionMessage('bad request');
        $transport->post('/v3/things', ['a' => 1]);
    }
}
