<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;
use Trassd\Contao\GroundingPages\GroundingStandard;

class GroundingStandardTest extends TestCase
{
    public function testVersionIsCurrentStandard(): void
    {
        $this->assertSame('1.6.1', GroundingStandard::VERSION);
        $this->assertSame('https://groundingpage.com/spec/', GroundingStandard::SPEC_URL);
    }

    /**
     * Guard: Die Standardversion darf in keinem Template hart codiert sein.
     */
    public function testNoTemplateHardcodesAStandardVersion(): void
    {
        $files = (new Finder())->files()->in(\dirname(__DIR__).'/contao/templates')->name('*.twig');

        foreach ($files as $file) {
            $this->assertDoesNotMatchRegularExpression(
                '/[\'"]1\.6(\.\d+)?[\'"]/',
                $file->getContents(),
                $file->getRelativePathname(),
            );
        }
    }
}
