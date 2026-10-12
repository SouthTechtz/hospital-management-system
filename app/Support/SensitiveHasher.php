<?php

namespace App\Support;

/**
 * Deterministic hashing for encrypted values that still need exact-match
 * lookup (patient identity, duplicate detection, API keys, log in by phone).
 *
 * The HMAC key is derived from the application key, so hashes cannot be
 * recreated without it and are never reversible.
 */
final class SensitiveHasher
{
    public static function hash(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        return hash_hmac('sha256', trim($value), self::key());
    }

    public static function phone(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $value);
        if ($digits === '' || $digits === null) {
            return null;
        }

        // Normalise Tanzanian country code so 0712... and +255712... match.
        if (str_starts_with($digits, '255') && strlen($digits) === 12) {
            $digits = '0'.substr($digits, 3);
        }

        return self::hash($digits);
    }

    public static function email(?string $value): ?string
    {
        return $value === null ? null : self::hash(mb_strtolower(trim($value)));
    }

    private static function key(): string
    {
        $appKey = (string) config('app.key');

        if (str_starts_with($appKey, 'base64:')) {
            $appKey = base64_decode(substr($appKey, 7));
        }

        return $appKey !== '' ? $appKey : 'hms-sensitivity-key';
    }
}
