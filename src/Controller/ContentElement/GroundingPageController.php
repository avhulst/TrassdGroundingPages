<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\Controller\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\GroundingPageModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Trassd\Contao\GroundingPages\Composer\GroundingPageComposer;
use Trassd\Contao\GroundingPages\GroundingStandard;
use Trassd\Contao\GroundingPages\JsonLd\GroundingJsonLdBuilder;

#[AsContentElement(type: 'grounding_page', category: 'includes', template: 'content_element/grounding_page')]
class GroundingPageController extends AbstractContentElementController
{
    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly GroundingPageComposer $composer,
        private readonly GroundingJsonLdBuilder $jsonLdBuilder,
    ) {
    }

    protected function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {
        $page = $this->framework->getAdapter(GroundingPageModel::class)->findById((int) $model->groundingPage);

        if (!$page instanceof GroundingPageModel) {
            return new Response();
        }

        $dto = $this->composer->compose($page);
        $this->jsonLdBuilder->addToResponseContext($dto);

        $template->set('grounding', $dto);
        $template->set('standard', ['version' => GroundingStandard::VERSION, 'specUrl' => GroundingStandard::SPEC_URL]);

        return $template->getResponse();
    }
}
