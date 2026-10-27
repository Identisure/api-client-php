<?php

declare(strict_types=1);

namespace Identisure\Client\Models\Vipps;

readonly class VippsCompleted implements VippsStatus
{
    public function __construct(
        public string $id,
        public ?string $refId,
        public ?string $ssn,
        public string $name,
        public string $givenName,
        public string $surname,
        public ?string $phone,
        public ?string $email,
        public ?string $address,
        public ?string $birthDate,
        public ?string $pid,
        public ?string $bankId,
        public ?string $authenticatedAt,
    ) {}
}
