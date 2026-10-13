# tez-utils-num

[![CI](https://github.com/tezmanian/tez-utils-num/actions/workflows/php.yml/badge.svg)](https://github.com/tezmanian/tez-utils-num/actions/workflows/php.yml)
[![Packagist Version](https://img.shields.io/packagist/v/tezmanian/utils-num)](https://packagist.org/packages/tezmanian/utils-num)
[![PHP](https://img.shields.io/packagist/php-v/tezmanian/utils-num)](https://packagist.org/packages/tezmanian/utils-num)
[![License: MIT](https://img.shields.io/packagist/l/tezmanian/utils-num)](LICENSE)

PHP number utilities: formatting, byte sizes, percentage, clamping, and rounding helpers (`Num`), plus arbitrary-precision decimal arithmetic via bcmath (`Bc`). PHP 8.3+, requires `ext-bcmath`.

## Installation

```bash
composer require tezmanian/utils-num
```

Package: [`tezmanian/utils-num`](https://packagist.org/packages/tezmanian/utils-num) · Namespace: `Tezmanian\Utils\Num`

## Components

| Class | Purpose |
|-------|---------|
| `Num` | Formatting, byte sizes, percentages, clamping, rounding |
| `Bc` | Arbitrary-precision decimal arithmetic wrapping `ext-bcmath` |

---

## Num

Formatting and rounding helpers for everyday number display.

### format()

```php
use Tezmanian\Utils\Num\Num;

Num::format(1234567.891);                              // '1,234,567.89'
Num::format(1234567.891, dec: ',', thousands: '.');    // '1.234.567,89'
Num::format(1234.5, decimals: 0);                      // '1,235'
Num::format(-1000.0);                                  // '-1,000.00'
```

### bytes()

Converts a byte count to a human-readable string using binary units (1 KB = 1 024 B).

```php
Num::bytes(0);           // '0 B'
Num::bytes(1023);        // '1,023 B'
Num::bytes(1024);        // '1.00 KB'
Num::bytes(1048576);     // '1.00 MB'
Num::bytes(1073741824);  // '1.00 GB'
Num::bytes(1024 ** 4);   // '1.00 TB'
Num::bytes(1500, precision: 0); // '1 KB'
```

### percent()

Formats a ratio (`0.0`–`1.0`) as a percentage string.

```php
Num::percent(0.1234);              // '12.3%'
Num::percent(1.0);                 // '100.0%'
Num::percent(0.0056, decimals: 2); // '0.56%'
```

### clamp()

Constrains a value to the inclusive range `[$min, $max]`.

```php
Num::clamp(105, 0, 100);   // 100
Num::clamp(-5,  0, 100);   // 0
Num::clamp(50,  0, 100);   // 50
Num::clamp(1.5, 0.0, 0.5); // 0.5
```

### roundTo()

Rounds a value to the nearest multiple of `$multiple`.

```php
Num::roundTo(17, 5);   // 15
Num::roundTo(18, 5);   // 20
Num::roundTo(1.7, 0.5); // 1.5
```

---

## Bc

Arbitrary-precision decimal arithmetic wrapping PHP's `bcmath` extension. Designed for financial calculations where floating-point imprecision is not acceptable.

**All inputs must be numeric strings** (e.g. `'1.50000000'`). Passing a non-numeric string throws `\InvalidArgumentException`. Division by zero throws `\DivisionByZeroError`.

The default scale is **8 decimal places** for all methods.

### Arithmetic

```php
use Tezmanian\Utils\Num\Bc;

Bc::add('10.5', '20.3');          // '30.80000000'
Bc::sub('20.3', '11.1');          // '9.20000000'
Bc::mul('4', '5');                 // '20.00000000'
Bc::div('1', '3');                 // '0.33333333'
Bc::div('1', '3', scale: 2);      // '0.33'

// Array operations
Bc::sum(['1', '2', '3']);          // '6.00000000'
Bc::sum([]);                       // '0.00000000'
Bc::product(['2', '3', '4']);      // '24.00000000'
Bc::product([]);                   // '1.00000000'
Bc::subAll(['10', '3', '2']);      // '5.00000000'  (10 - 3 - 2)
Bc::divAll(['100', '5', '4']);     // '5.00000000'  (100 / 5 / 4)
```

### Utilities

```php
Bc::abs('-5');                     // '5.00000000'
Bc::negate('3.14');                // '-3.14000000'
Bc::negate('-5');                  // '5.00000000'
Bc::max('10', '5');                // '10.00000000'
Bc::min('10', '3');                // '3.00000000'
Bc::scale('1.5');                  // '1.50000000'  — normalize to 8 decimal places
Bc::scale('1.123456789');          // '1.12345678'  — truncates, does not round
Bc::percent('200', '15');          // '30.00000000' — 15% of 200
```

### Comparisons

```php
Bc::compare('10', '5');    // 1   (greater)
Bc::compare('5', '10');    // -1  (less)
Bc::compare('5.00', '5'); // 0   (equal)

Bc::eq('5.00', '5.000');   // true
Bc::gt('10', '5');          // true
Bc::lt('3', '7');           // true
Bc::gte('5', '5');          // true
Bc::lte('3', '7');          // true

Bc::isZero('0.00000000');   // true
Bc::isZero('0.00000001');   // false
Bc::isPositive('0.00000001'); // true
Bc::isNegative('-0.00000001'); // true
```

### Scale behaviour

`Bc::scale()` truncates (does not round). All arithmetic methods default to scale 8 and accept an explicit `scale` parameter:

```php
Bc::add('10.5', '20.3', scale: 2); // '30.80'
Bc::div('1', '3', scale: 4);       // '0.3333'

// Equal at scale 2, different at scale 8
Bc::eq('5.001', '5.009', scale: 2); // true
Bc::eq('5.001', '5.009');           // false
```

---

## Requirements

- PHP 8.3+
- `ext-bcmath`
- [`tezmanian/utils-validate`](https://packagist.org/packages/tezmanian/utils-validate) ^1.0

## Development

```bash
make build && make install
make ci   # audit, PHPStan, code style, tests
```

## License

MIT — see [LICENSE](LICENSE).

Author: René Halberstadt
