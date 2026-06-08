<?php

declare(strict_types=1);

namespace Idkollen\Client\Models\BankIdSe;

readonly class BankIdSePhoneAuthRequest
{
    public function __construct(
        public string $ssn,
        public string $callInitiator,
        public ?string $callbackUrl = null,
        public ?bool $pinRequired = null,
        public ?string $intent = null,
        public ?string $orgNumber = null,
        public ?bool $requestAddress = null,
        public ?string $refId = null,
    ) {}
}
