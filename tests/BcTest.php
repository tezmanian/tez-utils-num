<?php

declare(strict_types=1);

namespace Tezmanian\Utils\Tests\Num;

use PHPUnit\Framework\TestCase;
use Tezmanian\Utils\Num\Bc;

final class BcTest extends TestCase
{
    // ── add ──────────────────────────────────────────────────────────────────

    public function testAddDefaultScale(): void
    {
        self::assertSame('30.80000000', Bc::add('10.5', '20.3'));
    }

    public function testAddCustomScale(): void
    {
        self::assertSame('30.80', Bc::add('10.5', '20.3', 2));
    }

    public function testAddNegativeValues(): void
    {
        self::assertSame('-5.00000000', Bc::add('-10', '5'));
    }

    public function testAddZero(): void
    {
        self::assertSame('42.00000000', Bc::add('42', '0'));
    }

    // ── sub ──────────────────────────────────────────────────────────────────

    public function testSubDefaultScale(): void
    {
        self::assertSame('9.20000000', Bc::sub('20.3', '11.1'));
    }

    public function testSubResultNegative(): void
    {
        self::assertSame('-5.00000000', Bc::sub('5', '10'));
    }

    public function testSubSameValues(): void
    {
        self::assertSame('0.00000000', Bc::sub('100', '100'));
    }

    // ── mul ──────────────────────────────────────────────────────────────────

    public function testMulDefaultScale(): void
    {
        self::assertSame('20.00000000', Bc::mul('4', '5'));
    }

    public function testMulDecimalPrecision(): void
    {
        // 0.1 × 0.1 = 0.01 exactly at scale 8
        self::assertSame('0.01000000', Bc::mul('0.1', '0.1'));
    }

    public function testMulByZero(): void
    {
        self::assertSame('0.00000000', Bc::mul('12345.678', '0'));
    }

    public function testMulNegative(): void
    {
        self::assertSame('-6.00000000', Bc::mul('-2', '3'));
    }

    // ── div ──────────────────────────────────────────────────────────────────

    public function testDivDefaultScale(): void
    {
        self::assertSame('0.33333333', Bc::div('1', '3'));
    }

    public function testDivExactResult(): void
    {
        self::assertSame('2.50000000', Bc::div('5', '2'));
    }

    public function testDivCustomScale(): void
    {
        self::assertSame('0.33', Bc::div('1', '3', 2));
    }

    public function testDivByZeroThrows(): void
    {
        $this->expectException(\DivisionByZeroError::class);
        Bc::div('1', '0');
    }

    public function testDivByZeroDecimalThrows(): void
    {
        $this->expectException(\DivisionByZeroError::class);
        Bc::div('1', '0.00000000');
    }

    // ── pow ──────────────────────────────────────────────────────────────────

    public function testPow(): void
    {
        self::assertSame('8.00000000', Bc::pow('2', '3'));
    }

    public function testPowDecimalBase(): void
    {
        self::assertSame('2.25000000', Bc::pow('1.5', '2'));
    }

    public function testPowZeroExponent(): void
    {
        self::assertSame('1.00000000', Bc::pow('7', '0'));
    }

    public function testPowNegativeExponent(): void
    {
        self::assertSame('0.25000000', Bc::pow('2', '-2'));
    }

    public function testPowIntegerValuedDecimalExponent(): void
    {
        self::assertSame('8.00', Bc::pow('2', '3.0', 2));
    }

    public function testPowFractionalExponentThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Bc::pow('4', '0.5');
    }

    public function testPowZeroToNegativePowerThrows(): void
    {
        $this->expectException(\DivisionByZeroError::class);
        Bc::pow('0', '-1');
    }

    public function testPowThrowsOnNonNumericInput(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        /** @phpstan-ignore-next-line */
        Bc::pow('x', '2');
    }

    // ── sqrt ─────────────────────────────────────────────────────────────────

    public function testSqrtPerfectSquare(): void
    {
        self::assertSame('3.00000000', Bc::sqrt('9'));
    }

    public function testSqrtIrrational(): void
    {
        self::assertSame('1.41421356', Bc::sqrt('2'));
    }

    public function testSqrtZero(): void
    {
        self::assertSame('0.00000000', Bc::sqrt('0'));
    }

    public function testSqrtCustomScale(): void
    {
        self::assertSame('1.41', Bc::sqrt('2', 2));
    }

    public function testSqrtNegativeThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Bc::sqrt('-1');
    }

    // ── mod ──────────────────────────────────────────────────────────────────

    public function testMod(): void
    {
        self::assertSame('1.00000000', Bc::mod('10', '3'));
    }

    public function testModDecimal(): void
    {
        self::assertSame('0.50000000', Bc::mod('5.5', '2.5'));
    }

    public function testModNegativeDividendKeepsSign(): void
    {
        self::assertSame('-1.00000000', Bc::mod('-10', '3'));
    }

    public function testModByZeroThrows(): void
    {
        $this->expectException(\DivisionByZeroError::class);
        Bc::mod('10', '0');
    }

    // ── floor / ceil ─────────────────────────────────────────────────────────

    public function testFloorPositive(): void
    {
        self::assertSame('1.00000000', Bc::floor('1.9'));
    }

    public function testFloorNegative(): void
    {
        self::assertSame('-2.00000000', Bc::floor('-1.1'));
    }

    public function testFloorNegativeBetweenZeroAndMinusOne(): void
    {
        self::assertSame('-1.00000000', Bc::floor('-0.5'));
    }

    public function testFloorIntegerValueUnchanged(): void
    {
        self::assertSame('-3.00000000', Bc::floor('-3.00000000'));
    }

    public function testFloorTinyFractionBeyondScale(): void
    {
        self::assertSame('-1.00', Bc::floor('-0.000000001', 2));
    }

    public function testCeilPositive(): void
    {
        self::assertSame('2.00000000', Bc::ceil('1.1'));
    }

    public function testCeilNegative(): void
    {
        self::assertSame('-1.00000000', Bc::ceil('-1.9'));
    }

    public function testCeilNegativeBetweenZeroAndMinusOne(): void
    {
        self::assertSame('0.00000000', Bc::ceil('-0.5'));
    }

    public function testCeilIntegerValueUnchanged(): void
    {
        self::assertSame('3.00000000', Bc::ceil('3.00000000'));
    }

    public function testCeilCustomScale(): void
    {
        self::assertSame('2.0', Bc::ceil('1.01', 1));
    }

    public function testFloorThrowsOnNonNumericInput(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        /** @phpstan-ignore-next-line */
        Bc::floor('x');
    }

    // ── scale ─────────────────────────────────────────────────────────────────

    public function testScalePadsShortDecimals(): void
    {
        self::assertSame('1.50000000', Bc::scale('1.5'));
    }

    public function testScaleTruncatesLongDecimals(): void
    {
        self::assertSame('1.12345678', Bc::scale('1.123456789'));
    }

    public function testScaleIntegerInput(): void
    {
        self::assertSame('42.00000000', Bc::scale('42'));
    }

    public function testScaleCustomScale(): void
    {
        self::assertSame('3.14', Bc::scale('3.14159', 2));
    }

    // ── abs ───────────────────────────────────────────────────────────────────

    public function testAbsNegativeValue(): void
    {
        self::assertSame('5.00000000', Bc::abs('-5'));
    }

    public function testAbsPositiveValueUnchanged(): void
    {
        self::assertSame('3.14000000', Bc::abs('3.14'));
    }

    public function testAbsZero(): void
    {
        self::assertSame('0.00000000', Bc::abs('0'));
    }

    // ── max / min ─────────────────────────────────────────────────────────────

    public function testMaxReturnsLarger(): void
    {
        self::assertSame('10.00000000', Bc::max('10', '5'));
    }

    public function testMaxWhenEqual(): void
    {
        self::assertSame('5.00000000', Bc::max('5', '5'));
    }

    public function testMinReturnsSmaller(): void
    {
        self::assertSame('3.00000000', Bc::min('10', '3'));
    }

    public function testMinNegativeValues(): void
    {
        self::assertSame('-10.00000000', Bc::min('-5', '-10'));
    }

    // ── compare ───────────────────────────────────────────────────────────────

    public function testCompareGreater(): void
    {
        self::assertSame(1, Bc::compare('10', '5'));
    }

    public function testCompareLess(): void
    {
        self::assertSame(-1, Bc::compare('5', '10'));
    }

    public function testCompareEqual(): void
    {
        self::assertSame(0, Bc::compare('5.00', '5.000'));
    }

    // ── isZero ────────────────────────────────────────────────────────────────

    public function testIsZeroTrueForZero(): void
    {
        self::assertTrue(Bc::isZero('0'));
    }

    public function testIsZeroTrueForFormattedZero(): void
    {
        self::assertTrue(Bc::isZero('0.00000000'));
    }

    public function testIsZeroFalseForNonZero(): void
    {
        self::assertFalse(Bc::isZero('0.00000001'));
    }

    // ── gt / lt ───────────────────────────────────────────────────────────────

    public function testGtTrueWhenGreater(): void
    {
        self::assertTrue(Bc::gt('10', '5'));
    }

    public function testGtFalseWhenEqual(): void
    {
        self::assertFalse(Bc::gt('5', '5'));
    }

    public function testLtTrueWhenLess(): void
    {
        self::assertTrue(Bc::lt('3', '7'));
    }

    public function testLtFalseWhenGreater(): void
    {
        self::assertFalse(Bc::lt('7', '3'));
    }

    // ── gte ───────────────────────────────────────────────────────────────────

    public function testGteTrueWhenGreater(): void
    {
        self::assertTrue(Bc::gte('10', '5'));
    }

    public function testGteTrueWhenEqual(): void
    {
        self::assertTrue(Bc::gte('5', '5'));
    }

    public function testGteFalseWhenLess(): void
    {
        self::assertFalse(Bc::gte('3', '7'));
    }

    // ── lte ───────────────────────────────────────────────────────────────────

    public function testLteTrueWhenLess(): void
    {
        self::assertTrue(Bc::lte('3', '7'));
    }

    public function testLteTrueWhenEqual(): void
    {
        self::assertTrue(Bc::lte('5', '5'));
    }

    public function testLteFalseWhenGreater(): void
    {
        self::assertFalse(Bc::lte('10', '5'));
    }

    // ── eq ────────────────────────────────────────────────────────────────────

    public function testEqTrueWhenEqual(): void
    {
        self::assertTrue(Bc::eq('5.00', '5.000'));
    }

    public function testEqFalseWhenDifferent(): void
    {
        self::assertFalse(Bc::eq('5', '5.00000001'));
    }

    public function testEqRespectsScale(): void
    {
        // At scale 2, 5.001 and 5.009 both truncate to 5.00 and are equal
        self::assertTrue(Bc::eq('5.001', '5.009', 2));
    }

    // ── isPositive ────────────────────────────────────────────────────────────

    public function testIsPositiveTrueForPositiveValue(): void
    {
        self::assertTrue(Bc::isPositive('0.00000001'));
    }

    public function testIsPositiveFalseForZero(): void
    {
        self::assertFalse(Bc::isPositive('0'));
    }

    public function testIsPositiveFalseForNegative(): void
    {
        self::assertFalse(Bc::isPositive('-1'));
    }

    // ── isNegative ────────────────────────────────────────────────────────────

    public function testIsNegativeTrueForNegativeValue(): void
    {
        self::assertTrue(Bc::isNegative('-0.00000001'));
    }

    public function testIsNegativeFalseForZero(): void
    {
        self::assertFalse(Bc::isNegative('0'));
    }

    public function testIsNegativeFalseForPositive(): void
    {
        self::assertFalse(Bc::isNegative('1'));
    }

    // ── negate ────────────────────────────────────────────────────────────────

    public function testNegatePositiveValue(): void
    {
        self::assertSame('-5.00000000', Bc::negate('5'));
    }

    public function testNegateNegativeValue(): void
    {
        self::assertSame('5.00000000', Bc::negate('-5'));
    }

    public function testNegateZeroRemainsZero(): void
    {
        self::assertSame('0.00000000', Bc::negate('0'));
    }

    public function testNegateCustomScale(): void
    {
        self::assertSame('-3.14', Bc::negate('3.14', 2));
    }

    // ── sum ───────────────────────────────────────────────────────────────────

    public function testSumEmptyArrayReturnsZero(): void
    {
        self::assertSame('0.00000000', Bc::sum([]));
    }

    public function testSumSingleElement(): void
    {
        self::assertSame('5.00000000', Bc::sum(['5']));
    }

    public function testSumMultipleElements(): void
    {
        self::assertSame('6.00000000', Bc::sum(['1', '2', '3']));
    }

    public function testSumWithNegativeValues(): void
    {
        self::assertSame('-2.00000000', Bc::sum(['-1', '2', '-3']));
    }

    public function testSumCustomScale(): void
    {
        self::assertSame('3.30', Bc::sum(['1.1', '2.2'], 2));
    }

    public function testSumThrowsOnNonNumericElement(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        /** @phpstan-ignore-next-line */
        Bc::sum(['1', 'not-a-number', '3']);
    }

    // ── product ───────────────────────────────────────────────────────────────

    public function testProductEmptyArrayReturnsOne(): void
    {
        self::assertSame('1.00000000', Bc::product([]));
    }

    public function testProductSingleElement(): void
    {
        self::assertSame('7.00000000', Bc::product(['7']));
    }

    public function testProductMultipleElements(): void
    {
        self::assertSame('24.00000000', Bc::product(['2', '3', '4']));
    }

    public function testProductWithDecimalValues(): void
    {
        self::assertSame('0.25000000', Bc::product(['0.5', '0.5']));
    }

    public function testProductWithNegativeValue(): void
    {
        self::assertSame('-6.00000000', Bc::product(['-2', '3']));
    }

    // ── subAll ────────────────────────────────────────────────────────────────

    public function testSubAllEmptyArrayReturnsZero(): void
    {
        self::assertSame('0.00000000', Bc::subAll([]));
    }

    public function testSubAllSingleElement(): void
    {
        // Single element is returned as-is (no reduce step applies)
        self::assertSame('5', Bc::subAll(['5']));
    }

    public function testSubAllMultipleElements(): void
    {
        // 10 - 3 - 2 = 5
        self::assertSame('5.00000000', Bc::subAll(['10', '3', '2']));
    }

    public function testSubAllResultCanBeNegative(): void
    {
        self::assertSame('-5.00000000', Bc::subAll(['5', '10']));
    }

    // ── divAll ────────────────────────────────────────────────────────────────

    public function testDivAllEmptyArrayReturnsOne(): void
    {
        self::assertSame('1.00000000', Bc::divAll([]));
    }

    public function testDivAllSingleElement(): void
    {
        // Single element is returned as-is (no reduce step applies)
        self::assertSame('8', Bc::divAll(['8']));
    }

    public function testDivAllMultipleElements(): void
    {
        // 100 / 5 / 4 = 5
        self::assertSame('5.00000000', Bc::divAll(['100', '5', '4']));
    }

    public function testDivAllThrowsOnDivisionByZero(): void
    {
        $this->expectException(\DivisionByZeroError::class);
        Bc::divAll(['10', '0']);
    }

    // ── percent ───────────────────────────────────────────────────────────────

    public function testPercentBasicCalculation(): void
    {
        // 15% of 200 = 30
        self::assertSame('30.00000000', Bc::percent('200', '15'));
    }

    public function testPercentFiftyPercent(): void
    {
        self::assertSame('50.00000000', Bc::percent('100', '50'));
    }

    public function testPercentFractionalResult(): void
    {
        // 33.33% of 100 = 33.33000000
        self::assertSame('33.33000000', Bc::percent('100', '33.33'));
    }

    public function testPercentZeroPercent(): void
    {
        self::assertSame('0.00000000', Bc::percent('100', '0'));
    }

    public function testPercentCustomScale(): void
    {
        self::assertSame('30.00', Bc::percent('200', '15', 2));
    }

    // ── Guard validation ──────────────────────────────────────────────────────

    public function testAddThrowsOnNonNumericInput(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        /** @phpstan-ignore-next-line */
        Bc::add('not-a-number', '1');
    }

    public function testSubThrowsOnNonNumericInput(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        /** @phpstan-ignore-next-line */
        Bc::sub('5', 'abc');
    }

    public function testMulThrowsOnNonNumericInput(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        /** @phpstan-ignore-next-line */
        Bc::mul('x', '2');
    }

    public function testDivThrowsOnNonNumericInput(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        /** @phpstan-ignore-next-line */
        Bc::div('10', 'y');
    }

    public function testGtThrowsOnNonNumericInput(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        /** @phpstan-ignore-next-line */
        Bc::gt('bad', '1');
    }

    public function testLtThrowsOnNonNumericInput(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        /** @phpstan-ignore-next-line */
        Bc::lt('1', 'bad');
    }
}
