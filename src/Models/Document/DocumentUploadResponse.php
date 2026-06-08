<?php

declare(strict_types=1);

namespace Idkollen\Client\Models\Document;

readonly class DocumentUploadResponse
{
    public function __construct(
        public string $id,
        public string $hash,
    ) {}
}
