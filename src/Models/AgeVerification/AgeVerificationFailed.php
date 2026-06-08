<?php

declare(strict_types=1);

namespace Idkollen\Client\Models\AgeVerification;

readonly class AgeVerificationFailed implements AgeVerificationStatus
{
    public function __construct(
        public string $id,
        public string $error,
    ) {}
}
