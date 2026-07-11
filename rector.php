<?php

declare(strict_types=1);

use Contao\Rector\Set\ContaoSetList;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPhpSets(php83: true)
    ->withSets([ContaoSetList::CONTAO_53])
    ->withPaths([
        __DIR__.'/src',
        __DIR__.'/tests',
    ])
    ->withRootFiles()
    ->withParallel()
    ->withCache(sys_get_temp_dir().'/rector/trassd-grounding-pages')
;
