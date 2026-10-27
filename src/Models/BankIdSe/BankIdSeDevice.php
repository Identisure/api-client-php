<?php

declare(strict_types=1);

namespace Identisure\Client\Models\BankIdSe;

readonly class BankIdSeDevice
{
    public function __construct(
        public ?string $ipAddress,
        public ?string $uhi,
    ) {}
}
