<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages;

/*
 * Einzige Quelle für die Versionen des Grounding Page Standards. Jede Seite nennt
 * die Version, die sie tatsächlich erfüllt. versionFor() kennt nur Review ja/nein
 * und liefert deshalb höchstens 1.7; 2.0 entscheidet der ConformanceResolver.
 */
final class GroundingStandard
{
    public const string BASE_VERSION = '1.6.1';

    public const string REVIEWED_VERSION = '1.7';

    public const string V2_VERSION = '2.0';

    public const string SPEC_URL = 'https://groundingpage.com/spec/';

    public static function versionFor(bool $reviewed): string
    {
        return $reviewed ? self::REVIEWED_VERSION : self::BASE_VERSION;
    }

    /**
     * Template-Daten für den Standard-Footer. Ohne aufgelöste Version gilt
     * versionFor(), ein überschriebenes Template behauptet also nie fälschlich 2.0.
     *
     * @return array{version: string, specUrl: string}
     */
    public static function templateData(bool $reviewed = false, string|null $version = null): array
    {
        return [
            'version' => null !== $version && '' !== $version ? $version : self::versionFor($reviewed),
            'specUrl' => self::SPEC_URL,
        ];
    }
}
