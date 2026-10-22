<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\Tests\Twig;

use Trassd\Contao\GroundingPages\Dto\GroundingPageDto;

class ReviewNoticeTemplateTest extends TwigTemplateTestCase
{
    public function testReviewedPageShowsNoticeWithMachineReadableDate(): void
    {
        $html = $this->notice(new GroundingPageDto(
            name: 'X',
            schemaType: 'Thing',
            reviewerName: 'Jane Doe',
            lastReviewed: '2026-10-02',
        ));

        $this->assertStringContainsString('class="grounding-page__review-notice"', $html);
        $this->assertStringContainsString(
            'grounding.review.notice[Jane Doe|<time datetime="2026-10-02">2026-10-02</time>]',
            $html,
        );
    }

    public function testReviewerNameIsEscaped(): void
    {
        $html = $this->notice(new GroundingPageDto(
            name: 'X',
            schemaType: 'Thing',
            reviewerName: '<b>Müller & Söhne</b>',
            lastReviewed: '2026-10-02',
        ));

        $this->assertStringContainsString('&lt;b&gt;Müller &amp; Söhne&lt;/b&gt;', $html);
        $this->assertStringNotContainsString('<b>', $html);
    }

    public function testUnreviewedPageRendersNothing(): void
    {
        $this->assertSame('', trim($this->notice(new GroundingPageDto(name: 'X', schemaType: 'Thing', reviewerName: 'Jane Doe'))));
        $this->assertSame('', trim($this->notice(new GroundingPageDto(name: 'X', schemaType: 'Thing', lastReviewed: '2026-10-02'))));
    }

    public function testFooterPlacesNoticeBeforeStandardText(): void
    {
        $html = $this->render('@Contao/grounding/_page.html.twig', [
            'grounding' => new GroundingPageDto(
                name: 'X',
                schemaType: 'Thing',
                reviewerName: 'Jane Doe',
                lastReviewed: '2026-10-02',
            ),
        ]);

        $notice = strpos($html, 'grounding-page__review-notice');
        $standard = strpos($html, 'grounding-page__standard-text');

        $this->assertNotFalse($notice);
        $this->assertNotFalse($standard);
        $this->assertLessThan($standard, $notice);
    }

    private function notice(GroundingPageDto $dto): string
    {
        return $this->render('@Contao/grounding/_review_notice.html.twig', ['grounding' => $dto]);
    }
}
