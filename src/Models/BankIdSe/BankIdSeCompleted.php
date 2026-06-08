<?php

declare(strict_types=1);

namespace Idkollen\Client\Models\BankIdSe;

readonly class BankIdSeCompleted implements BankIdSeStatus, BankIdSePhoneStatus
{
    public function __construct(
        public string $id,
        public ?string $refId,
        public string $ssn,
        public string $name,
        public string $givenName,
        public string $surname,
        public ?string $certStartDate,
        public ?string $address,
        public ?string $companySignatoryText,
    ) {}
}
