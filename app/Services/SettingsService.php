<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class SettingsService
{
    private const CACHE_KEY = 'app_settings';
    private const CACHE_TTL = 3600; // 1 hour

    /**
     * Get a setting value by key.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $settings = $this->all();

        if (! isset($settings[$key])) {
            return $default;
        }

        return $settings[$key];
    }

    /**
     * Set a setting value.
     */
    public function set(string $key, mixed $value): void
    {
        $setting = Setting::where('key', $key)->first();

        if ($setting) {
            $storedValue = $this->prepareValue($value, $setting->type);
            $setting->update(['value' => $storedValue]);
        }

        $this->clearCache();
    }

    /**
     * Get all settings for a group.
     */
    public function group(string $group): array
    {
        $settings = $this->all();
        $grouped = Setting::where('group', $group)->pluck('key')->toArray();

        return array_intersect_key($settings, array_flip($grouped));
    }

    /**
     * Get all settings as key => typed_value array.
     */
    public function all(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            try {
                if (! Schema::hasTable('settings')) {
                    return [];
                }

                return Setting::all()->mapWithKeys(function (Setting $setting) {
                    return [$setting->key => $setting->typed_value];
                })->toArray();
            } catch (\Throwable) {
                return [];
            }
        });
    }

    /**
     * Clear the settings cache.
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Prepare value for storage based on type.
     */
    private function prepareValue(mixed $value, string $type): ?string
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'boolean' => $value ? '1' : '0',
            'json'    => is_string($value) ? $value : json_encode($value),
            default   => (string) $value,
        };
    }
}
