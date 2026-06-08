<?php

declare(strict_types=1);

namespace Idkollen\Client\Models\MitId;

readonly class MitIdAuthRequest
{
    public function __construct(
        public ?string $redirectUrl = null,
        public ?string $referenceText = null,
        public ?bool $requestPhone = null,
        public ?bool $requestEmail = null,
        public ?bool $requestAddress = null,
        public ?string $refId = null,
    ) {}
}
