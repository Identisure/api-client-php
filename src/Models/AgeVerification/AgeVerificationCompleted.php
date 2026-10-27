<?php

declare(strict_types=1);

namespace Identisure\Client\Models\AgeVerification;

readonly class AgeVerificationCompleted implements AgeVerificationStatus
{
    public function __construct(
        public string $id,
        public bool $ageVerified,
    ) {}
}
