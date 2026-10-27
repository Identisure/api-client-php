<?php

declare(strict_types=1);

namespace Identisure\Client\Models\BankIdNo;

readonly class BankIdNoUnknown implements BankIdNoStatus
{
    public function __construct(
        public string $status,
    ) {}
}
