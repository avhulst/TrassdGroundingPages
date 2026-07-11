<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\Tests;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use PHPUnit\Framework\TestCase;
use Trassd\Contao\GroundingPages\ContaoManager\Plugin;
use Trassd\Contao\GroundingPages\TrassdGroundingPagesBundle;

class BundleBootTest extends TestCase
{
    public function testBundleIsInstantiable(): void
    {
        $this->assertInstanceOf(TrassdGroundingPagesBundle::class, new TrassdGroundingPagesBundle());
    }

    public function testPluginRegistersExactlyOneBundle(): void
    {
        $configs = (new Plugin())->getBundles($this->createStub(ParserInterface::class));

        $this->assertCount(1, $configs);
        $this->assertInstanceOf(BundleConfig::class, $configs[0]);
        $this->assertSame(TrassdGroundingPagesBundle::class, $configs[0]->getName());
    }

    public function testPluginLoadsAfterContaoCore(): void
    {
        $configs = (new Plugin())->getBundles($this->createStub(ParserInterface::class));

        $this->assertSame([ContaoCoreBundle::class], $configs[0]->getLoadAfter());
    }
}
