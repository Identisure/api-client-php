<?php

declare(strict_types=1);

namespace Identisure\Client;

use Identisure\Client\Endpoints\BankIdNoEndpoint;
use Identisure\Client\Endpoints\BankIdSeEndpoint;
use Identisure\Client\Endpoints\DocumentEndpoint;
use Identisure\Client\Endpoints\FrejaEndpoint;
use Identisure\Client\Endpoints\FtnEndpoint;
use Identisure\Client\Endpoints\MitIdEndpoint;
use Identisure\Client\Endpoints\VippsEndpoint;

final class IdentisureClient
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
