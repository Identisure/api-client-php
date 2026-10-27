<?php

declare(strict_types=1);

namespace Identisure\Client\Models\Vipps;

readonly class VippsUnknown implements VippsStatus
{
    public function __construct(
        public string $status,
    ) {}
}
