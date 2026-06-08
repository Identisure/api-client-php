<?php

declare(strict_types=1);

namespace Idkollen\Client\Models\MitId;

readonly class MitIdPending implements MitIdStatus
{
    public function __construct(
        public string $id,
        public ?string $refId,
        public ?string $url,
        public ?string $bindingMessage,
    ) {}
}
