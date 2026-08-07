<?php

declare(strict_types=1);

namespace Idkollen\Client\Models\Vipps;

readonly class VippsUnknown implements VippsStatus
{
    public function __construct(
        public string $status,
    ) {}
}
