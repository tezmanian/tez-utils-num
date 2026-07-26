<?php

declare(strict_types=1);

namespace Tez\Utils\Num;

/**
 * Number formatting helpers: decimal, byte sizes, percentages, and rounding.
 */
final class Num
{
    private const BYTE_UNITS = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];

    /**
     * Format a number with grouped thousands and a configurable decimal separator.
     */
    public static function format(
        int|float $value,
        int $decimals = 2,
        string $dec = '.',
        string $thousands = ',',
    ): string {
        return number_format((float) $value, $decimals, $dec, $thousands);
    }

    /**
     * Format a byte count as a human-readable string using binary units (1 KB = 1024 B).
     *
     * @param int<0, max> $bytes
     */
    public static function bytes(int $bytes, int $precision = 2): string
    {
        if ($bytes < 1024) {
            return self::format($bytes, 0) . ' B';
        }

        $exp   = (int) floor(log($bytes, 1024));
        $exp   = min($exp, count(self::BYTE_UNITS) - 1);
        $value = $bytes / (1024 ** $exp);
        $unit  = self::BYTE_UNITS[$exp];

        return self::format($value, $precision) . ' ' . $unit;
    }

    /**
     * Format a ratio (0.0–1.0) as a percentage string.
     *
     * Example: 0.1234 → '12.3%'
     */
    public static function percent(int|float $ratio, int $decimals = 1): string
    {
        return self::format((float) $ratio * 100, $decimals) . '%';
    }

    /**
     * Clamp a value to the inclusive range [$min, $max].
     */
    public static function clamp(int|float $value, int|float $min, int|float $max): int|float
    {
        if ($value < $min) {
            return $min;
        }

        if ($value > $max) {
            return $max;
        }

        return $value;
    }

    /**
     * Round a value to the nearest multiple of $multiple.
     *
     * Examples: roundTo(17, 5) → 15, roundTo(18, 5) → 20, roundTo(1.7, 0.5) → 1.5
     */
    public static function roundTo(int|float $value, int|float $multiple): int|float
    {
        if ($multiple === 0 || $multiple === 0.0) {
            return $value;
        }

        $rounded = round((float) $value / (float) $multiple) * (float) $multiple;

        if (is_int($value) && is_int($multiple)) {
            return (int) $rounded;
        }

        return $rounded;
    }
}
