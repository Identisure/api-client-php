<?php

declare(strict_types=1);

namespace Idkollen\Client\Tests;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class MockHttpClient implements ClientInterface
{
    /** @var list<array{status:int,body:string}> */
    private array $queue = [];

    /** @var list<RequestInterface> */
    private array $requests = [];

    public function enqueue(int $status, array|string $body): void
    {
        $bodyStr = is_array($body) ? json_encode($body) : $body;
        $this->queue[] = ['status' => $status, 'body' => $bodyStr];
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $this->requests[] = $request;
        if (count($this->queue) === 0) {
            throw new \RuntimeException('No responses queued');
        }
        $item = array_shift($this->queue);
        return new Response($item['status'], [], $item['body']);
    }

    public function lastRequest(): RequestInterface
    {
        if (count($this->requests) === 0) {
            throw new \RuntimeException('No requests captured');
        }
        return $this->requests[count($this->requests) - 1];
    }

    /** @return list<RequestInterface> */
    public function requests(): array
    {
        return $this->requests;
    }
}
