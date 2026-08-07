<?php

declare(strict_types=1);

namespace Idkollen\Client\Models\Ftn;

readonly class FtnUnknown implements FtnStatus
{
    public function __construct(
        public string $status,
    ) {}
}
