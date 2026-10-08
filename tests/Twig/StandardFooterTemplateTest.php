<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\Tests\Twig;

use Trassd\Contao\GroundingPages\Dto\GroundingPageDto;
use Trassd\Contao\GroundingPages\GroundingStandard;

class StandardFooterTemplateTest extends TwigTemplateTestCase
{
    public function testFooterShowsVersionAndSpecUrlFromStandardVariable(): void
    {
        $html = $this->render('@Contao/grounding/_page.html.twig', [
            'grounding' => new GroundingPageDto(name: 'CERN', schemaType: 'Organization'),
            'standard' => ['version' => GroundingStandard::VERSION, 'specUrl' => GroundingStandard::SPEC_URL],
        ]);

        $this->assertStringContainsString('grounding.detail.standardLinkText[1.6.1]', $html);
        $this->assertStringContainsString('href="https://groundingpage.com/spec/"', $html);
        $this->assertStringNotContainsString('[1.6]', $html);
    }

    public function testVerifiedVariantCarriesVersionAndDate(): void
    {
        $html = $this->render('@Contao/grounding/_page.html.twig', [
            'grounding' => new GroundingPageDto(name: 'CERN', schemaType: 'Organization', dateModified: '2026-10-08'),
            'standard' => ['version' => '1.6.1', 'specUrl' => 'https://groundingpage.com/spec/'],
        ]);

        $this->assertMatchesRegularExpression('/grounding\.detail\.standardTextVerified\[.*\|1\.6\.1\|2026-10-08\]/s', $html);
    }
}
