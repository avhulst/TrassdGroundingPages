<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;
use Trassd\Contao\GroundingPages\Review\ReviewFieldsValidator;

/*
 * Lehnt halbe Review-Angaben und Review-Daten in der Zukunft beim Speichern ab.
 * Das jeweils andere Feld kommt aus dem POST (auch als <feld>_<id> bei „Mehrere
 * bearbeiten“), sonst aus dem gespeicherten Datensatz.
 */
final readonly class ReviewFieldsListener
{
    public function __construct(
        private ReviewFieldsValidator $validator,
        private RequestStack $requestStack,
        private TranslatorInterface $translator,
    ) {
    }

    #[AsCallback(table: 'tl_grounding_page', target: 'fields.reviewerName.save')]
    public function onSaveReviewerName(mixed $value, DataContainer $dc): mixed
    {
        $this->assertValid((string) $value, $this->sibling('lastReviewed', $dc));

        return $value;
    }

    #[AsCallback(table: 'tl_grounding_page', target: 'fields.lastReviewed.save')]
    public function onSaveLastReviewed(mixed $value, DataContainer $dc): mixed
    {
        $this->assertValid($this->sibling('reviewerName', $dc), (string) ($value ?? ''));

        return $value;
    }

    private function assertValid(string $name, string $date): void
    {
        $error = $this->validator->validate($name, $date, time());

        if (null !== $error) {
            throw new \RuntimeException($this->translator->trans('tl_grounding_page.error.'.$error, [], 'contao_tl_grounding_page'));
        }
    }

    private function sibling(string $field, DataContainer $dc): string
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
