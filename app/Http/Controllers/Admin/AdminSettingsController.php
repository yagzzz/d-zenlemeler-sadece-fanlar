<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\SettingsService;
use Illuminate\Http\Request;

class AdminSettingsController extends Controller
{
    public function __construct(
        private SettingsService $settingsService,
    ) {}

    /**
     * GET /admin/settings — List all settings grouped.
     */
    public function index(Request $request)
    {
        $settings = Setting::orderBy('group')->orderBy('key')->get()->groupBy('group');

        if ($request->wantsJson()) {
            return response()->json(['settings' => $settings]);
        }

        return view('admin.settings.index', compact('settings'));
    }

    /**
     * PUT /admin/settings — Bulk update settings.
     */
    public function update(Request $request)
    {
        $request->validate([
            'settings' => 'present|array',
            'settings.*' => 'nullable|string',
        ]);

        foreach ($request->input('settings', []) as $key => $value) {
            $setting = Setting::where('key', $key)->first();
            if (! $setting) {
                continue;
            }

            // Handle boolean toggle: checkboxes send '1' when checked, absent when unchecked
            if ($setting->type === 'boolean') {
                $value = $value ? '1' : '0';
            }

            $setting->update(['value' => $value]);
        }

        // Handle boolean settings that were NOT submitted (unchecked checkboxes)
        $booleanSettings = Setting::where('type', 'boolean')->pluck('key')->toArray();
        $submittedKeys = array_keys($request->input('settings', []));
        $uncheckedBooleans = array_diff($booleanSettings, $submittedKeys);

        foreach ($uncheckedBooleans as $key) {
            Setting::where('key', $key)->update(['value' => '0']);
        }

        $this->settingsService->clearCache();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Ayarlar güncellendi.']);
        }

        return redirect('/admin/settings')->with('success', 'Ayarlar güncellendi.');
    }
}
