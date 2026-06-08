<?php

declare(strict_types=1);

namespace Idkollen\Client;

use Idkollen\Client\Endpoints\BankIdNoEndpoint;
use Idkollen\Client\Endpoints\BankIdSeEndpoint;
use Idkollen\Client\Endpoints\DocumentEndpoint;
use Idkollen\Client\Endpoints\FrejaEndpoint;
use Idkollen\Client\Endpoints\FtnEndpoint;
use Idkollen\Client\Endpoints\MitIdEndpoint;
use Idkollen\Client\Endpoints\VippsEndpoint;

final class IdkollenClient
{
    public function __construct(
        private readonly Transport $transport,
    ) {}

    public function bankIdSe(): BankIdSeEndpoint
    {
        return new BankIdSeEndpoint($this->transport);
    }

    public function bankIdNo(): BankIdNoEndpoint
    {
        return new BankIdNoEndpoint($this->transport);
    }

    public function freja(): FrejaEndpoint
    {
        return new FrejaEndpoint($this->transport);
    }

    public function mitId(): MitIdEndpoint
    {
        return new MitIdEndpoint($this->transport);
    }

    public function ftn(): FtnEndpoint
    {
        return new FtnEndpoint($this->transport);
    }

    public function vipps(): VippsEndpoint
    {
        return new VippsEndpoint($this->transport);
    }

    public function document(): DocumentEndpoint
    {
        return new DocumentEndpoint($this->transport);
    }
}
