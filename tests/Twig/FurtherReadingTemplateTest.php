<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\Tests\Twig;

use Trassd\Contao\GroundingPages\Dto\GroundingPageDto;
use Trassd\Contao\GroundingPages\Dto\GroundingSectionDto;

class FurtherReadingTemplateTest extends TwigTemplateTestCase
{
    public function testRendersLinkedEntriesWithEntityNameInFallbackHeading(): void
    {
        $html = $this->renderPage([['title' => 'Annual Report', 'url' => 'https://home.cern/report']]);

        $this->assertStringContainsString('grounding-page__further-reading', $html);
        $this->assertStringContainsString('<h2>grounding.detail.furtherReading[CERN]</h2>', $html);
        $this->assertStringContainsString('<a href="https://home.cern/report" rel="noopener">Annual Report</a>', $html);
    }

    public function testCustomSectionTitleWins(): void
    {
        $html = $this->renderPage([['title' => 'A', 'url' => 'https://a/']], 'CERN: Weiterlesen');

        $this->assertStringContainsString('<h2>CERN: Weiterlesen</h2>', $html);
    }

    public function testTitleOnlyEntryIsPlainText(): void
    {
        $html = $this->renderPage([['title' => 'Nur Titel', 'url' => '']]);

        $this->assertStringContainsString('<li>Nur Titel</li>', $html);
    }

    public function testUrlWithoutTitleUsesUrlAsLinkText(): void
    {
        $html = $this->renderPage([['title' => '', 'url' => 'https://a/']]);

        $this->assertStringContainsString('<a href="https://a/" rel="noopener">https://a/</a>', $html);
    }

    public function testNonHttpUrlIsNeverRenderedAsLink(): void
    {
        $html = $this->renderPage([
            ['title' => 'Böse', 'url' => 'javascript:alert(1)'],
            ['title' => '', 'url' => 'ftp://x/'],
        ]);

        $this->assertStringNotContainsString('javascript:', $html);
        $this->assertStringNotContainsString('ftp://', $html);
        $this->assertStringContainsString('<li>Böse</li>', $html);
    }

    /**
     * @param list<array<string, string>> $rows
     */
    private function renderPage(array $rows, string $title = ''): string
    {
        return $this->render('@Contao/grounding/_page.html.twig', [
            'grounding' => new GroundingPageDto(
                name: 'CERN',
                schemaType: 'Organization',
                sections: [new GroundingSectionDto(sectionType: 'further-reading', sectionTitle: $title, furtherReading: $rows)],
            ),
            'standard' => ['version' => '1.6.1', 'specUrl' => 'https://groundingpage.com/spec/'],
        ]);
    }
}
