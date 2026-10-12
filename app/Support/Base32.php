<?php

namespace App\Support;

/**
 * RFC 4648 Base32 encoder/decoder used for TOTP secrets.
 */
final class Base32
{
    private const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    public static function encode(string $bytes): string
    {
        if ($bytes === '') {
            return '';
        }

        $bits = '';
        foreach (str_split($bytes) as $char) {
            $bits .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
        }

        $output = '';
        foreach (str_split($bits, 5) as $chunk) {
            $chunk = str_pad($chunk, 5, '0', STR_PAD_RIGHT);
            $output .= self::ALPHABET[bindec($chunk)];
        }

        return $output;
    }

    public static function decode(string $base32): string
    {
        $base32 = strtoupper(preg_replace('/[^A-Z2-7]/i', '', $base32) ?? '');

        if ($base32 === '') {
            return '';
        }

        $bits = '';
        foreach (str_split($base32) as $char) {
            $index = strpos(self::ALPHABET, $char);
            if ($index === false) {
                continue;
            }
            $bits .= str_pad(decbin($index), 5, '0', STR_PAD_LEFT);
        }

        $output = '';
        foreach (str_split($bits, 8) as $byte) {
            if (strlen($byte) < 8) {
                break;
            }
            $output .= chr(bindec($byte));
        }

        return $output;
    }
}
