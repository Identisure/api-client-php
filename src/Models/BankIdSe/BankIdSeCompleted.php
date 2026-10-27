<?php

declare(strict_types=1);

namespace Identisure\Client\Models\BankIdSe;

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
        public string $ocspResponse,
        public ?string $risk,
        public ?BankIdSeDevice $device,
        public ?BankIdSeStepUp $stepUp,
        public ?string $address,
        public ?string $companySignatoryText,
        public ?string $authenticatedAt,
        public ?string $signedAt,
    ) {}
}
