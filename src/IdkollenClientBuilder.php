<?php

declare(strict_types=1);

namespace Idkollen\Client;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

final class IdkollenClientBuilder
{
    private Environment $environment = Environment::Production;
    private ?string $baseUrl = null;
    private ?ClientInterface $httpClient = null;
    private ?RequestFactoryInterface $requestFactory = null;
    private ?StreamFactoryInterface $streamFactory = null;

    public function __construct(
        private readonly string $clientId,
        private readonly string $clientSecret,
    ) {}

    public function environment(Environment $env): static
    {
        $this->environment = $env;
        return $this;
    }

    public function baseUrl(string $url): static
    {
        $this->baseUrl = $url;
        return $this;
    }

    public function httpClient(ClientInterface $client): static
    {
        $this->httpClient = $client;
        return $this;
    }

    public function requestFactory(RequestFactoryInterface $factory): static
    {
        $this->requestFactory = $factory;
        return $this;
    }

    public function streamFactory(StreamFactoryInterface $factory): static
    {
        $this->streamFactory = $factory;
        return $this;
    }

    public function build(): IdkollenClient
    {
        $httpClient = $this->httpClient ?? self::discoverHttpClient();
        $requestFactory = $this->requestFactory ?? self::discoverHttpFactory();
        $streamFactory = $this->streamFactory ?? self::discoverHttpFactory();
        $baseUrl = $this->baseUrl ?? $this->environment->baseUrl();

        $transport = new Transport(
            httpClient: $httpClient,
            requestFactory: $requestFactory,
            streamFactory: $streamFactory,
            baseUrl: $baseUrl,
            clientId: $this->clientId,
            clientSecret: $this->clientSecret,
        );

        return new IdkollenClient($transport);
    }

    private static function discoverHttpClient(): ClientInterface
    {
        if (class_exists(\GuzzleHttp\Client::class)) {
            return new \GuzzleHttp\Client();
        }
        throw new \RuntimeException(
            'No PSR-18 HTTP client available. Install guzzlehttp/guzzle or pass one via httpClient().'
        );
    }

    private static function discoverHttpFactory(): RequestFactoryInterface&StreamFactoryInterface
    {
        if (class_exists(\GuzzleHttp\Psr7\HttpFactory::class)) {
            return new \GuzzleHttp\Psr7\HttpFactory();
        }
        throw new \RuntimeException(
            'No PSR-17 factory available. Install guzzlehttp/guzzle or pass factories via requestFactory()/streamFactory().'
        );
    }
}
