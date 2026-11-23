<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\Reference;

use Contao\CoreBundle\Monolog\ContaoContext;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Trassd\Contao\GroundingPages\Dto\GroundingPageDto;
use Trassd\Contao\GroundingPages\GroundingStandard;

/*
 * Entscheidet, welche Standardversion eine Seite nennt. 2.0 nur nach explizitem
 * 2.0-Review und bestandenen Laufzeitprüfungen; sonst höchstens 1.7, damit nie
 * fälschlich 2.0 behauptet wird (Standard 2.0, P-10).
 */
final readonly class ConformanceResolver
{
    public function __construct(
        private SameAsPolicy $policy,
        #[Autowire(service: 'monolog.logger.contao.error')]
        private LoggerInterface|null $logger = null,
        #[Autowire(service: 'cache.app')]
        private CacheInterface|null $cache = null,
    ) {
    }

    public function resolve(GroundingPageDto $dto, EntityIdentity $identity): ConformanceResult
    {
        if (!$dto->isReviewed()) {
            return new ConformanceResult(GroundingStandard::BASE_VERSION);
        }

        if (GroundingStandard::V2_VERSION !== $dto->reviewStandard) {
            return new ConformanceResult(GroundingStandard::REVIEWED_VERSION);
        }

        $violations = [];

        // Ungültige Rohwerte ignoriert der EntityIdentityResolver; die Seite darf dann
        // trotzdem nicht 2.0 nennen.
        if (!SameAsPolicy::isAbsoluteHttpUrl($identity->entityId) || self::isInvalidStoredUrl($dto->entityId)) {
            $violations[] = 'entityId';
        }

        if (self::isInvalidStoredUrl($dto->anchorUrl)) {
            $violations[] = 'anchorUrl';
        }

        $ownHosts = array_values(array_filter(array_map(
            SameAsPolicy::hostOf(...),
            [$identity->pageUrl, $identity->entityId, $identity->anchorUrl],
        )));

        foreach ($this->policy->violations(array_values(array_unique([...$dto->sameAs, ...$dto->sameAsUrls()])), $ownHosts) as $url => $violation) {
            $violations[] = 'sameAs:'.$violation->value.':'.$url;
        }

        if ([] === $violations) {
            return new ConformanceResult(GroundingStandard::V2_VERSION);
        }

        $this->logOnce($dto, $identity, $violations);

        return new ConformanceResult(GroundingStandard::REVIEWED_VERSION, $violations);
    }

    /**
     * Jeder Frontend-Aufruf würde sonst einen tl_log-Eintrag schreiben: dieselbe
     * Seite mit denselben Verstößen nur einmal pro Tag melden.
     *
     * @param list<string> $violations
     */
    private function logOnce(GroundingPageDto $dto, EntityIdentity $identity, array $violations): void
    {
        $logger = $this->logger;

        if (null === $logger) {
            return;
        }

        $log = static function () use ($logger, $dto, $identity, $violations): void {
            $logger->warning(
                \sprintf('Grounding Page "%s" (%s) is reviewed against standard 2.0 but fails reference integrity: %s. Stating 1.7.', $dto->name, $identity->pageUrl, implode(', ', $violations)),
                ['contao' => new ContaoContext(self::class.'::resolve', ContaoContext::ERROR)],
            );
        };

        if (null === $this->cache) {
            $log();

            return;
        }

        $this->cache->get(
            'trassd_grounding_conformance_'.sha1($identity->pageUrl.'|'.implode(',', $violations)),
            static function (ItemInterface $item) use ($log): bool {
                $item->expiresAfter(86400);
                $log();

                return true;
            },
        );
    }

    private static function isInvalidStoredUrl(string $value): bool
    {
        return '' !== $value && !SameAsPolicy::isAbsoluteHttpUrl($value);
    }
}
