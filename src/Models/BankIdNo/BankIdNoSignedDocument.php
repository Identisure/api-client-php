<?php

declare(strict_types=1);

namespace Identisure\Client\Models\BankIdNo;

readonly class BankIdNoSignedDocument
{
    public function __construct(
        public string $id,
        public string $hash,
    ) {}
}
