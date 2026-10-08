<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\Tests\Twig;

use Trassd\Contao\GroundingPages\Dto\GroundingPageDto;

class HumanNoticeTemplateTest extends TwigTemplateTestCase
{
    public function testHumanNoticeIsRenderedByDefault(): void
    {
        $html = $this->header(new GroundingPageDto(name: 'X', schemaType: 'Thing'));

        $this->assertStringContainsString('grounding-page__human-notice', $html);
    }

    public function testHumanNoticeIsOmittedWhenDisabled(): void
    {
        $html = $this->header(new GroundingPageDto(name: 'X', schemaType: 'Thing', showHumanNotice: false));

        $this->assertStringNotContainsString('grounding-page__human-notice', $html);
    }

    private function header(GroundingPageDto $dto): string
    {
        return $this->render('@Contao/grounding/section/_entity_header.html.twig', ['grounding' => $dto]);
    }
}
