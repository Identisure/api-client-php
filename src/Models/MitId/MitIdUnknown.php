<?php

declare(strict_types=1);

namespace Idkollen\Client\Models\MitId;

readonly class MitIdUnknown implements MitIdStatus
{
    public function __construct(
        public string $status,
    ) {}
}
