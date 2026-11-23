<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\DataContainer;
use Contao\Message;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Symfony\Contracts\Translation\TranslatorInterface;
use Trassd\Contao\GroundingPages\GroundingStandard;
use Trassd\Contao\GroundingPages\Reference\OwnHostsProvider;
use Trassd\Contao\GroundingPages\Reference\SameAsPolicy;
use Trassd\Contao\GroundingPages\Review\ReviewFieldsValidator;

/*
 * Standard 2.0 beim Speichern: absolute Identifier, „Geprüft nach“ nur mit Review,
 * sameAs nach RI-3. Bei „Geprüft nach 2.0“ blockiert ein Verstoß, sonst nur Hinweis.
 */
final readonly class ReferenceFieldsListener
{
    public function __construct(
        private SameAsPolicy $policy,
        private ReviewFieldsValidator $validator,
        private DcaSiblingReader $siblings,
        private OwnHostsProvider $ownHosts,
        private TranslatorInterface $translator,
        private ContaoFramework $framework,
        private Connection|null $connection = null,
    ) {
    }

    #[AsCallback(table: 'tl_grounding_page', target: 'fields.reviewStandard.save')]
    public function onSaveReviewStandard(mixed $value, DataContainer $dc): mixed
    {
        $error = $this->validator->validateStandard(
            (string) $value,
            $this->siblings->read('reviewerName', $dc),
            $this->siblings->read('lastReviewed', $dc),
        );

        if (null !== $error) {
            throw new \RuntimeException($this->trans('error.'.$error));
        }

        return $value;
    }

    #[AsCallback(table: 'tl_grounding_page', target: 'fields.entityId.save')]
    #[AsCallback(table: 'tl_grounding_page', target: 'fields.anchorUrl.save')]
    public function onSaveAbsoluteUrl(mixed $value, DataContainer $dc): mixed
    {
        $url = trim((string) $value);

        if ('' !== $url && !SameAsPolicy::isAbsoluteHttpUrl($url)) {
            throw new \RuntimeException($this->trans('error.notAbsoluteUrl', [$url]));
        }

        return $value;
    }

    #[AsCallback(table: 'tl_grounding_page', target: 'fields.sameAs.save')]
    public function onSaveSameAs(mixed $value, DataContainer $dc): mixed
    {
        // listWizard liefert den Wert bereits serialisiert (DC_Table::save).
        $urls = array_values(array_filter(array_map(
            static fn (mixed $row): string => trim((string) (\is_array($row) ? reset($row) : $row)),
            StringUtil::deserialize($value, true),
        )));

        $this->check(
            $urls,
            GroundingStandard::V2_VERSION === $this->siblings->read('reviewStandard', $dc),
            $this->siblings->read('entityId', $dc),
            $this->siblings->read('anchorUrl', $dc),
        );

        return $value;
    }

    #[AsCallback(table: 'tl_grounding_section', target: 'fields.identifiers.save')]
    public function onSaveSectionIdentifiers(mixed $value, DataContainer $dc): mixed
    {
        $urls = [];

        foreach (StringUtil::deserialize($value, true) as $row) {
            $candidate = \is_array($row) ? trim((string) ($row['value'] ?? '')) : '';

            if (str_starts_with($candidate, 'http://') || str_starts_with($candidate, 'https://')) {
                $urls[] = $candidate;
            }
        }

        $pid = (int) ($dc->getCurrentRecord()['pid'] ?? 0);
        $parent = $pid > 0 && null !== $this->connection
            ? $this->connection->fetchAssociative('SELECT reviewStandard, entityId, anchorUrl FROM tl_grounding_page WHERE id = ?', [$pid])
            : false;

        $this->check(
            $urls,
            GroundingStandard::V2_VERSION === ($parent['reviewStandard'] ?? ''),
            (string) ($parent['entityId'] ?? ''),
            (string) ($parent['anchorUrl'] ?? ''),
        );

        return $value;
    }

    /**
     * @param list<string> $urls
     */
    private function check(array $urls, bool $blocking, string $entityId, string $anchorUrl): void
    {
        $violations = $this->policy->violations($urls, $this->ownHosts->hosts($entityId, $anchorUrl));

        if ([] === $violations) {
            return;
        }

        $messages = [];

        foreach ($violations as $url => $violation) {
            $messages[] = $this->translator->trans($violation->translationKey(), [$url], 'contao_tl_grounding_page');
        }

        if ($blocking) {
            throw new \RuntimeException(implode(' ', $messages));
        }

        $message = $this->framework->getAdapter(Message::class);

        foreach ($messages as $text) {
            $message->addInfo($text);
        }
    }

    /**
     * @param list<string> $params
     */
    private function trans(string $key, array $params = []): string
    {
        return $this->translator->trans('tl_grounding_page.'.$key, $params, 'contao_tl_grounding_page');
    }
}
