<?php

declare(strict_types=1);

namespace Identisure\Client\Models\Ftn;

readonly class FtnUnknown implements FtnStatus
{
    public function __construct(
        public string $status,
    ) {}
}
