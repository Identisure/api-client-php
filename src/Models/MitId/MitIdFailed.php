<?php

declare(strict_types=1);

namespace Identisure\Client\Models\MitId;

readonly class MitIdFailed implements MitIdStatus
{
    public function __construct(
        public string $id,
        public ?string $refId,
        public string $error,
    ) {}
}
