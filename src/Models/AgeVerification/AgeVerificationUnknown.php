<?php

declare(strict_types=1);

namespace Identisure\Client\Models\AgeVerification;

readonly class AgeVerificationUnknown implements AgeVerificationStatus
{
    public function __construct(
        public string $status,
    ) {}
}
