<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    public const ACTIVE_CDN_BASE_URL_CACHE_KEY = 'active_cdn_base_url';

    protected $fillable = ['group', 'key', 'value', 'is_sensitive'];

    /**
     * Helper to get a setting value quickly
     */
    public static function val($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public static function mediaUrl(?string $path, ?string $default = null, ?string $forcedBaseUrl = null): ?string
    {
        if (! filled($path)) {
            return $default;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '//')) {
            return $path;
        }

        $normalizedPath = ltrim($path, '/');
        $cdnBaseUrl = $forcedBaseUrl !== null ? rtrim($forcedBaseUrl, '/') : self::activeCdnBaseUrlFromCache();

        $provider = strtolower((string) self::val('cdn_provider', 'none'));
        if ($cdnBaseUrl === '') {
            $cdnBaseUrl = rtrim((string) self::val('cdn_base_url', ''), '/');
        }

        if ($provider === 'bunny' && $cdnBaseUrl === '') {
            $bunnyHost = trim((string) self::val('bunny_cdn_hostname', ''));
            $bunnyHost = preg_replace('#^https?://#', '', $bunnyHost);
            $bunnyHost = trim((string) $bunnyHost, '/');
            if ($bunnyHost !== '') {
                $cdnBaseUrl = 'https://'.$bunnyHost;
            }
        }

        if (in_array($cdnBaseUrl, ['https://b-cdn.net', 'http://b-cdn.net'], true)) {
            $cdnBaseUrl = '';
        }

        if ($cdnBaseUrl !== '') {
            return $cdnBaseUrl.'/'.$normalizedPath;
        }

        return asset($normalizedPath);
    }

    public static function clearActiveCdnCache(): void
    {
        Cache::forget(self::ACTIVE_CDN_BASE_URL_CACHE_KEY);
    }

    private static function activeCdnBaseUrlFromCache(): string
    {
        try {
            return Cache::remember(self::ACTIVE_CDN_BASE_URL_CACHE_KEY, now()->addMinutes(30), function (): string {
                $activeProvider = CdnProvider::query()->where('is_active', true)->first();
                if ($activeProvider && filled($activeProvider->base_url)) {
                    return rtrim((string) $activeProvider->base_url, '/');
                }

                return '';
            });
        } catch (\Throwable $e) {
            // Migration/cache driver sorununda fallback calisir.
            return '';
        }
    }
}
