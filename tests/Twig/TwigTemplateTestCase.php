<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\Tests\Twig;

use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;

/**
 * Rendert Bundle-Templates mit reinem Twig. Der trans-Filter ist ein Stub: Er gibt
 * den Schlüssel zurück, Parameter folgen in eckigen Klammern ("key[a|b]").
 */
abstract class TwigTemplateTestCase extends TestCase
{
    /**
     * @param array<string, mixed> $context
     */
    protected function render(string $template, array $context): string
    {
        $loader = new FilesystemLoader();
        $loader->addPath(\dirname(__DIR__, 2).'/contao/templates', 'Contao');

        $twig = new Environment($loader, ['autoescape' => 'html']);
        $twig->addFilter(new TwigFilter(
            'trans',
            static fn (string $id, array $params = [], string|null $domain = null): string => [] === $params
                ? $id
                : $id.'['.implode('|', array_map(strval(...), $params)).']',
        ));

        return $twig->render($template, $context);
    }
}
