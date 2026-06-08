<?php

declare(strict_types=1);

namespace Idkollen\Client\Models\Freja;

readonly class FrejaCompleted implements FrejaStatus
{
    public function __construct(
        public string $id,
        public ?string $refId,
        public string $ssn,
        public string $country,
        public string $name,
        public string $givenName,
        public string $surname,
        public ?string $address,
        public ?string $companySignatoryText,
    ) {}
}
