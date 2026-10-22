<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\Tests\Review;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Trassd\Contao\GroundingPages\Review\ReviewFieldsValidator;

class ReviewFieldsValidatorTest extends TestCase
{
    private const int NOW = 1_792_670_400; // 2026-10-22 12:00 UTC

    /**
     * @return iterable<string, array{string, string, string|null}>
     */
    public static function cases(): iterable
    {
        yield 'beides leer' => ['', '', null];
        yield 'beides gesetzt' => ['Jane Doe', (string) (self::NOW - 86_400), null];
        yield 'heute' => ['Jane Doe', (string) self::NOW, null];
        yield 'Datum als formatierter POST-Wert' => ['Jane Doe', '22.10.2026', null];
        yield 'Name ohne Datum' => ['Jane Doe', '', ReviewFieldsValidator::ERROR_INCOMPLETE];
        yield 'Name ohne Datum, Datum 0' => ['Jane Doe', '0', ReviewFieldsValidator::ERROR_INCOMPLETE];
        yield 'Datum ohne Name' => ['', (string) self::NOW, ReviewFieldsValidator::ERROR_INCOMPLETE];
        yield 'nur Leerzeichen als Name' => ['   ', (string) self::NOW, ReviewFieldsValidator::ERROR_INCOMPLETE];
        yield 'morgen' => ['Jane Doe', (string) (self::NOW + 86_400), ReviewFieldsValidator::ERROR_FUTURE];
    }

    #[DataProvider('cases')]
    public function testValidate(string $name, string $date, string|null $expected): void
    {
        $this->assertSame($expected, (new ReviewFieldsValidator())->validate($name, $date, self::NOW));
    }
}
