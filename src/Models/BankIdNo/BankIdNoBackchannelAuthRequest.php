<?php

declare(strict_types=1);

namespace Identisure\Client\Models\BankIdNo;

readonly class BankIdNoBackchannelAuthRequest
{
    public function __construct(
        public string $ssn,
        public ?string $callbackUrl = null,
        public ?string $refId = null,
    ) {}
}
