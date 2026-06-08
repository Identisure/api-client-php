<?php

declare(strict_types=1);

namespace Idkollen\Client\Models\BankIdSe;

readonly class BankIdSePending implements BankIdSeStatus
{
    public function __construct(
        public string $id,
        public ?string $refId,
        public ?string $autoStartToken,
        public ?string $qrStartToken,
        public ?string $qrStartSecret,
        public ?string $hintCode,
    ) {}
}
