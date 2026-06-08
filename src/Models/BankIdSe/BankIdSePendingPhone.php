<?php

declare(strict_types=1);

namespace Idkollen\Client\Models\BankIdSe;

readonly class BankIdSePendingPhone implements BankIdSePhoneStatus
{
    public function __construct(
        public string $id,
        public ?string $refId,
        public ?string $hintCode,
    ) {}
}
