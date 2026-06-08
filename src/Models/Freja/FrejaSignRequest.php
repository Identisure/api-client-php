<?php

declare(strict_types=1);

namespace Idkollen\Client\Models\Freja;

readonly class FrejaSignRequest
{
    public function __construct(
        public string $text,
        public ?string $ssn = null,
        public ?string $callbackUrl = null,
        public ?string $minRegistrationLevel = null,
        public ?string $orgNumber = null,
        public ?bool $requestAddress = null,
        public ?string $refId = null,
    ) {}
}
