<?php

declare(strict_types=1);

namespace Identisure\Client\Models\AgeVerification;

readonly class AgeVerificationRequest
{
    public function __construct(
        public ?int $minAge = null,
        public ?int $maxAge = null,
        public ?string $refId = null,
        public ?string $callbackUrl = null,
        public ?string $redirectUrl = null,
    ) {}
}
