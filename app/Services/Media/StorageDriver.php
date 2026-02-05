<?php

namespace App\Services\Media;

interface StorageDriver
{
    /**
     * @return array{upload_url: string, headers?: array<string,string>, expires_at: string}
     */
    public function createUploadUrl(string $objectKey, string $mimeType, int $expiresSeconds): array;

    /**
     * @return array{view_url: string, expires_at: string}
     */
    public function createViewUrl(string $objectKey, int $expiresSeconds): array;
}
