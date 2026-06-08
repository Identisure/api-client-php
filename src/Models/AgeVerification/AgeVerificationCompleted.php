<?php

declare(strict_types=1);

namespace Idkollen\Client\Models\AgeVerification;

readonly class AgeVerificationCompleted implements AgeVerificationStatus
{
    public function __construct(
        public string $id,
        public bool $ageVerified,
    ) {}
}
