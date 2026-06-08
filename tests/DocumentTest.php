<?php

declare(strict_types=1);

namespace Idkollen\Client\Tests;

use GuzzleHttp\Psr7\HttpFactory;
use Idkollen\Client\Endpoints\DocumentEndpoint;
use Idkollen\Client\Transport;
use PHPUnit\Framework\TestCase;

final class DocumentTest extends TestCase
{
    private MockHttpClient $http;
    private DocumentEndpoint $endpoint;

    protected function setUp(): void
    {
        $this->http = new MockHttpClient();
        $factory = new HttpFactory();
        $transport = new Transport($this->http, $factory, $factory, 'https://x.test', 'cid', 'sec');
        $this->endpoint = new DocumentEndpoint($transport);
    }

    public function testUpload(): void
    {
        $this->http->enqueue(200, ['id' => 'doc1', 'hash' => 'h1']);
        $result = $this->endpoint->upload('PDF-DATA', 'contract.pdf');
        self::assertSame('doc1', $result->id);
        self::assertSame('h1', $result->hash);

        $req = $this->http->lastRequest();
        self::assertSame('POST', $req->getMethod());
        $contentType = $req->getHeaderLine('Content-Type');
        self::assertStringStartsWith('multipart/form-data; boundary=', $contentType);

        $body = (string) $req->getBody();
        self::assertStringContainsString('filename="contract.pdf"', $body);
        self::assertStringContainsString('Content-Type: application/pdf', $body);
        self::assertStringContainsString('PDF-DATA', $body);
    }

    public function testDownloadReturnsRawBytes(): void
    {
        $this->http->enqueue(200, "\x25PDF-binary-bytes");
        $result = $this->endpoint->download('doc1');
        self::assertSame("\x25PDF-binary-bytes", $result);
    }

    public function testDelete(): void
    {
        $this->http->enqueue(204, '');
        $this->endpoint->delete('doc1');
        self::assertSame('DELETE', $this->http->lastRequest()->getMethod());
        self::assertSame('/document/doc1', $this->http->lastRequest()->getUri()->getPath());
    }
}
