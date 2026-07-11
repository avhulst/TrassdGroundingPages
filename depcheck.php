<?php

declare(strict_types=1);

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

return (new Configuration())
    // Eigene Contao-Legacy-Models (contao/models, kein PSR-4 → vom Scanner
    // nicht auffindbar).
    ->ignoreUnknownClasses([
        'Contao\GroundingPageModel',
        'Contao\GroundingSectionModel',
    ])
    // Contao-Konvention: das Manager-Plugin liegt in require-dev, die Plugin-Klasse
    // aber in src/ (zur Laufzeit von der Managed Edition bereitgestellt).
    ->ignoreErrorsOnPackage('contao/manager-plugin', [ErrorType::DEV_DEPENDENCY_IN_PROD])
;
