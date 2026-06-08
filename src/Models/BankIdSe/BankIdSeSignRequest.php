<?php

declare(strict_types=1);

namespace Idkollen\Client\Models\BankIdSe;

readonly class BankIdSeSignRequest
{
    public function __construct(
        public string $text,
        public ?string $ssn = null,
        public ?string $ipAddress = null,
        public ?string $callbackUrl = null,
        public ?bool $pinRequired = null,
        public ?string $digest = null,
        public ?string $orgNumber = null,
        public ?bool $requestAddress = null,
        public ?string $refId = null,
    ) {}
}
