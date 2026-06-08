<?php

declare(strict_types=1);

namespace Idkollen\Client\Models\Ftn;

readonly class FtnPending implements FtnStatus
{
    public function __construct(
        public string $id,
        public ?string $refId,
        public string $url,
    ) {}
}
