<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Breach-list password check (Part B) using the Pwned Passwords range API
 * (k-anonymity: only the first five SHA-1 hex characters leave this server).
 * If the service is unreachable the check fails open with a warning log entry
 * (assumption recorded in ASSUMPTIONS.md).
 */
class NotBreachedPassword implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! config('hms.password.breach_check', true)) {
            return;
        }

        $sha1 = strtoupper(sha1($value));
        $prefix = substr($sha1, 0, 5);
        $suffix = substr($sha1, 5);

        try {
            $response = Http::timeout(5)
                ->withHeaders(['Accept' => 'text/plain'])
                ->get('https://api.pwnedpasswords.com/range/'.$prefix);

            if ($response->successful()) {
                $suffixFound = collect(explode("\n", $response->body()))
                    ->map(function (string $line) {
                        [$hashSuffix] = explode(':', trim($line));

                        return $hashSuffix;
                    })
                    ->contains($suffix);

                if ($suffixFound) {
                    $fail(__('validation.password_breached'));
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Pwned passwords service unreachable, breach check skipped', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}