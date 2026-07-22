<?php

declare(strict_types=1);

namespace Idkollen\Client\Models\BankIdSe;

readonly class BankIdSeStepUp
{
    public function __construct(
        public ?bool $mrtd,
        public ?bool $face,
    ) {}
}
