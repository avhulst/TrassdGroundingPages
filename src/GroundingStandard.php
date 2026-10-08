<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages;

/*
 * Einzige Quelle für die umgesetzte Version des Grounding Page Standards.
 */
final class GroundingStandard
{
    public const string VERSION = '1.6.1';

    public const string SPEC_URL = 'https://groundingpage.com/spec/';

    /**
     * Template-Variable `standard` für den Standard-Footer.
     *
     * @return array{version: string, specUrl: string}
     */
    public static function templateData(): array
    {
        return ['version' => self::VERSION, 'specUrl' => self::SPEC_URL];
    }
}
