<?php

/*
|--------------------------------------------------------------------------
| HMS configuration
|--------------------------------------------------------------------------
|
| Every value in this file is read from the environment. External
| specifications and credentials are NEVER hardcoded; anything that must be
| confirmed with a national system is recorded in docs/ASSUMPTIONS.md with a
| VERIFY_WITH_PROVIDER marker and a mock adapter (see config/integrations.php).
|
*/

return [

    'facility' => [
        'code' => env('HMS_FACILITY_CODE', 'RHH001'),
        'timezone' => env('APP_TIMEZONE', 'Africa/Dar_es_Salaam'),
    ],

    'locale' => [
        'supported' => ['en', 'sw'],
        'default' => env('APP_LOCALE', 'en'),
    ],

    'session' => [
        'idle_minutes' => (int) env('HMS_SESSION_IDLE_MINUTES', 15),
        'absolute_hours' => (int) env('HMS_SESSION_ABSOLUTE_HOURS', 12),
        'concurrent_limit' => (int) env('HMS_SESSION_CONCURRENT_LIMIT', 3),
    ],

    'password' => [
        'min_length' => (int) env('HMS_PASSWORD_MIN_LENGTH', 12),
        'breach_check' => env('HMS_PASSWORD_BREACH_CHECK', true),
        'max_attempts' => 5,
        'lockout_minutes' => 15,
    ],

    /*
    | MFA is mandatory for these clinical actions regardless of role, in
    | addition to any role flagged as requiring MFA.
    */
    'mfa' => [
        'required_actions' => array_filter(explode(',', (string) env('HMS_MFA_REQUIRED_ACTIONS', 'admin,prescribe'))),
        'issuer' => env('HMS_MFA_ISSUER', 'Tanzania RHH HMS'),
        'window' => 1,
    ],

    'break_glass' => [
        'duration_minutes' => (int) env('HMS_BREAK_GLASS_MINUTES', 60),
        'requires_reason' => true,
        'notify_privacy_officer' => true,
    ],

    'audit' => [
    // Columns that must never be written to logs or error reports.
        'redact' => [
            'password',
            'password_confirmation',
            'current_password',
            'token',
            'authorization',
            'x-service-token',
            'secret',
            'api_key',
            'national_id',
            'nida_number',
            'nhif_number',
            'phone',
            'next_of_kin_phone',
            'mfa_secret',
            'memory',
        ],
    ],

    'clinical' => [
    // Clinical reads must hit the primary unless replica lag is below this.
        'replica_max_lag_seconds' => (int) env('HMS_CLINICAL_REPLICA_MAX_LAG_SECONDS', 2),
        'dt_formats' => [
            'date' => 'd/m/Y',
            'time' => 'H:i',
            'datetime' => 'd/m/Y H:i',
        ],
    ],

    'mrn' => [
    // Prefix and zero-padded width of the primary MRN sequence.
        'prefix' => env('HMS_MRN_PREFIX', 'MRN'),
        'sequence_width' => (int) env('HMS_MRN_SEQUENCE_WIDTH', 7),
    ],

    'attachments' => [
        'allowed_mime' => ['image/jpeg', 'image/png', 'application/pdf'],
        'max_kilobytes' => 4096,
        'disk' => 'private',
    ],

    'banned' => [
    /*
    | Automated prohibition scan (Part D2 + Part F). These patterns must never
    | appear in the repository. The `php artisan hms:scan-banned` command and
    | the test suite enforce this.
    */
        'unicode' => '/[\x{1F000}-\x{1FAFF}\x{2600}-\x{27BF}\x{2B00}-\x{2BFF}\x{FE0F}\x{2190}-\x{21FF}]/u',
        'phrases' => [
            'ask ai', 'ai assistant', 'ai-powered', 'powered by ai', 'magic wand',
            'sparkle', 'good morning', 'good afternoon', 'good evening',
            'our team', 'welcome back, doctor', "i found", 'fun fact', 'congratulations',
        ],
    ],

];
