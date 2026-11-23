<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\Tests\Dto;

use PHPUnit\Framework\TestCase;
use Trassd\Contao\GroundingPages\Dto\GroundingPageDto;
use Trassd\Contao\GroundingPages\Dto\GroundingSectionDto;

class GroundingDtoTest extends TestCase
{
    public function testSectionDtoDefaults(): void
    {
        $dto = new GroundingSectionDto(sectionType: 'fact-grid');

        $this->assertSame('fact-grid', $dto->sectionType);
        $this->assertSame('', $dto->sectionTitle);
        $this->assertSame([], $dto->factGrid);
        $this->assertSame([], $dto->identifiers);
    }

    public function testSectionDtoCarriesTypedRows(): void
    {
        $dto = new GroundingSectionDto(
            sectionType: 'faq',
            sectionTitle: 'FAQ',
            faq: [['question' => 'Was?', 'answer' => 'Das.']],
        );

        $this->assertSame('FAQ', $dto->sectionTitle);
        $this->assertSame('Das.', $dto->faq[0]['answer']);
    }

    public function testPageDtoDefaults(): void
    {
        $dto = new GroundingPageDto(name: 'CERN', schemaType: 'Organization');

        $this->assertSame('CERN', $dto->name);
        $this->assertSame('Organization', $dto->schemaType);
        $this->assertSame('', $dto->entityType);
        $this->assertSame('', $dto->correctionContact);
        $this->assertSame([], $dto->changelog);
        $this->assertSame([], $dto->sameAs);
        $this->assertSame([], $dto->sections);
        $this->assertNull($dto->datePublished);
        $this->assertNull($dto->dateModified);
        $this->assertSame('person', $dto->reviewerType);
        $this->assertSame('', $dto->reviewerName);
        $this->assertNull($dto->lastReviewed);
        $this->assertFalse($dto->isReviewed());
    }

    public function testPageIsReviewedOnlyWithNameAndDate(): void
    {
        $reviewed = new GroundingPageDto(name: 'X', schemaType: 'Thing', reviewerName: 'Jane Doe', lastReviewed: '2026-10-02');
        $nameOnly = new GroundingPageDto(name: 'X', schemaType: 'Thing', reviewerName: 'Jane Doe');
        $dateOnly = new GroundingPageDto(name: 'X', schemaType: 'Thing', lastReviewed: '2026-10-02');

        $emptyDate = new GroundingPageDto(name: 'X', schemaType: 'Thing', reviewerName: 'Jane Doe', lastReviewed: '');

        $this->assertTrue($reviewed->isReviewed());
        $this->assertFalse($emptyDate->isReviewed());
        $this->assertFalse($nameOnly->isReviewed());
        $this->assertFalse($dateOnly->isReviewed());
    }

    public function testPageDtoAggregatesSections(): void
    {
        $dto = new GroundingPageDto(
            name: 'CERN',
            schemaType: 'Organization',
            sameAs: ['https://home.cern/'],
            sections: [new GroundingSectionDto(sectionType: 'sources')],
        );

        $this->assertCount(1, $dto->sections);
        $this->assertInstanceOf(GroundingSectionDto::class, $dto->sections[0]);
        $this->assertSame('https://home.cern/', $dto->sameAs[0]);
    }

    public function testNewReferenceFieldsDefaultToEmpty(): void
    {
        $dto = new GroundingPageDto(name: 'CERN', schemaType: 'Organization');

        $this->assertSame('', $dto->reviewStandard);
        $this->assertSame('', $dto->entityId);
        $this->assertSame('', $dto->anchorUrl);
    }

    public function testOfficialProfilesMergeSameAsAndUrlIdentifiersWithoutDuplicates(): void
    {
        $dto = new GroundingPageDto(
            name: 'CERN',
            schemaType: 'Organization',
            sameAs: ['https://www.wikidata.org/wiki/Q42944'],
            sections: [
                new GroundingSectionDto(sectionType: 'sources', identifiers: [
                    ['label' => 'Wikidata', 'value' => 'https://www.wikidata.org/wiki/Q42944'],
                    ['label' => 'ROR', 'value' => 'https://ror.org/01ggx4157'],
                    ['label' => 'Mitgliedstaaten', 'value' => '25'],
                    ['label' => '', 'value' => 'ohne Label'],
                ]),
            ],
        );

        $this->assertSame(
            [
                ['label' => '', 'url' => 'https://www.wikidata.org/wiki/Q42944'],
                ['label' => 'ROR', 'url' => 'https://ror.org/01ggx4157'],
            ],
            $dto->officialProfiles(),
        );
        $this->assertSame(['https://www.wikidata.org/wiki/Q42944', 'https://ror.org/01ggx4157'], $dto->sameAsUrls());
        $this->assertSame([['label' => 'Mitgliedstaaten', 'value' => '25']], $dto->identifierValues());
    }

    public function testWithHumanNoticeReturnsChangedCopy(): void
    {
        $dto = new GroundingPageDto(name: 'CERN', schemaType: 'Organization', reviewStandard: '2.0');
        $hidden = $dto->withHumanNotice(false);

        $this->assertTrue($dto->showHumanNotice);
        $this->assertFalse($hidden->showHumanNotice);
        $this->assertSame('2.0', $hidden->reviewStandard);
        $this->assertSame('CERN', $hidden->name);
    }
}
