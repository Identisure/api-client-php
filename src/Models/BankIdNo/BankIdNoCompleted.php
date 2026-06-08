<?php

declare(strict_types=1);

namespace Idkollen\Client\Models\BankIdNo;

readonly class BankIdNoCompleted implements BankIdNoStatus
{
    public function __construct(
        public string $id,
        public ?string $refId,
        public string $ssn,
        public string $name,
        public string $givenName,
        public string $surname,
        public ?string $phone,
        public ?string $email,
        public ?string $address,
        public ?string $birthDate,
        public ?string $pid,
        public ?string $bankId,
        public ?BankIdNoSignResult $signResult,
        /** @var list<BankIdNoSignedDocument> */
        public array $signedDocuments,
    ) {}
}
