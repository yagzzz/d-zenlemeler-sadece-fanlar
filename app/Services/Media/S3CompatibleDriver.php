<?php

namespace App\Services\Media;

use RuntimeException;

class S3CompatibleDriver implements StorageDriver
{
    public function createUploadUrl(string $objectKey, string $mimeType, int $expiresSeconds): array
    {
        throw new RuntimeException('S3CompatibleDriver not implemented. TODO: generate presigned upload URL.');
    }

    public function createViewUrl(string $objectKey, int $expiresSeconds): array
    {
        throw new RuntimeException('S3CompatibleDriver not implemented. TODO: generate presigned view URL.');
    }
}
