<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\Tests\Twig;

use PHPUnit\Framework\TestCase;
use Trassd\Contao\GroundingPages\GroundingStandard;
use Trassd\Contao\GroundingPages\Twig\GroundingStandardExtension;

class GroundingStandardExtensionTest extends TestCase
{
    public function testExposesGroundingStandardFunction(): void
    {
        $functions = [];

        foreach ((new GroundingStandardExtension())->getFunctions() as $function) {
            $functions[$function->getName()] = $function->getCallable();
        }

        $this->assertArrayHasKey('grounding_standard', $functions);
        $this->assertSame(GroundingStandard::templateData(), ($functions['grounding_standard'])());
    }
}
