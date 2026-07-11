<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\Tests\Enum;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Trassd\Contao\GroundingPages\Enum\SectionRowSchema;

class SectionRowSchemaTest extends TestCase
{
    public function testBackingValueIsTheModelFieldName(): void
    {
        $this->assertSame('timelineItems', SectionRowSchema::Timeline->fieldName());
        $this->assertSame('faqItems', SectionRowSchema::Faq->fieldName());
        $this->assertSame('factGrid', SectionRowSchema::FactGrid->fieldName());
    }

    /**
     * @param array{0: string, 1: string} $expected
     */
    #[DataProvider('columnProvider')]
    public function testColumnsAreTheTwoRowKeys(SectionRowSchema $schema, array $expected): void
    {
        $this->assertSame($expected, $schema->columns());
    }

    /**
     * @return iterable<string, array{SectionRowSchema, array{0: string, 1: string}}>
     */
    public static function columnProvider(): iterable
    {
        yield 'factGrid' => [SectionRowSchema::FactGrid, ['label', 'value']];
        yield 'timelineItems' => [SectionRowSchema::Timeline, ['year', 'event']];
        yield 'definedTerms' => [SectionRowSchema::DefinedTerms, ['term', 'definition']];
        yield 'faqItems' => [SectionRowSchema::Faq, ['question', 'answer']];
        yield 'sources' => [SectionRowSchema::Sources, ['title', 'url']];
        yield 'identifiers' => [SectionRowSchema::Identifiers, ['label', 'value']];
    }

    public function testEveryColumnPairHasTwoDistinctNonEmptyKeys(): void
    {
        foreach (SectionRowSchema::cases() as $schema) {
            [$a, $b] = $schema->columns();

            $this->assertNotSame('', $a, $schema->name);
            $this->assertNotSame('', $b, $schema->name);
            $this->assertNotSame($a, $b, $schema->name);
        }
    }
}
