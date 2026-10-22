<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages;

/*
 * Einzige Quelle für die Versionen des Grounding Page Standards. Seit v1.7 nennt
 * jede Seite die Version, die sie tatsächlich erfüllt: ohne dokumentiertes
 * menschliches Review bleibt sie auf der Basisversion.
 */
final class GroundingStandard
{
    public const string BASE_VERSION = '1.6.1';

    public const string REVIEWED_VERSION = '1.7';

    public const string SPEC_URL = 'https://groundingpage.com/spec/';

    public static function versionFor(bool $reviewed): string
    {
        return $reviewed ? self::REVIEWED_VERSION : self::BASE_VERSION;
    }

    /**
     * Template-Daten für den Standard-Footer.
     *
     * @return array{version: string, specUrl: string}
     */
    public static function templateData(bool $reviewed = false): array
    {
        return ['version' => self::versionFor($reviewed), 'specUrl' => self::SPEC_URL];
    }
}
