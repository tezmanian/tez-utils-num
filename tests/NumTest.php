<?php

declare(strict_types=1);

namespace Tez\Utils\Tests\Num;

use PHPUnit\Framework\TestCase;
use Tez\Utils\Num\Num;

final class NumTest extends TestCase
{
    // -------------------------------------------------------------------------
    // format()
    // -------------------------------------------------------------------------

    public function testFormatDefaultSeparators(): void
    {
        self::assertSame('1,234,567.89', Num::format(1234567.891));
    }

    public function testFormatCustomSeparators(): void
    {
        self::assertSame('1.234.567,89', Num::format(1234567.891, dec: ',', thousands: '.'));
    }

    public function testFormatZeroDecimals(): void
    {
        self::assertSame('1,235', Num::format(1234.5, 0));
    }

    public function testFormatNegative(): void
    {
        self::assertSame('-1,000.00', Num::format(-1000.0));
    }

    public function testFormatInteger(): void
    {
        self::assertSame('42.00', Num::format(42));
    }

    // -------------------------------------------------------------------------
    // bytes()
    // -------------------------------------------------------------------------

    public function testBytesZero(): void
    {
        self::assertSame('0 B', Num::bytes(0));
    }

    public function testBytesUnderKilobyte(): void
    {
        self::assertSame('1,023 B', Num::bytes(1023));
    }

    public function testBytesExactlyOneKilobyte(): void
    {
        self::assertSame('1.00 KB', Num::bytes(1024));
    }

    public function testBytesMegabyte(): void
    {
        self::assertSame('1.00 MB', Num::bytes(1048576));
    }

    public function testBytesGigabyte(): void
    {
        self::assertSame('1.00 GB', Num::bytes(1073741824));
    }

    public function testBytesCustomPrecision(): void
    {
        self::assertSame('1 KB', Num::bytes(1500, precision: 0));
    }

    public function testBytesPrecisionTwo(): void
    {
        self::assertSame('1.46 KB', Num::bytes(1500));
    }

    public function testBytesTerabyte(): void
    {
        self::assertSame('1.00 TB', Num::bytes(1024 ** 4));
    }

    // -------------------------------------------------------------------------
    // percent()
    // -------------------------------------------------------------------------

    public function testPercentDefaultDecimals(): void
    {
        self::assertSame('12.3%', Num::percent(0.1234));
    }

    public function testPercentFullRatio(): void
    {
        self::assertSame('100.0%', Num::percent(1.0));
    }

    public function testPercentSmallValue(): void
    {
        self::assertSame('0.56%', Num::percent(0.0056, decimals: 2));
    }

    public function testPercentZero(): void
    {
        self::assertSame('0.0%', Num::percent(0.0));
    }

    // -------------------------------------------------------------------------
    // clamp()
    // -------------------------------------------------------------------------

    public function testClampAboveMax(): void
    {
        self::assertSame(100, Num::clamp(105, 0, 100));
    }

    public function testClampBelowMin(): void
    {
        self::assertSame(0, Num::clamp(-5, 0, 100));
    }

    public function testClampWithinRange(): void
    {
        self::assertSame(50, Num::clamp(50, 0, 100));
    }

    public function testClampAtExactBoundary(): void
    {
        self::assertSame(0, Num::clamp(0, 0, 100));
        self::assertSame(100, Num::clamp(100, 0, 100));
    }

    public function testClampFloat(): void
    {
        self::assertSame(0.5, Num::clamp(1.5, 0.0, 0.5));
    }

    // -------------------------------------------------------------------------
    // roundTo()
    // -------------------------------------------------------------------------

    public function testRoundToNearestFiveLow(): void
    {
        self::assertSame(15, Num::roundTo(17, 5));
    }

    public function testRoundToNearestFiveHigh(): void
    {
        self::assertSame(20, Num::roundTo(18, 5));
    }

    public function testRoundToHalf(): void
    {
        self::assertSame(1.5, Num::roundTo(1.7, 0.5));
    }

    public function testRoundToExactMultiple(): void
    {
        self::assertSame(10, Num::roundTo(10, 5));
    }

    public function testRoundToOne(): void
    {
        self::assertSame(7, Num::roundTo(7, 1));
    }

    public function testRoundToZeroMultipleReturnsValue(): void
    {
        self::assertSame(17, Num::roundTo(17, 0));
    }
}
