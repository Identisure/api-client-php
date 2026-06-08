<?php

declare(strict_types=1);

namespace Idkollen\Client\Models\Vipps;

readonly class VippsBackchannelAuthRequest
{
    public function __construct(
        public string $phone,
        public ?bool $requestSsn = null,
        public ?bool $requestEmail = null,
        public ?bool $requestAddress = null,
        public ?string $callbackUrl = null,
        public ?string $refId = null,
    ) {}
}
