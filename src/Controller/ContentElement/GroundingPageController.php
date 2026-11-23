<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\Controller\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\CoreBundle\Routing\PageFinder;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\GroundingPageModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ExceptionInterface as RoutingException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Trassd\Contao\GroundingPages\Composer\GroundingPageComposer;
use Trassd\Contao\GroundingPages\JsonLd\GroundingJsonLdBuilder;
use Trassd\Contao\GroundingPages\Reference\ConformanceResolver;
use Trassd\Contao\GroundingPages\Reference\EntityIdentityResolver;

#[AsContentElement(type: 'grounding_page', category: 'includes', template: 'content_element/grounding_page')]
class GroundingPageController extends AbstractContentElementController
{
    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly GroundingPageComposer $composer,
        private readonly GroundingJsonLdBuilder $jsonLdBuilder,
        private readonly EntityIdentityResolver $identityResolver,
        private readonly ConformanceResolver $conformanceResolver,
        private readonly ContentUrlGenerator $urlGenerator,
        private readonly PageFinder $pageFinder,
    ) {
    }

    protected function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {
        $page = $this->framework->getAdapter(GroundingPageModel::class)->findPublishedById((int) $model->groundingPage);

        if (!$page instanceof GroundingPageModel) {
            return new Response();
        }

        $dto = $this->composer->compose($page);
        $identity = $this->identityResolver->resolve($dto, $this->pageUrl($request));
        $conformance = $this->conformanceResolver->resolve($dto, $identity);

        // Standard 2.0: Der Kasten „Hinweis für menschliche Leser“ wird nicht
        // mehr empfohlen.
        if ($conformance->isV2()) {
            $dto = $dto->withHumanNotice(false);
        }

        $this->jsonLdBuilder->addToResponseContext($dto, $identity);

        $template->set('grounding', $dto);
        $template->set('identity', $identity);
        $template->set('conformance', $conformance);

        return $template->getResponse();
    }

    /**
     * Kanonische absolute URL der aktuellen Seite, ohne Query-String (RI-1).
     */
    protected function pageUrl(Request $request): string
    {
        $pageModel = $this->pageFinder->getCurrentPage($request);

        if (null !== $pageModel) {
            try {
                return EntityIdentityResolver::stripQueryAndFragment(
                    $this->urlGenerator->generate($pageModel, [], UrlGeneratorInterface::ABSOLUTE_URL),
                );
            } catch (RoutingException) {
                // ContentUrlGenerator::generate() wirft nur Routing-Exceptions (z. B.
                // RouteNotFoundException für Seiten ohne routbare URL): Fallback unten.
            }
        }

        return $request->getSchemeAndHttpHost().$request->getBaseUrl().$request->getPathInfo();
    }
}
