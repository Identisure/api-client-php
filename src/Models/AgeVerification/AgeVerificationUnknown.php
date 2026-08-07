<?php

declare(strict_types=1);

namespace Idkollen\Client\Models\AgeVerification;

readonly class AgeVerificationUnknown implements AgeVerificationStatus
{
    public function __construct(
        public string $status,
    ) {}
}
