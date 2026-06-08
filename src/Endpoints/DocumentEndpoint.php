<?php

declare(strict_types=1);

namespace Idkollen\Client\Endpoints;

use Idkollen\Client\Models\Document\DocumentUploadResponse;
use Idkollen\Client\Transport;

final class DocumentEndpoint
{
    public function __construct(private readonly Transport $transport) {}

    public function upload(string $data, string $filename, string $mimeType = 'application/pdf'): DocumentUploadResponse
    {
        $d = $this->transport->postMultipart('/document', $data, $filename, $mimeType);
        return new DocumentUploadResponse(id: $d['id'], hash: $d['hash']);
    }

    public function download(string $id): string
    {
        return $this->transport->getRaw('/document/' . $id);
    }

    public function delete(string $id): void
    {
        $this->transport->delete('/document/' . $id);
    }
}
