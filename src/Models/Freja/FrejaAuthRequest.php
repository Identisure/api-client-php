<?php

declare(strict_types=1);

namespace Identisure\Client\Models\Freja;

readonly class FrejaAuthRequest
{
    public function __construct(
        public ?string $ssn = null,
        public ?string $callbackUrl = null,
        public ?string $minRegistrationLevel = null,
        public ?string $orgNumber = null,
        public ?bool $requestAddress = null,
        public ?string $refId = null,
    ) {}
}
