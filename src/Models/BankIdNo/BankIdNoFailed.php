<?php

declare(strict_types=1);

namespace Idkollen\Client\Models\BankIdNo;

readonly class BankIdNoFailed implements BankIdNoStatus
{
    public function __construct(
        public string $id,
        public ?string $refId,
        public string $error,
    ) {}
}
