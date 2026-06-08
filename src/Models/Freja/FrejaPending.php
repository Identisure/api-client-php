<?php

declare(strict_types=1);

namespace Idkollen\Client\Models\Freja;

readonly class FrejaPending implements FrejaStatus
{
    public function __construct(
        public string $id,
        public ?string $refId,
        public string $autoStartToken,
        public string $qrData,
    ) {}
}
