<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\Twig;

use Trassd\Contao\GroundingPages\GroundingStandard;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/*
 * Stellt die Standardversion als Twig-Funktion bereit, damit der Footer auch in
 * überschriebenen Templates ohne durchgereichte Variable funktioniert.
 */
final class GroundingStandardExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [new TwigFunction('grounding_standard', GroundingStandard::templateData(...))];
    }
}
