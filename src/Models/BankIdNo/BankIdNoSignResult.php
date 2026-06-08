<?php

declare(strict_types=1);

namespace Idkollen\Client\Models\BankIdNo;

readonly class BankIdNoSignResult
{
    public function __construct(
        public string $endUser,
        public string $merchant,
        public string $hash,
    ) {}
}
