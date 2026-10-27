<?php

declare(strict_types=1);

namespace Identisure\Client\Endpoints;

use Identisure\Client\Models\Document\DocumentUploadResponse;
use Identisure\Client\Transport;

final class DocumentEndpoint
{
    public function __construct(private readonly Transport $transport) {}

    public function upload(string $data, string $filename, string $mimeType = 'application/pdf'): DocumentUploadResponse
    {
        $d = $this->transport->postMultipart('/v3/document', $data, $filename, $mimeType);
        return new DocumentUploadResponse(id: $d['id'], hash: $d['hash']);
    }

    public function download(string $id): string
    {
        return $this->transport->getRaw('/v3/document/' . $id);
    }
}
