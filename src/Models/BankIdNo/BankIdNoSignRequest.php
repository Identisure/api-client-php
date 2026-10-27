<?php

declare(strict_types=1);

namespace Identisure\Client\Models\BankIdNo;

readonly class BankIdNoSignRequest
{
    public function __construct(
        public ?string $redirectUrl = null,
        public ?string $text = null,
        /** @var list<string>|null */
        public ?array $documents = null,
        public ?string $refId = null,
    ) {}
}
