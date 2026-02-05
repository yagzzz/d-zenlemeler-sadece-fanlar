<?php

namespace App\Services\Media;

use App\Models\Content;
use App\Models\MediaAsset;
use App\Models\User;
use App\Services\AccessEngine\AccessEngine;
use App\Services\AccessEngine\AccessRequest;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MediaService
{
    public function __construct(
        private StorageDriver $storage,
        private AccessEngine $accessEngine
    ) {}

    /**
     * @return array{media_asset_id:int, upload: array{upload_url:string, headers?:array<string,string>, expires_at:string}}
     */
    public function initiateUpload(
        User $creator,
        string $type,
        string $mimeType,
        string $originalFilename,
        ?int $sizeBytes = null
    ): array {
        $this->authorizeCreator($creator);

        $objectKey = sprintf('creator/%d/%s', $creator->id, (string) Str::uuid());

        $asset = MediaAsset::create([
            'creator_id' => $creator->id,
            'content_id' => null,
            'type' => $type,
            'status' => 'pending',
            'provider' => (string) config('media.driver', 'local'),
            'bucket' => config('media.bucket'),
            'object_key' => $objectKey,
            'original_filename' => $originalFilename,
            'mime_type' => $mimeType,
            'size_bytes' => $sizeBytes,
        ]);

        $upload = $this->storage->createUploadUrl(
            $objectKey,
            $mimeType,
            (int) config('media.upload_expires_seconds', 900)
        );

        return [
            'media_asset_id' => $asset->id,
            'upload' => $upload,
        ];
    }

    public function attachToContent(User $creator, Content $content, int $mediaAssetId, int $position = 0): void
    {
        $this->authorizeCreator($creator);

        if ($content->creator_id !== $creator->id) {
            throw new AuthorizationException('Not allowed to attach media to this content.');
        }

        $asset = MediaAsset::query()->findOrFail($mediaAssetId);

        if ($asset->creator_id !== $creator->id) {
            throw new AuthorizationException('Not allowed to attach this media asset.');
        }

        if ($asset->status === 'deleted') {
            throw ValidationException::withMessages([
                'media_asset_id' => 'Media asset is deleted.',
            ]);
        }

        $content->mediaAssets()->syncWithoutDetaching([
            $asset->id => ['position' => $position],
        ]);

        if ($asset->status === 'pending') {
            $asset->update(['status' => 'ready']);
        }
    }

    /**
     * @return array{view_url:string, expires_at:string}
     */
    public function getSignedViewUrl(?User $user, MediaAsset $asset): array
    {
        $contents = $asset->contents()->get();

        if ($contents->isEmpty()) {
            if ($user === null || $user->id !== $asset->creator_id) {
                throw new AuthorizationException('media_unattached');
            }
        } else {
            $granted = false;
            $reason = null;

            foreach ($contents as $content) {
                $decision = $this->accessEngine->decide(
                    new AccessRequest($user, $content->visibility, $content->creator_id, $content->id)
                );

                if ($decision->granted) {
                    $granted = true;
                    break;
                }

                $reason = $decision->reason;
            }

            if (! $granted) {
                throw new AuthorizationException($reason ?? 'access_denied');
            }
        }

        return $this->storage->createViewUrl(
            $asset->object_key,
            (int) config('media.view_expires_seconds', 600)
        );
    }

    private function authorizeCreator(User $creator): void
    {
        if ($creator->role !== 'creator') {
            throw new AuthorizationException('Creator access required.');
        }
    }
}
