<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\Tests\Twig;

use PHPUnit\Framework\TestCase;
use Trassd\Contao\GroundingPages\GroundingStandard;
use Trassd\Contao\GroundingPages\Twig\GroundingStandardExtension;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

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
        $this->assertSame('1.7', ($functions['grounding_standard'])(true)['version']);
    }

    public function testFunctionPassesResolvedVersion(): void
    {
        $twig = new Environment(new ArrayLoader([
            't' => '{{ grounding_standard(true, "2.0").version }}',
        ]));
        $twig->addExtension(new GroundingStandardExtension());

        $this->assertSame('2.0', $twig->render('t'));
    }
}
