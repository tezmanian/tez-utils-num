<?php

declare(strict_types=1);

namespace Tez\Utils\Num;

use Tez\Utils\Validate\Guard;

/**
 * Arbitrary-precision decimal arithmetic helpers wrapping PHP's bcmath extension.
 *
 * All methods default to 8 decimal places, which is appropriate for financial
 * calculations. Inputs may be int, float, or numeric string.
 * Passing a non-numeric string to any method throws \InvalidArgumentException.
 */
final class Bc
{
    /**
     * Add two arbitrary-precision decimals.
     *
     * @throws \Throwable
     * @return numeric-string
     */
    public static function add(int|float|string $a, int|float|string $b, int $scale = 8): string
    {
        return bcadd(
            Guard::isNumeric((string) $a), // @phpstan-ignore-line
            Guard::isNumeric((string) $b), // @phpstan-ignore-line
            $scale,
        );
    }

    /**
     * Sum an array of arbitrary-precision decimals.
     *
     * Returns a correctly scaled zero ('0.00000000') for an empty array.
     *
     * @param array<int|float|string> $operands
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
            static fn(string $carry, int|float|string $item) => self::add($carry, (string) $item, $scale),
            '0',
        );
    }

    /**
     * Subtract $b from $a.
     *
     * @throws \Throwable
     * @return numeric-string
     */
    public static function sub(int|float|string $a, int|float|string $b, int $scale = 8): string
    {
        return bcsub(
            Guard::isNumeric((string) $a), // @phpstan-ignore-line
            Guard::isNumeric((string) $b), // @phpstan-ignore-line
            $scale,
        );
    }

    /**
     * Multiply two arbitrary-precision decimals.
     *
     * @throws \Throwable
     * @return numeric-string
     */
    public static function mul(int|float|string $a, int|float|string $b, int $scale = 8): string
    {
        return bcmul(
            Guard::isNumeric((string) $a), // @phpstan-ignore-line
            Guard::isNumeric((string) $b), // @phpstan-ignore-line
            $scale,
        );
    }

    /**
     * Divide $a by $b.
     *
     * @throws \Throwable
     * @throws \DivisionByZeroError if $b is zero
     * @return numeric-string
     */
    public static function div(int|float|string $a, int|float|string $b, int $scale = 8): string
    {
        Guard::that(
            !self::isZero($b, $scale + 1),
            static fn() => new \DivisionByZeroError('Division by zero.'),
        );

        return bcdiv(Guard::isNumeric((string) $a), (string) $b, $scale);
    }

    /**
     * Normalize a numeric value to exactly $scale decimal places (truncates, does not round).
     *
     * Example: Bc::scale(1.5, 8) ₒ '1.50000000'
     *
     * @throws \Throwable
     * @return numeric-string
     */
    public static function scale(int|float|string $value, int $scale = 8): string
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
     * @throws \Throwable
     * @return numeric-string
     */
    public static function abs(int|float|string $value, int $scale = 8): string
    {
        return self::isNegative($value, $scale)
            ? self::sub('0', $value, $scale)
            : self::add($value, '0', $scale);
    }

    /**
     * Return the larger of two values.
     *
     * @throws \Throwable
     * @return numeric-string
     */
    public static function max(int|float|string $a, int|float|string $b, int $scale = 8): string
    {
        return self::gte($a, $b, $scale)
            ? self::add($a, '0', $scale)
            : self::add($b, '0', $scale);
    }

    /**
     * Return the smaller of two values.
     *
     * @throws \Throwable
     * @return numeric-string
     */
    public static function min(int|float|string $a, int|float|string $b, int $scale = 8): string
    {
        return self::lte($a, $b, $scale)
            ? self::add($a, '0', $scale)
            : self::add($b, '0', $scale);
    }

    /**
     * Compare two values. Returns -1, 0, or 1.
     *
     * @throws \Throwable
     */
    public static function compare(int|float|string $a, int|float|string $b, int $scale = 8): int
    {
        return bccomp(
            Guard::isNumeric((string) $a), // @phpstan-ignore-line
            Guard::isNumeric((string) $b), // @phpstan-ignore-line
            $scale,
        );
    }

    /**
     * Return true when the value is exactly zero at the given scale.
     *
     * @throws \Throwable
     */
    public static function isZero(int|float|string $value, int $scale = 8): bool
    {
        return 0 === self::compare($value, '0', $scale);
    }

    /**
     * Return true when $a is greater than $b.
     *
     * @throws \Throwable
     */
    public static function gt(int|float|string $a, int|float|string $b, int $scale = 8): bool
    {
        return 1 === self::compare($a, $b, $scale);
    }

    /**
     * Return true when $a is less than $b.
     *
     * @throws \Throwable
     */
    public static function lt(int|float|string $a, int|float|string $b, int $scale = 8): bool
    {
        return -1 === self::compare($a, $b, $scale);
    }

    /**
     * Return true when $a is greater than or equal to $b.
     *
     * @throws \Throwable
     */
    public static function gte(int|float|string $a, int|float|string $b, int $scale = 8): bool
    {
        return self::compare($a, $b, $scale) >= 0;
    }

    /**
     * Return true when $a is less than or equal to $b.
     *
     * @throws \Throwable
     */
    public static function lte(int|float|string $a, int|float|string $b, int $scale = 8): bool
    {
        return self::compare($a, $b, $scale) <= 0;
    }

    /**
     * Return true when $a equals $b at the given scale.
     *
     * @throws \Throwable
     */
    public static function eq(int|float|string $a, int|float|string $b, int $scale = 8): bool
    {
        return 0 === self::compare($a, $b, $scale);
    }

    /**
     * Return true when $value is strictly greater than zero.
     *
     * @throws \Throwable
     */
    public static function isPositive(int|float|string $value, int $scale = 8): bool
    {
        return 1 === self::compare($value, '0', $scale);
    }

    /**
     * Return true when $value is strictly less than zero.
     *
     * @throws \Throwable
     */
    public static function isNegative(int|float|string $value, int $scale = 8): bool
    {
        return -1 === self::compare($value, '0', $scale);
    }

    /**
     * Negate a value (equivalent to multiplying by -1).
     *
     * @throws \Throwable
     * @return numeric-string
     */
    public static function negate(int|float|string $value, int $scale = 8): string
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
     * Example: Bc::percent(200, 15) → '30.00000000'
     *
     * @throws \Throwable
     * @return numeric-string
     */
    public static function percent(int|float|string $value, int|float|string $percent, int $scale = 8): string
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
     * @param array<int|float|string> $operands
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
            static fn(string $carry, int|float|string $item) => self::mul($carry, (string) $item, $scale),
            '1',
        );
    }

    /**
     * Subtract all subsequent operands from the first.
     *
     * Returns '0.00000000' for an empty array.
     *
     * @param array<int|float|string> $operands
     * @throws \Throwable
     * @return numeric-string
     */
    public static function subAll(array $operands, int $scale = 8): string
    {
        if ($operands === []) {
            return self::add('0', '0', $scale);
        }

        $first = (string) array_shift($operands);

        /** @var numeric-string */
        return array_reduce(
            $operands,
            static fn(string $carry, int|float|string $item) => self::sub($carry, (string) $item, $scale),
            $first,
        );
    }

    /**
     * Divide the first operand by each subsequent operand in turn.
     *
     * Returns '1.00000000' for an empty array (neutral element of division).
     *
     * @param array<int|float|string> $operands
     * @throws \Throwable
     * @return numeric-string
     */
    public static function divAll(array $operands, int $scale = 8): string
    {
        if ($operands === []) {
            return self::add('1', '0', $scale);
        }

        $first = (string) array_shift($operands);

        /** @var numeric-string */
        return array_reduce(
            $operands,
            static fn(string $carry, int|float|string $item) => self::div($carry, (string) $item, $scale),
            $first,
        );
    }
}
