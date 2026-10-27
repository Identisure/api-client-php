<?php

declare(strict_types=1);

namespace Identisure\Client;

readonly class PollOptions
{
    public function __construct(
        public int $interval = 2,
        public int $timeout = 300,
    ) {}
}
