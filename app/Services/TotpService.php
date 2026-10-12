<?php

namespace App\Services;

use App\Support\Base32;

/**
 * Time-based One-Time Password (RFC 6238) used for TOTP MFA (Part B).
 * Implemented directly so there is no third-party runtime dependency.
 */
class TotpService
{
    public function generateSecret(int $bytes = 20): string
    {
        return Base32::encode(random_bytes($bytes));
    }

    public function verify(string $secret, string $code, ?int $window = null, int $period = 30): bool
    {
        $code = preg_replace('/\s+/', '', $code) ?? '';
        if (! ctype_digit($code) || strlen($code) !== 6) {
            return false;
        }

        $window ??= (int) config('hms.mfa.window', 1);
        $counter = (int) floor(time() / $period);

        for ($offset = -$window; $offset <= $window; $offset++) {
            if (hash_equals($this->code($secret, $counter + $offset, $period), $code)) {
                return true;
            }
        }

        return false;
    }

    public function code(string $secret, int $counter, int $period = 30, int $digits = 6): string
    {
        $key = Base32::decode($secret);
        $binary = pack('N*', 0).pack('N*', $counter);
        $hash = hash_hmac('sha1', $binary, $key, true);

        $offset = ord($hash[19]) & 0x0F;
        $value = ((ord($hash[$offset]) & 0x7F) << 24)
            | ((ord($hash[$offset + 1]) & 0xFF) << 16)
            | ((ord($hash[$offset + 2]) & 0xFF) << 8)
            | (ord($hash[$offset + 3]) & 0xFF);

        $otp = $value % (10 ** $digits);

        return str_pad((string) $otp, $digits, '0', STR_PAD_LEFT);
    }

    /**
     * otpauth URI for authenticator apps.
     */
    public function provisioningUri(string $accountName, string $secret, ?string $issuer = null): string
    {
        $issuer ??= (string) config('hms.mfa.issuer', config('app.name'));
        $label = rawurlencode($issuer.':'.$accountName);

        $params = http_build_query([
            'secret' => $secret,
            'issuer' => $issuer,
            'algorithm' => 'SHA1',
            'digits' => 6,
            'period' => 30,
        ]);

        return 'otpauth://totp/'.$label.'?'.$params;
    }

    /**
     * Human-readable grouping of the secret for manual entry.
     */
    public function readableSecret(string $secret): string
    {
        return trim(chunk_split($secret, 4, ' '));
    }
}
