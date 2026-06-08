<?php

declare(strict_types=1);

namespace Idkollen\Client\Models\BankIdSe;

readonly class BankIdSeVerifyResponse
{
    public function __construct(
        public string $ssn,
        public string $name,
        public string $givenName,
        public string $surname,
        public ?int $age,
        public ?string $verifiedAt,
    ) {}
}
