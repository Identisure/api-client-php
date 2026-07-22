<?php

declare(strict_types=1);

namespace Idkollen\Client\Models\BankIdSe;

readonly class BankIdSeDevice
{
    public function __construct(
        public ?string $ipAddress,
        public ?string $uhi,
    ) {}
}
