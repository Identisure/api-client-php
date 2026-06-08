<?php

declare(strict_types=1);

namespace Idkollen\Client\Models\MitId;

readonly class MitIdSignResult
{
    public function __construct(
        public string $checksum,
    ) {}
}
