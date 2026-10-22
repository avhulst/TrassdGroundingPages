<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;
use Trassd\Contao\GroundingPages\GroundingStandard;

class GroundingStandardTest extends TestCase
{
    public function testVersionsMatchStandard(): void
    {
        $this->assertSame('1.6.1', GroundingStandard::BASE_VERSION);
        $this->assertSame('1.7', GroundingStandard::REVIEWED_VERSION);
        $this->assertSame('https://groundingpage.com/spec/', GroundingStandard::SPEC_URL);
    }

    public function testVersionDependsOnReview(): void
    {
        $this->assertSame('1.6.1', GroundingStandard::versionFor(false));
        $this->assertSame('1.7', GroundingStandard::versionFor(true));
    }

    public function testTemplateDataDefaultsToBaseVersion(): void
    {
        $this->assertSame(
            ['version' => '1.6.1', 'specUrl' => 'https://groundingpage.com/spec/'],
            GroundingStandard::templateData(),
        );
        $this->assertSame(
            ['version' => '1.7', 'specUrl' => 'https://groundingpage.com/spec/'],
            GroundingStandard::templateData(true),
        );
    }

    /**
     * Guard: Die Standardversion darf in keinem Template hart codiert sein.
     */
    public function testNoTemplateHardcodesAStandardVersion(): void
    {
        $files = (new Finder())->files()->in(\dirname(__DIR__).'/contao/templates')->name('*.twig');

        foreach ($files as $file) {
            $this->assertDoesNotMatchRegularExpression(
                '/[\'"]1\.[67](\.\d+)?[\'"]/',
                $file->getContents(),
                $file->getRelativePathname(),
            );
        }
    }
}
