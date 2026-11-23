<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\EventListener\DataContainer;

use Contao\DataContainer;
use Symfony\Component\HttpFoundation\RequestStack;

/*
 * Liest ein Geschwisterfeld beim Speichern: erst POST (auch <feld>_<id> bei
 * „Mehrere bearbeiten“), sonst den gespeicherten Datensatz.
 */
final readonly class DcaSiblingReader
{
    public function __construct(private RequestStack $requestStack)
    {
    }

    public function read(string $field, DataContainer $dc): string
    {
        $post = $this->requestStack->getCurrentRequest()?->request;

        foreach ([$field, $field.'_'.$dc->id] as $key) {
            if (null !== $post && $post->has($key)) {
                return trim((string) $post->get($key));
            }
        }

        $record = $dc->getCurrentRecord();

        return trim((string) ($record[$field] ?? ''));
    }
}
