<?php

declare(strict_types=1);

namespace Identisure\Client\Models\MitId;

readonly class MitIdBackchannelAuthRequest
{
    public function __construct(
        public string $ssn,
        public string $bindingMessage,
        public ?string $callbackUrl = null,
        public ?string $refId = null,
    ) {}
}
