<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\Tests\Controller;

use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use PHPUnit\Framework\TestCase;
use Trassd\Contao\GroundingPages\Controller\ContentElement\GroundingPageController;

class GroundingControllersTest extends TestCase
{
    public function testContentElementIsRegisteredWithCorrectAttributeArguments(): void
    {
        $attributes = (new \ReflectionClass(GroundingPageController::class))->getAttributes(AsContentElement::class);

        $this->assertCount(1, $attributes);
        $arguments = $attributes[0]->getArguments();

        $this->assertSame('grounding_page', $arguments['type']);
        $this->assertSame('includes', $arguments['category']);
        $this->assertSame('content_element/grounding_page', $arguments['template']);
    }
}
