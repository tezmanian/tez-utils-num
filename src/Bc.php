<?php

declare(strict_types=1);

namespace Tez\Utils\Num;

use Tez\Utils\Validate\Guard;

/**
 * Arbitrary-precision decimal arithmetic helpers wrapping PHP's bcmath extension.
 *
 * All methods default to 8 decimal places, which is appropriate for financial
 * calculations. Every input must be a numeric string (e.g. "1.50000000").
 * Passing a non-numeric string to any method throws \InvalidArgumentException.
 */
final class Bc
{
    /**
     * Add two arbitrary-precision decimals.
     *
     * @param numeric-string $a
     * @param numeric-string $b
     * @throws \Throwable
     * @return numeric-string
     */
    public static function add(string $a, string $b, int $scale = 8): string
    {
        return bcadd(
            Guard::isNumeric($a),
            Guard::isNumeric($b),
            $scale,
        );
    }

    /**
     * Sum an array of arbitrary-precision decimals.
     *
     * Returns a correctly scaled zero ('0.00000000') for an empty array.
     *
     * @param numeric-string[] $operands
     * @throws \Throwable
     * @return numeric-string
     */
    public static function sum(array $operands, int $scale = 8): string
    {
        if ($operands === []) {
            return self::add('0', '0', $scale);
        }

        return array_reduce(
            $operands,
            static fn(string $carry, string $item) => self::add($carry, (string) $item, $scale),
            '0',
        );
    }

    /**
     * Subtract $b from $a.
     *
     * @param numeric-string $a
     * @param numeric-string $b
     * @throws \Throwable
     * @return numeric-string
     */
    public static function sub(string $a, string $b, int $scale = 8): string
    {
        return bcsub(
            Guard::isNumeric($a),
            Guard::isNumeric($b),
            $scale,
        );
    }

    /**
     * Multiply two arbitrary-precision decimals.
     *
     * @param numeric-string $a
     * @param numeric-string $b
     * @throws \Throwable
     * @return numeric-string
     */
    public static function mul(string $a, string $b, int $scale = 8): string
    {
        return bcmul(
            Guard::isNumeric($a),
            Guard::isNumeric($b),
            $scale,
        );
    }

    /**
     * Divide $a by $b.
     *
     * @param numeric-string $a
     * @param numeric-string $b
     * @throws \Throwable
     * @throws \DivisionByZeroError if $b is zero
     * @return numeric-string
     */
    public static function div(string $a, string $b, int $scale = 8): string
    {
        Guard::that(
            !self::isZero($b, $scale + 1),
            static fn() => new \DivisionByZeroError('Division by zero.'),
        );

        return bcdiv(Guard::isNumeric($a), $b, $scale);
    }

    /**
     * Normalize a numeric string to exactly $scale decimal places (truncates, does not round).
     *
     * Example: Bc::scale('1.5', 8) → '1.50000000'
     *
     * @param numeric-string $value
     * @throws \Throwable
     * @return numeric-string
     */
    public static function scale(string $value, int $scale = 8): string
    {
        return self::add(
            $value,
            '0',
            $scale,
        );
    }

    /**
     * Return the absolute value.
     *
     * @param numeric-string $value
     * @throws \Throwable
     * @return numeric-string
     */
    public static function abs(string $value, int $scale = 8): string
    {
        return self::isNegative($value, $scale)
            ? self::sub('0', $value, $scale)
            : self::add($value, '0', $scale);
    }

    /**
     * Return the larger of two values.
     *
     * @param numeric-string $a
     * @param numeric-string $b
     * @throws \Throwable
     * @return numeric-string
     */
    public static function max(string $a, string $b, int $scale = 8): string
    {
        return self::gte($a, $b, $scale)
            ? self::add($a, '0', $scale)
            : self::add($b, '0', $scale);
    }

    /**
     * Return the smaller of two values.
     *
     * @param numeric-string $a
     * @param numeric-string $b
     * @throws \Throwable
     * @return numeric-string
     */
    public static function min(string $a, string $b, int $scale = 8): string
    {
        return self::lte($a, $b, $scale)
            ? self::add($a, '0', $scale)
            : self::add($b, '0', $scale);
    }

    /**
     * Compare two values. Returns -1, 0, or 1.
     *
     * @param numeric-string $a
     * @param numeric-string $b
     * @throws \Throwable
     */
    public static function compare(string $a, string $b, int $scale = 8): int
    {
        return bccomp(
            Guard::isNumeric($a),
            Guard::isNumeric($b),
            $scale,
        );
    }

    /**
     * Return true when the value is exactly zero at the given scale.
     *
     * @param numeric-string $value
     * @throws \Throwable
     */
    public static function isZero(string $value, int $scale = 8): bool
    {
        return 0 === self::compare($value, '0', $scale);
    }

    /**
     * Return true when $a is greater than $b.
     *
     * @param numeric-string $a
     * @param numeric-string $b
     * @throws \Throwable
     */
    public static function gt(string $a, string $b, int $scale = 8): bool
    {
        return 1 === self::compare($a, $b, $scale);
    }

    /**
     * Return true when $a is less than $b.
     *
     * @param numeric-string $a
     * @param numeric-string $b
     * @throws \Throwable
     */
    public static function lt(string $a, string $b, int $scale = 8): bool
    {
        return -1 === self::compare($a, $b, $scale);
    }

    /**
     * Return true when $a is greater than or equal to $b.
     *
     * @param numeric-string $a
     * @param numeric-string $b
     * @throws \Throwable
     */
    public static function gte(string $a, string $b, int $scale = 8): bool
    {
        return self::compare($a, $b, $scale) >= 0;
    }

    /**
     * Return true when $a is less than or equal to $b.
     *
     * @param numeric-string $a
     * @param numeric-string $b
     * @throws \Throwable
     */
    public static function lte(string $a, string $b, int $scale = 8): bool
    {
        return self::compare($a, $b, $scale) <= 0;
    }

    /**
     * Return true when $a equals $b at the given scale.
     *
     * @param numeric-string $a
     * @param numeric-string $b
     * @throws \Throwable
     */
    public static function eq(string $a, string $b, int $scale = 8): bool
    {
        return 0 === self::compare($a, $b, $scale);
    }

    /**
     * Return true when $value is strictly greater than zero.
     *
     * @param numeric-string $value
     * @throws \Throwable
     */
    public static function isPositive(string $value, int $scale = 8): bool
    {
        return 1 === self::compare($value, '0', $scale);
    }

    /**
     * Return true when $value is strictly less than zero.
     *
     * @param numeric-string $value
     * @throws \Throwable
     */
    public static function isNegative(string $value, int $scale = 8): bool
    {
        return -1 === self::compare($value, '0', $scale);
    }

    /**
     * Negate a value (equivalent to multiplying by -1).
     *
     * @param numeric-string $value
     * @throws \Throwable
     * @return numeric-string
     */
    public static function negate(string $value, int $scale = 8): string
    {
        return self::sub(
            '0',
            $value,
            $scale,
        );
    }

    /**
     * Calculate $percent % of $value (i.e. value × percent ÷ 100).
     *
     * Example: Bc::percent('200', '15') → '30.00000000'
     *
     * @param numeric-string $value
     * @param numeric-string $percent
     * @throws \Throwable
     * @return numeric-string
     */
    public static function percent(string $value, string $percent, int $scale = 8): string
    {
        return self::div(
            self::mul(
                $value,
                $percent,
                $scale + 2,
            ),
            '100',
            $scale,
        );
    }

    /**
     * Multiply an array of arbitrary-precision decimals together.
     *
     * Returns '1.00000000' for an empty array (neutral element of multiplication).
     *
     * @param numeric-string[] $operands
     * @throws \Throwable
     * @return numeric-string
     */
    public static function product(array $operands, int $scale = 8): string
    {
        if ($operands === []) {
            return self::add('1', '0', $scale);
        }

        return array_reduce(
            $operands,
            static fn(string $carry, string $item) => self::mul($carry, (string) $item, $scale),
            '1',
        );
    }

    /**
     * Subtract all subsequent operands from the first.
     *
     * Returns '0.00000000' for an empty array.
     *
     * @param numeric-string[] $operands
     * @throws \Throwable
     * @return numeric-string
     */
    public static function subAll(array $operands, int $scale = 8): string
    {
        if ($operands === []) {
            return self::add('0', '0', $scale);
        }

        $first = (string) array_shift($operands);

        return array_reduce(
            $operands,
            static fn(string $carry, string $item) => self::sub($carry, (string) $item, $scale),
            $first,
        );
    }

    /**
     * Divide the first operand by each subsequent operand in turn.
     *
     * Returns '1.00000000' for an empty array (neutral element of division).
     *
     * @param numeric-string[] $operands
     * @throws \Throwable
     * @return numeric-string
     */
    public static function divAll(array $operands, int $scale = 8): string
    {
        if ($operands === []) {
            return self::add('1', '0', $scale);
        }

        $first = (string) array_shift($operands);

        return array_reduce(
            $operands,
            static fn(string $carry, string $item) => self::div($carry, (string) $item, $scale),
            $first,
        );
    }
}
