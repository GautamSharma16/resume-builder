<?php

namespace App\Support;

final class Utf8Sanitizer
{
    /**
     * Recursively clean strings in arrays for JSON / database storage.
     */
    public static function clean(mixed $value): mixed
    {
        if (is_string($value)) {
            return self::cleanString($value);
        }

        if (is_array($value)) {
            $cleaned = [];
            foreach ($value as $key => $item) {
                $cleanedKey = is_string($key) ? self::cleanString($key) : $key;
                $cleaned[$cleanedKey] = self::clean($item);
            }

            return $cleaned;
        }

        return $value;
    }

    public static function cleanString(string $text): string
    {
        if ($text === '') {
            return '';
        }

        $text = str_replace("\0", '', $text);
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $text) ?? '';

        if ($text === '') {
            return '';
        }

        if (function_exists('iconv')) {
            $converted = @iconv('UTF-8', 'UTF-8//IGNORE', $text);
            if ($converted !== false) {
                $text = $converted;
            }
        }

        if (function_exists('mb_convert_encoding')) {
            $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        }

        if (function_exists('mb_check_encoding') && ! mb_check_encoding($text, 'UTF-8')) {
            if (function_exists('mb_scrub')) {
                $text = mb_scrub($text, 'UTF-8');
            } else {
                $text = (string) preg_replace('/[\x80-\xFF]/', '', $text);
            }
        }

        return trim($text);
    }

    /**
     * Ensure value can be json_encoded without InvalidArgumentException.
     */
    public static function jsonSafe(mixed $value): mixed
    {
        $cleaned = self::clean($value);

        try {
            json_encode($cleaned, JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE);
        } catch (\JsonException) {
            return self::clean($cleaned);
        }

        return $cleaned;
    }
}
