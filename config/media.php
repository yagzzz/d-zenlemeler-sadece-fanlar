<?php

return [
    'driver' => env('MEDIA_DRIVER', 'local'),
    'bucket' => env('MEDIA_BUCKET'),
    'upload_expires_seconds' => (int) env('MEDIA_UPLOAD_EXPIRES_SECONDS', 900),
    'view_expires_seconds' => (int) env('MEDIA_VIEW_EXPIRES_SECONDS', 600),
    's3' => [
        'endpoint' => env('MEDIA_S3_ENDPOINT'),
        'region' => env('MEDIA_S3_REGION', 'us-east-1'),
        'access_key' => env('MEDIA_S3_ACCESS_KEY'),
        'secret_key' => env('MEDIA_S3_SECRET_KEY'),
        'bucket' => env('MEDIA_S3_BUCKET'),
    ],
];
