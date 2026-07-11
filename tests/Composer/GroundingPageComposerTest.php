<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\Tests\Composer;

use Contao\GroundingPageModel;
use Contao\GroundingSectionModel;
use Contao\Model\Collection;
use Contao\TestCase\ContaoTestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use Trassd\Contao\GroundingPages\Composer\GroundingPageComposer;
use Trassd\Contao\GroundingPages\Dto\GroundingPageDto;

#[AllowMockObjectsWithoutExpectations]
class GroundingPageComposerTest extends ContaoTestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        require_once \dirname(__DIR__, 2).'/contao/models/GroundingPageModel.php';
        require_once \dirname(__DIR__, 2).'/contao/models/GroundingSectionModel.php';
    }

    public function testComposesHeaderFieldsAndResolvesSchemaType(): void
    {
        $dto = (new GroundingPageComposer())->compose($this->mockPage([
            'title' => 'CERN',
            'entityType' => 'organization',
            'definition' => 'Europäische Organisation für Kernforschung.',
            'segment' => 'Forschung',
            'publisher' => 'Grounding Page Project',
            'language' => 'de',
        ]));

        $this->assertInstanceOf(GroundingPageDto::class, $dto);
        $this->assertSame('CERN', $dto->name);
        $this->assertSame('Organization', $dto->schemaType);
        $this->assertSame('de', $dto->inLanguage);
        $this->assertSame('Grounding Page Project', $dto->publisher);
        $this->assertSame([], $dto->sections);
    }

    public function testCustomSchemaTypeOverridesOntology(): void
    {
        $dto = (new GroundingPageComposer())->compose($this->mockPage([
            'title' => 'X',
            'entityType' => 'organization',
            'customSchemaType' => 'MedicalOrganization',
        ]));

        $this->assertSame('MedicalOrganization', $dto->schemaType);
    }

    public function testUnknownEntityTypeFallsBackToThing(): void
    {
        $dto = (new GroundingPageComposer())->compose($this->mockPage([
            'title' => 'X',
            'entityType' => 'nonsense',
        ]));

        $this->assertSame('Thing', $dto->schemaType);
    }

    public function testDateModifiedFallsBackThroughVerifiedTstampPublished(): void
    {
        $tstamp = 1_700_000_000;

        $dto = (new GroundingPageComposer())->compose($this->mockPage([
            'title' => 'X',
            'entityType' => 'organization',
            'dateVerified' => 0,
            'tstamp' => $tstamp,
            'datePublished' => 0,
        ]));

        $this->assertSame(date('Y-m-d', $tstamp), $dto->dateModified);
        $this->assertNull($dto->datePublished);
    }

    public function testSameAsIsTrimmedAndDeduplicated(): void
    {
        $dto = (new GroundingPageComposer())->compose($this->mockPage([
            'title' => 'X',
            'entityType' => 'organization',
            'sameAs' => serialize(['https://a/', 'https://a/', '  https://b/  ', '']),
        ]));

        $this->assertSame(['https://a/', 'https://b/'], $dto->sameAs);
    }

    public function testFactGridRowsAreTrimmedAndEmptyRowsDropped(): void
    {
        $section = $this->mockSection([
            'sectionType' => 'fact-grid',
            'sectionTitle' => 'Fakten',
            'factGrid' => serialize([
                ['label' => 'Gegründet', 'value' => '1954'],
                ['label' => '   ', 'value' => '   '],
                ['label' => '  HQ ', 'value' => ' Genf '],
            ]),
        ]);

        $dto = (new GroundingPageComposer())->compose($this->mockPage(['title' => 'X', 'entityType' => 'organization'], [$section]));

        $this->assertCount(1, $dto->sections);
        $this->assertSame(
            [['label' => 'Gegründet', 'value' => '1954'], ['label' => 'HQ', 'value' => 'Genf']],
            $dto->sections[0]->factGrid,
        );
    }

    public function testOnlyTheMatchingSectionTypeArrayIsPopulated(): void
    {
        $section = $this->mockSection([
            'sectionType' => 'faq',
            'sectionTitle' => 'FAQ',
            'faqItems' => serialize([['question' => 'Was?', 'answer' => 'Das.']]),
            'factGrid' => serialize([['label' => 'x', 'value' => 'y']]),
        ]);

        $dto = (new GroundingPageComposer())->compose($this->mockPage(['title' => 'X', 'entityType' => 'organization'], [$section]));

        $this->assertSame([['question' => 'Was?', 'answer' => 'Das.']], $dto->sections[0]->faq);
        $this->assertSame([], $dto->sections[0]->factGrid);
    }

    public function testSourcesSectionPopulatesSourcesAndIdentifiers(): void
    {
        $section = $this->mockSection([
            'sectionType' => 'sources',
            'sources' => serialize([['title' => 'Website', 'url' => 'https://home.cern/']]),
            'identifiers' => serialize([['label' => 'ROR', 'value' => 'https://ror.org/01ggx4157']]),
        ]);

        $dto = (new GroundingPageComposer())->compose($this->mockPage(['title' => 'X', 'entityType' => 'organization'], [$section]));

        $this->assertSame('https://home.cern/', $dto->sections[0]->sources[0]['url']);
        $this->assertSame('ROR', $dto->sections[0]->identifiers[0]['label']);
    }

    public function testComposesGovernanceFieldsAndDropsEmptyChangelogRows(): void
    {
        $dto = (new GroundingPageComposer())->compose($this->mockPage([
            'title' => 'X',
            'entityType' => 'person',
            'correctionContact' => '  info@example.com  ',
            'changelog' => serialize([
                ['date' => '2026-06-20', 'change' => 'Initiale Fassung'],
                ['date' => '', 'change' => ''],
            ]),
        ]));

        $this->assertSame('person', $dto->entityType);
        $this->assertSame('info@example.com', $dto->correctionContact);
        $this->assertSame([['date' => '2026-06-20', 'change' => 'Initiale Fassung']], $dto->changelog);
    }

    /**
     * @param array<string, mixed>        $properties
     * @param list<GroundingSectionModel> $sections
     */
    private function mockPage(array $properties, array $sections = []): GroundingPageModel
    {
        $page = $this->mockClassWithProperties(GroundingPageModel::class, $properties);
        $page
            ->method('getPublishedSections')
            ->willReturn([] === $sections ? null : new Collection($sections, 'tl_grounding_section'))
        ;

        return $page;
    }

    /**
     * @param array<string, mixed> $properties
     */
    private function mockSection(array $properties): GroundingSectionModel
    {
        return $this->mockClassWithProperties(GroundingSectionModel::class, $properties);
    }
}
