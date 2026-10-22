<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Generic key/value application settings.
 *
 * The whole table is cached under a single key so that reading a setting on a
 * public request (e.g. the registration form) costs no query.
 */
class AppSetting extends Model
{
    public const CACHE_KEY = 'app_settings.all';

    protected $table = 'app_settings';
    protected $primaryKey = 'setting_id';

    protected $fillable = [
        'setting_key',
        'setting_value',
        'setting_type',
        'setting_group',
        'description',
        'updated_by',
    ];

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    /**
     * All settings as [key => ['value' => ..., 'type' => ...]].
     *
     * This is read on the public registration form, so a missing table (code
     * deployed ahead of the migration) or a database hiccup must not take the
     * page down — callers fall back to their defaults instead.
     */
    public static function allSettings(): array
    {
        try {
            return Cache::rememberForever(self::CACHE_KEY, function () {
                return static::query()
                    ->get(['setting_key', 'setting_value', 'setting_type'])
                    ->keyBy('setting_key')
                    ->map(fn ($row) => [
                        'value' => $row->setting_value,
                        'type' => $row->setting_type,
                    ])
                    ->toArray();
            });
        } catch (\Throwable $e) {
            Log::warning('AppSetting: could not read settings, using defaults. ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Read a single setting, cast according to its declared type.
     */
    public static function get(string $key, $default = null)
    {
        $settings = static::allSettings();

        if (!array_key_exists($key, $settings)) {
            return $default;
        }

        return static::castValue($settings[$key]['value'], $settings[$key]['type']);
    }

    /**
     * Create or update a setting. Cache is flushed by the model events.
     */
    public static function set(string $key, $value, ?string $type = null, ?string $group = null): void
    {
        $setting = static::firstOrNew(['setting_key' => $key]);

        $setting->setting_type = $type ?? $setting->setting_type ?? 'string';
        $setting->setting_group = $group ?? $setting->setting_group ?? 'general';
        $setting->setting_value = static::serialiseValue($value, $setting->setting_type);
        $setting->updated_by = auth()->check() ? auth()->user()->sysuser_id : null;
        $setting->save();
    }

    /**
     * Is the public (non school-link) registration form accepting enrolments?
     */
    public static function registrationIsOpen(): bool
    {
        return (bool) static::get('registration.open', true);
    }

    /**
     * Admin-authored note about the next batch, in the requested language,
     * falling back to English.
     */
    public static function nextBatchNote(?string $lang = null): ?string
    {
        $lang = strtolower($lang ?: app()->getLocale());

        if (!in_array($lang, ['en', 'hi', 'gu'], true)) {
            $lang = 'en';
        }

        $note = static::get("registration.next_batch_note_{$lang}");

        if (blank($note) && $lang !== 'en') {
            $note = static::get('registration.next_batch_note_en');
        }

        return blank($note) ? null : $note;
    }

    protected static function castValue($value, ?string $type)
    {
        return match ($type) {
            'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'int' => (int) $value,
            'json' => json_decode((string) $value, true),
            default => $value,
        };
    }

    protected static function serialiseValue($value, ?string $type): ?string
    {
        return match ($type) {
            'bool' => $value ? '1' : '0',
            'int' => (string) (int) $value,
            'json' => json_encode($value),
            default => $value === null ? null : (string) $value,
        };
    }
}
