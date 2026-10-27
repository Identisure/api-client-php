<?php

declare(strict_types=1);

namespace Identisure\Client\Models\MitId;

readonly class MitIdSignRequest
{
    public function __construct(
        public string $text,
        public ?string $redirectUrl = null,
        public ?string $refId = null,
    ) {}
}
