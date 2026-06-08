<?php

declare(strict_types=1);

namespace Idkollen\Client;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * @internal Used by endpoint classes. Not part of the public API.
 */
final class Transport
{
    private const USER_AGENT = 'idkollen-client-php/0.1.0';

    public function __construct(
        private readonly ClientInterface $httpClient,
        private readonly RequestFactoryInterface $requestFactory,
        private readonly StreamFactoryInterface $streamFactory,
        private readonly string $baseUrl,
        private readonly string $clientId,
        private readonly string $clientSecret,
    ) {}

    public function post(string $path, array $body): array
    {
        $request = $this->buildRequest('POST', $path)
            ->withHeader('Content-Type', 'application/json')
            ->withBody($this->streamFactory->createStream(json_encode($body, JSON_THROW_ON_ERROR)));

        return $this->send($request);
    }

    public function get(string $path): array
    {
        return $this->send($this->buildRequest('GET', $path));
    }

    public function getRaw(string $path): string
    {
        return $this->sendRaw($this->buildRequest('GET', $path));
    }

    public function delete(string $path): void
    {
        $this->send($this->buildRequest('DELETE', $path));
    }

    public function postMultipart(string $path, string $data, string $filename, string $mimeType): array
    {
        $boundary = bin2hex(random_bytes(16));
        $body = "--{$boundary}\r\n"
            . "Content-Disposition: form-data; name=\"file\"; filename=\"{$filename}\"\r\n"
            . "Content-Type: {$mimeType}\r\n"
            . "\r\n"
            . $data
            . "\r\n"
            . "--{$boundary}--\r\n";

        $request = $this->buildRequest('POST', $path)
            ->withHeader('Content-Type', "multipart/form-data; boundary={$boundary}")
            ->withBody($this->streamFactory->createStream($body));

        return $this->send($request);
    }

    private function buildRequest(string $method, string $path): RequestInterface
    {
        return $this->requestFactory->createRequest($method, $this->baseUrl . $path)
            ->withHeader('User-Agent', self::USER_AGENT)
            ->withHeader('Authorization', 'Basic ' . base64_encode("{$this->clientId}:{$this->clientSecret}"));
    }

    private function send(RequestInterface $request): array
    {
        $body = $this->sendRaw($request);
        if ($body === '') {
            return [];
        }
        $decoded = json_decode($body, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function sendRaw(RequestInterface $request): string
    {
        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            throw new IdkollenException(0, $e->getMessage());
        }

        $statusCode = $response->getStatusCode();
        $body = (string) $response->getBody();

        if ($statusCode < 200 || $statusCode >= 300) {
            $decoded = json_decode($body, true);
            $message = is_array($decoded) && isset($decoded['message']) ? (string) $decoded['message'] : $body;
            throw new IdkollenException($statusCode, $message);
        }

        return $body;
    }
}
