<?php

declare(strict_types=1);

namespace Idkollen\Client;

readonly class PollOptions
{
    public function __construct(
        public int $interval = 2,
        public int $timeout = 300,
    ) {}
}
