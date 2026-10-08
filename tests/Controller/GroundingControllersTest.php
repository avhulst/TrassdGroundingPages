<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\Tests\Controller;

use Contao\ContentModel;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\CoreBundle\Routing\ResponseContext\ResponseContextAccessor;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\GroundingPageModel;
use Contao\TestCase\ContaoTestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Trassd\Contao\GroundingPages\Composer\GroundingPageComposer;
use Trassd\Contao\GroundingPages\Controller\ContentElement\GroundingPageController;
use Trassd\Contao\GroundingPages\GroundingStandard;
use Trassd\Contao\GroundingPages\JsonLd\GroundingJsonLdBuilder;

#[AllowMockObjectsWithoutExpectations]
class GroundingControllersTest extends ContaoTestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        require_once \dirname(__DIR__, 2).'/contao/models/GroundingPageModel.php';
        require_once \dirname(__DIR__, 2).'/contao/models/GroundingSectionModel.php';
    }

    public function testContentElementIsRegisteredWithCorrectAttributeArguments(): void
    {
        $attributes = (new \ReflectionClass(GroundingPageController::class))->getAttributes(AsContentElement::class);

        $this->assertCount(1, $attributes);
        $arguments = $attributes[0]->getArguments();

        $this->assertSame('grounding_page', $arguments['type']);
        $this->assertSame('includes', $arguments['category']);
        $this->assertSame('content_element/grounding_page', $arguments['template']);
    }

    public function testPassesStandardVersionAndSpecUrlToTemplate(): void
    {
        $page = $this->mockClassWithProperties(GroundingPageModel::class, ['title' => 'CERN', 'entityType' => 'organization']);
        $page->method('getPublishedSections')->willReturn(null);

        $adapter = $this->mockAdapter(['findById']);
        $adapter->method('findById')->willReturn($page);

        $framework = $this->mockContaoFramework([GroundingPageModel::class => $adapter]);

        $controller = new GroundingPageController(
            $framework,
            new GroundingPageComposer(),
            new GroundingJsonLdBuilder(new ResponseContextAccessor(new RequestStack())),
        );

        $template = new FragmentTemplate('content_element/grounding_page', static fn (): Response => new Response());
        $model = $this->mockClassWithProperties(ContentModel::class, ['groundingPage' => 1]);

        (new \ReflectionMethod($controller, 'getResponse'))->invoke($controller, $template, $model, new Request());

        $this->assertSame(
            ['version' => GroundingStandard::VERSION, 'specUrl' => GroundingStandard::SPEC_URL],
            $template->get('standard'),
        );
    }
}
