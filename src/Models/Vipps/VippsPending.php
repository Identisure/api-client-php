<?php

declare(strict_types=1);

namespace Identisure\Client\Models\Vipps;

readonly class VippsPending implements VippsStatus
{
    public function __construct(
        public string $id,
        public ?string $refId,
        public ?string $url,
    ) {}
}
