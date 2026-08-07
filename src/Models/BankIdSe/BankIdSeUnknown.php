<?php

declare(strict_types=1);

namespace Idkollen\Client\Models\BankIdSe;

readonly class BankIdSeUnknown implements BankIdSeStatus, BankIdSePhoneStatus
{
    public function __construct(
        public string $status,
    ) {}
}
