<?php

declare(strict_types=1);

namespace Idkollen\Client;

class WaitException extends \RuntimeException
{
    public function __construct(
        public readonly bool $timeout,
        ?\Throwable $previous = null,
    ) {
        parent::__construct(
            $timeout ? 'Poll timed out' : 'Poll error: ' . ($previous?->getMessage() ?? ''),
            0,
            $previous,
        );
    }
}
