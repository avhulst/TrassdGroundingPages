<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Trassd\Contao\GroundingPages\TrassdGroundingPagesBundle;

class Plugin implements BundlePluginInterface
{
    public function getBundles(ParserInterface $parser): array
    {
        return [
            BundleConfig::create(TrassdGroundingPagesBundle::class)
                ->setLoadAfter([ContaoCoreBundle::class]),
        ];
    }
}
