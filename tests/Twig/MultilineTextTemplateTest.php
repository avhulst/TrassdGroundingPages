<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\Tests\Twig;

use Trassd\Contao\GroundingPages\Dto\GroundingPageDto;

class MultilineTextTemplateTest extends TwigTemplateTestCase
{
    public function testDefinitionPreservesLineBreaks(): void
    {
        $html = $this->header(new GroundingPageDto(name: 'CERN', schemaType: 'Organization', definition: "Erste Zeile.\nZweite Zeile."));

        $this->assertStringContainsString('Erste Zeile.<br />', $html);
        $this->assertStringContainsString('Zweite Zeile.', $html);
    }

    public function testDistinctionPreservesLineBreaks(): void
    {
        $html = $this->header(new GroundingPageDto(name: 'CERN', schemaType: 'Organization', distinction: "Nicht ESA.\nNicht ITER."));

        $this->assertStringContainsString('Nicht ESA.<br />', $html);
    }

    public function testMarkupInMultilineTextStaysEscaped(): void
    {
        $html = $this->header(new GroundingPageDto(
            name: 'X',
            schemaType: 'Thing',
            definition: "<script>alert(1)</script>\nZeile",
            distinction: "<b>fett</b>\nZeile",
        ));

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringNotContainsString('<b>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }

    private function header(GroundingPageDto $dto): string
    {
        return $this->render('@Contao/grounding/section/_entity_header.html.twig', ['grounding' => $dto]);
    }
}
