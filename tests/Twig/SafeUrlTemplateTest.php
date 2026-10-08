<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\Tests\Twig;

use Trassd\Contao\GroundingPages\Dto\GroundingPageDto;
use Trassd\Contao\GroundingPages\Dto\GroundingSectionDto;

class SafeUrlTemplateTest extends TwigTemplateTestCase
{
    public function testSourceWithUnsafeUrlIsPlainText(): void
    {
        $html = $this->renderPage(new GroundingPageDto(name: 'X', schemaType: 'Thing', sections: [
            new GroundingSectionDto(sectionType: 'sources', sources: [['title' => 'Böse', 'url' => 'javascript:alert(1)']]),
        ]));

        $this->assertStringNotContainsString('javascript:', $html);
        $this->assertStringContainsString('<li>Böse</li>', $html);
    }

    public function testSourceWithoutUrlRendersNoEmptyHref(): void
    {
        $html = $this->renderPage(new GroundingPageDto(name: 'X', schemaType: 'Thing', sections: [
            new GroundingSectionDto(sectionType: 'sources', sources: [['title' => 'Nur Titel', 'url' => '']]),
        ]));

        $this->assertStringNotContainsString('href=""', $html);
        $this->assertStringContainsString('<li>Nur Titel</li>', $html);
    }

    public function testSourceWithHttpUrlStaysLinked(): void
    {
        $html = $this->renderPage(new GroundingPageDto(name: 'X', schemaType: 'Thing', sections: [
            new GroundingSectionDto(sectionType: 'sources', sources: [['title' => 'Website', 'url' => 'https://home.cern/']]),
        ]));

        $this->assertStringContainsString('<a href="https://home.cern/" rel="nofollow noopener">Website</a>', $html);
    }

    public function testUnsafeCorrectionContactIsPlainText(): void
    {
        $html = $this->renderPage(new GroundingPageDto(name: 'X', schemaType: 'Thing', correctionContact: 'javascript:alert(1)'));

        $this->assertStringNotContainsString('href="javascript:', $html);
        $this->assertStringContainsString('javascript:alert(1)', $html);
    }

    public function testCorrectionContactEmailAndUrlAreLinked(): void
    {
        $mail = $this->renderPage(new GroundingPageDto(name: 'X', schemaType: 'Thing', correctionContact: 'info@example.com'));
        $url = $this->renderPage(new GroundingPageDto(name: 'X', schemaType: 'Thing', correctionContact: 'https://example.com/kontakt'));

        $this->assertStringContainsString('href="mailto:info@example.com"', $mail);
        $this->assertStringContainsString('href="https://example.com/kontakt"', $url);
    }

    private function renderPage(GroundingPageDto $dto): string
    {
        return $this->render('@Contao/grounding/_page.html.twig', ['grounding' => $dto]);
    }
}
