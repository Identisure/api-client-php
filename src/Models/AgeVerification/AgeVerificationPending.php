<?php

declare(strict_types=1);

namespace Idkollen\Client\Models\AgeVerification;

readonly class AgeVerificationPending implements AgeVerificationStatus
{
    public function __construct(
        public string $id,
        public ?string $url,
        public ?int $minAge,
        public ?int $maxAge,
    ) {}
}
