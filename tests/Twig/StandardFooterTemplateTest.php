<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\Tests\Twig;

use Trassd\Contao\GroundingPages\Dto\GroundingPageDto;

class StandardFooterTemplateTest extends TwigTemplateTestCase
{
    public function testUnreviewedPageShowsBaseVersion(): void
    {
        $html = $this->render('@Contao/grounding/_page.html.twig', [
            'grounding' => new GroundingPageDto(name: 'CERN', schemaType: 'Organization'),
        ]);

        $this->assertStringContainsString('grounding.detail.standardLinkText[1.6.1]', $html);
        $this->assertStringContainsString('href="https://groundingpage.com/spec/"', $html);
        $this->assertStringNotContainsString('[1.7]', $html);
    }

    public function testReviewedPageShowsReviewedVersion(): void
    {
        $html = $this->render('@Contao/grounding/_page.html.twig', [
            'grounding' => new GroundingPageDto(
                name: 'CERN',
                schemaType: 'Organization',
                reviewerName: 'Jane Doe',
                lastReviewed: '2026-10-02',
            ),
        ]);

        $this->assertStringContainsString('grounding.detail.standardLinkText[1.7]', $html);
        $this->assertStringNotContainsString('[1.6.1]', $html);
    }

    public function testHalfReviewDataKeepsBaseVersion(): void
    {
        $html = $this->render('@Contao/grounding/_page.html.twig', [
            'grounding' => new GroundingPageDto(name: 'CERN', schemaType: 'Organization', reviewerName: 'Jane Doe'),
        ]);

        $this->assertStringContainsString('grounding.detail.standardLinkText[1.6.1]', $html);
    }

    public function testDateModifiedIsLabelledAsLastUpdated(): void
    {
        $html = $this->render('@Contao/grounding/_page.html.twig', [
            'grounding' => new GroundingPageDto(name: 'CERN', schemaType: 'Organization', dateModified: '2026-10-08'),
        ]);

        $this->assertMatchesRegularExpression('/grounding\.detail\.standardTextUpdated\[.*\|1\.6\.1\|2026-10-08\]/s', $html);
        $this->assertStringNotContainsString('standardTextVerified', $html);
    }
}
