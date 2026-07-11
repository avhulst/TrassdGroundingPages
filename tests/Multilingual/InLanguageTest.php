<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\Tests\Multilingual;

use Contao\CoreBundle\Routing\ResponseContext\ResponseContextAccessor;
use Contao\GroundingPageModel;
use Contao\TestCase\ContaoTestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\RequestStack;
use Trassd\Contao\GroundingPages\Composer\GroundingPageComposer;
use Trassd\Contao\GroundingPages\JsonLd\GroundingJsonLdBuilder;

/**
 * Mehrsprachigkeit über separate Sprachbäume: je Datensatz trägt das
 * language-Feld die Sprache, die als inLanguage bis in den Graphen durchgereicht
 * wird (Model → DTO → WebPage).
 */
#[AllowMockObjectsWithoutExpectations]
class InLanguageTest extends ContaoTestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        require_once \dirname(__DIR__, 2).'/contao/models/GroundingPageModel.php';
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function languageProvider(): iterable
    {
        yield 'de' => ['de'];
        yield 'en' => ['en'];
    }

    #[DataProvider('languageProvider')]
    public function testRecordLanguageBecomesInLanguageInGraph(string $language): void
    {
        $page = $this->mockClassWithProperties(GroundingPageModel::class, [
            'title' => 'CERN',
            'entityType' => 'organization',
            'language' => $language,
        ]);

        $page
            ->method('getPublishedSections')
            ->willReturn(null)
        ;

        $dto = (new GroundingPageComposer())->compose($page);
        $this->assertSame($language, $dto->inLanguage);

        $webPage = (new GroundingJsonLdBuilder(new ResponseContextAccessor(new RequestStack())))
            ->buildWebPage($dto)
            ->toArray()
        ;

        $this->assertSame($language, $webPage['inLanguage']);
    }
}
