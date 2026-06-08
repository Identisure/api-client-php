<?php

declare(strict_types=1);

namespace Idkollen\Client\Models\Vipps;

readonly class VippsAuthRequest
{
    public function __construct(
        public ?string $redirectUrl = null,
        public ?bool $requestSsn = null,
        public ?bool $requestPhone = null,
        public ?bool $requestEmail = null,
        public ?bool $requestAddress = null,
        public ?string $refId = null,
        public ?string $appCallbackUri = null,
    ) {}
}
