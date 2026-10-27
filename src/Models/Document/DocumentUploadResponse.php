<?php

declare(strict_types=1);

namespace Identisure\Client\Models\Document;

readonly class DocumentUploadResponse
{
    public function __construct(
        public string $id,
        public string $hash,
    ) {}
}
