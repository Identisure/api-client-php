<?php

declare(strict_types=1);

namespace Idkollen\Client\Models\Freja;

readonly class FrejaUnknown implements FrejaStatus
{
    public function __construct(
        public string $status,
    ) {}
}
