<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\Reference;

use Trassd\Contao\GroundingPages\GroundingStandard;

/*
 * Ergebnis der Konformitätsprüfung: die Version, die die Seite nennen darf, und die
 * Verstöße, die eine 2.0-Angabe verhindert haben.
 */
final readonly class ConformanceResult
{
    /**
     * @param list<string> $violations
     */
    public function __construct(
        public string $version,
        public array $violations = [],
    ) {
    }

    public function isV2(): bool
    {
        return GroundingStandard::V2_VERSION === $this->version;
    }
}
