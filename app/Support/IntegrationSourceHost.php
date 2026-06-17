<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Entegrasyon isteğinin "nereden geldiği" (ortağın sunucusu / origin hostu).
 * TEKSAT_INTEGRATION_ALLOWED_HOSTS ile eşleştirilir; funnel domain_name ile karıştırılmaz.
 */
final class IntegrationSourceHost
{
    public static function normalize(string $value): string
    {
        $h = strtolower(trim($value));
        $h = preg_replace('#^https?://#i', '', $h) ?? $h;
        $h = explode('/', $h, 2)[0];
        $h = explode(':', $h, 2)[0];

        return $h;
    }

    /**
     * Önce X-Teksat-Source-Host; yoksa Origin; yoksa Referer hostu.
     */
    public static function fromRequest(Request $request): ?string
    {
        $raw = $request->header('X-Teksat-Source-Host');
        if ($raw !== null && trim($raw) !== '') {
            return self::normalize($raw);
        }

        foreach (['Origin', 'Referer'] as $header) {
            $v = $request->header($header);
            if ($v === null || trim($v) === '') {
                continue;
            }
            if (! str_starts_with(strtolower($v), 'http')) {
                continue;
            }
            $host = parse_url($v, PHP_URL_HOST);
            if (is_string($host) && $host !== '') {
                return self::normalize($host);
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    public static function hostVariants(string $normalizedConfiguredHost): array
    {
        $root = preg_replace('/^www\./', '', $normalizedConfiguredHost) ?? $normalizedConfiguredHost;
        $withWww = str_starts_with($normalizedConfiguredHost, 'www.')
            ? $normalizedConfiguredHost
            : 'www.' . $root;

        return array_values(array_unique([
            $normalizedConfiguredHost,
            $root,
            $withWww,
        ]));
    }

    /**
     * @param list<string> $allowlistEntries .env'den gelen ham host satırları
     */
    public static function callerHostMatchesAllowlist(?string $normalizedRequestHost, array $allowlistEntries): bool
    {
        if ($normalizedRequestHost === null || $normalizedRequestHost === '') {
            return false;
        }

        foreach ($allowlistEntries as $entry) {
            $entry = trim((string) $entry);
            if ($entry === '') {
                continue;
            }
            $norm = self::normalize($entry);
            $variants = self::hostVariants($norm);
            if (in_array($normalizedRequestHost, $variants, true)) {
                return true;
            }
        }

        return false;
    }
}
