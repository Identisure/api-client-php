<?php

declare(strict_types=1);

namespace Idkollen\Client\Models\BankIdSe;

readonly class BankIdSeVerifyRequest
{
    public function __construct(
        public string $qrCode,
    ) {}
}
