<?php

/*
|--------------------------------------------------------------------------
| Feature Flags
|--------------------------------------------------------------------------
|
| Each flag reads an env var first (FEATURES_<FLAG>). When the env var is
| not set the default depends on the application environment:
|   local  → true   (developer convenience)
|   other  → false  (production safety)
|
*/

$local = in_array(env('APP_ENV', 'production'), ['local', 'testing']);

return [
    'flags' => [
        'access_engine'        => env('FEATURES_ACCESS_ENGINE', $local),
        'analytics_stub'       => env('FEATURES_ANALYTICS_STUB', $local),
        'creator_applications' => env('FEATURES_CREATOR_APPLICATIONS', $local),
        'content_core'         => env('FEATURES_CONTENT_CORE', $local),
        'content_show'         => env('FEATURES_CONTENT_SHOW', $local),
        'feed'                 => env('FEATURES_FEED', $local),
        'payments_core'        => env('FEATURES_PAYMENTS_CORE', $local),
        'payments'             => env('FEATURES_PAYMENTS', $local),
        'ppv_core'             => env('FEATURES_PPV_CORE', $local),
        'ppv'                  => env('FEATURES_PPV', $local),
        'media_core'           => env('FEATURES_MEDIA_CORE', $local),
        'creator_profile'      => env('FEATURES_CREATOR_PROFILE', $local),
        'tiers'                => env('FEATURES_TIERS', $local),
        'tier_ux'              => env('FEATURES_TIER_UX', $local),
        'tips'                 => env('FEATURES_TIPS', $local),
        'ui'                   => env('FEATURES_UI', $local),
        'ui_polish'            => env('FEATURES_UI_POLISH', $local),
        'comments'             => env('FEATURES_COMMENTS', $local),
        'reactions'            => env('FEATURES_REACTIONS', $local),
        'bookmarks'            => env('FEATURES_BOOKMARKS', $local),
        'notifications'        => env('FEATURES_NOTIFICATIONS', $local),
    ],
];
