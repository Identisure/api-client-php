<?php

declare(strict_types=1);

namespace Idkollen\Client\Models\BankIdSe;

readonly class BankIdSeAuthRequest
{
    public function __construct(
        public ?string $ssn = null,
        public ?string $ipAddress = null,
        public ?string $callbackUrl = null,
        public ?bool $pinRequired = null,
        public ?string $intent = null,
        public ?string $orgNumber = null,
        public ?bool $requestAddress = null,
        public ?string $refId = null,
    ) {}
}
