<?php

namespace App\Services\Media;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class LocalSignedDriver implements StorageDriver
{
    public function createUploadUrl(string $objectKey, string $mimeType, int $expiresSeconds): array
    {
        $expiresAt = Carbon::now()->addSeconds($expiresSeconds);
        $token = $this->signToken($objectKey, $expiresAt->timestamp, 'upload');

        return [
            'upload_url' => url('/media/upload/'.$token),
            'headers' => [
                'Content-Type' => $mimeType,
            ],
            'expires_at' => $expiresAt->toISOString(),
        ];
    }

    public function createViewUrl(string $objectKey, int $expiresSeconds): array
    {
        $expiresAt = Carbon::now()->addSeconds($expiresSeconds);
        $token = $this->signToken($objectKey, $expiresAt->timestamp, 'view');

        return [
            'view_url' => url('/media/view/'.$token),
            'expires_at' => $expiresAt->toISOString(),
        ];
    }

    private function signToken(string $objectKey, int $expiresAt, string $mode): string
    {
        $payload = [
            'object_key' => $objectKey,
            'exp' => $expiresAt,
            'mode' => $mode,
        ];

        $encoded = $this->base64UrlEncode(json_encode($payload));
        $signature = hash_hmac('sha256', $encoded, $this->signingKey());

        return $encoded.'.'.$signature;
    }

    private function signingKey(): string
    {
        $key = (string) config('app.key');

        if ($key === '') {
            return 'local';
        }

        if (Str::startsWith($key, 'base64:')) {
            $decoded = base64_decode(substr($key, 7));

            return $decoded === false ? 'local' : $decoded;
        }

        return $key;
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
