<?php

use App\Services\SettingsService;

if (! function_exists('settings')) {
    /**
     * Get a site setting by key, or the SettingsService instance.
     *
     * @param  string|null  $key
     * @param  mixed        $default
     * @return mixed
     */
    function settings(?string $key = null, mixed $default = null): mixed
    {
        $service = app(SettingsService::class);

        if ($key === null) {
            return $service;
        }

        return $service->get($key, $default);
    }
}
