<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
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
        private DcaSiblingReader $siblings,
        private TranslatorInterface $translator,
    ) {
    }

    #[AsCallback(table: 'tl_grounding_page', target: 'fields.reviewerName.save')]
    public function onSaveReviewerName(mixed $value, DataContainer $dc): mixed
    {
        $this->assertValid((string) $value, $this->siblings->read('lastReviewed', $dc));

        return $value;
    }

    #[AsCallback(table: 'tl_grounding_page', target: 'fields.lastReviewed.save')]
    public function onSaveLastReviewed(mixed $value, DataContainer $dc): mixed
    {
        $this->assertValid($this->siblings->read('reviewerName', $dc), (string) ($value ?? ''));

        return $value;
    }

    private function assertValid(string $name, string $date): void
    {
        $error = $this->validator->validate($name, $date, time());

        if (null !== $error) {
            throw new \RuntimeException($this->translator->trans('tl_grounding_page.error.'.$error, [], 'contao_tl_grounding_page'));
        }
    }
}
