<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\Reference;

use Contao\CoreBundle\Monolog\ContaoContext;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
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

        if (!SameAsPolicy::isAbsoluteHttpUrl($identity->entityId)) {
            $violations[] = 'entityId';
        }

        $ownHosts = array_values(array_filter(array_map(
            SameAsPolicy::hostOf(...),
            [$identity->pageUrl, $identity->entityId, $identity->anchorUrl],
        )));

        foreach ($this->policy->violations($dto->sameAsUrls(), $ownHosts) as $url => $violation) {
            $violations[] = 'sameAs:'.$violation->value.':'.$url;
        }

        if ([] === $violations) {
            return new ConformanceResult(GroundingStandard::V2_VERSION);
        }

        $this->logger?->warning(
            \sprintf('Grounding Page "%s" (%s) is reviewed against standard 2.0 but fails reference integrity: %s. Stating 1.7.', $dto->name, $identity->pageUrl, implode(', ', $violations)),
            ['contao' => new ContaoContext(__METHOD__, ContaoContext::ERROR)],
        );

        return new ConformanceResult(GroundingStandard::REVIEWED_VERSION, $violations);
    }
}
