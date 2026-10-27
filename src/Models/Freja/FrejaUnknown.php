<?php

declare(strict_types=1);

namespace Identisure\Client\Models\Freja;

readonly class FrejaUnknown implements FrejaStatus
{
    public function __construct(
        public string $status,
    ) {}
}
