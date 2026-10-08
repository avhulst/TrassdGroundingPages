<?php

declare(strict_types=1);

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

// Die Legacy-Models (contao/models) findet der Scanner über die classmap
// in composer.json.
return (new Configuration())
    // Contao-Konvention: das Manager-Plugin liegt in require-dev, die Plugin-Klasse
    // aber in src/ (zur Laufzeit von der Managed Edition bereitgestellt).
    ->ignoreErrorsOnPackage('contao/manager-plugin', [ErrorType::DEV_DEPENDENCY_IN_PROD])
;
