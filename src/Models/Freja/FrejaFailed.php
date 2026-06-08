<?php

declare(strict_types=1);

namespace Idkollen\Client\Models\Freja;

readonly class FrejaFailed implements FrejaStatus
{
    public function __construct(
        public string $id,
        public ?string $refId,
        public string $error,
    ) {}
}
