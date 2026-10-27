<?php

declare(strict_types=1);

namespace Identisure\Client;

class IdentisureException extends \RuntimeException
{
    public function __construct(
        public readonly int $statusCode,
        string $message,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }
}
