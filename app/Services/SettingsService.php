<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;

class SettingsService
{
    public function get(string $key, mixed $default = null): mixed
    {
        $setting = Cache::rememberForever('system.setting.'.$key, function () use ($key) {
            return SystemSetting::where('key', $key)->first();
        });

        if ($setting === null) {
            return $default;
        }

        return match ($setting->type) {
            'int' => (int) $setting->value,
            'bool' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode((string) $setting->value, true),
            default => $setting->value,
        };
    }

    public function set(string $key, mixed $value, string $group = 'general', string $type = 'string', string $verification = 'confirmed', ?int $userId = null): SystemSetting
    {
        $setting = SystemSetting::updateOrCreate(
            ['key' => $key],
            [
                'value' => is_array($value) ? json_encode($value) : (string) $value,
                'group' => $group,
                'type' => $type,
                'verification_status' => $verification,
                'updated_by' => $userId,
            ]
        );

        Cache::forget('system.setting.'.$key);

        return $setting;
    }
}
