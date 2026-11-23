<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\Tests\Controller;

use Contao\ContentModel;
use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\CoreBundle\Routing\PageFinder;
use Contao\CoreBundle\Routing\ResponseContext\ResponseContextAccessor;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\GroundingPageModel;
use Contao\PageModel;
use Contao\TestCase\ContaoTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Trassd\Contao\GroundingPages\Composer\GroundingPageComposer;
use Trassd\Contao\GroundingPages\Controller\ContentElement\GroundingPageController;
use Trassd\Contao\GroundingPages\JsonLd\GroundingJsonLdBuilder;
use Trassd\Contao\GroundingPages\Reference\ConformanceResolver;
use Trassd\Contao\GroundingPages\Reference\EntityIdentityResolver;
use Trassd\Contao\GroundingPages\Reference\SameAsPolicy;

class GroundingPageControllerTest extends ContaoTestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        require_once \dirname(__DIR__, 2).'/contao/models/GroundingPageModel.php';
        require_once \dirname(__DIR__, 2).'/contao/models/GroundingSectionModel.php';
    }

    public function testUnpublishedOrMissingGroundingPageRendersNothing(): void
    {
        $adapter = $this->createAdapterMock(['findPublishedById']);
        $adapter
            ->expects($this->once())
            ->method('findPublishedById')
            ->with(42)
            ->willReturn(null)
        ;

        // Ohne Seite darf auch kein JSON-LD in den Response-Kontext gelangen.
        $accessor = $this->createMock(ResponseContextAccessor::class);
        $accessor
            ->expects($this->never())
            ->method('getResponseContext')
        ;

        // Ohne Seite wird das Template nie benutzt; ein Rendern würde am fehlenden
        // Callback scheitern.
        $template = (new \ReflectionClass(FragmentTemplate::class))->newInstanceWithoutConstructor();

        $response = $this->invokeGetResponse($adapter, $accessor, $template);

        $this->assertSame('', $response->getContent());
    }

    public function testPageUrlFallsBackToRequestWithoutQuery(): void
    {
        $request = Request::create('https://example.com/cern?page_n=2');

        $this->assertSame('https://example.com/cern', $this->pageUrl($request, null, $this->createStub(ContentUrlGenerator::class)));
    }

    public function testPageUrlUsesGeneratedUrlOfCurrentPageWithoutQueryAndFragment(): void
    {
        $page = $this->createClassWithPropertiesStub(PageModel::class, ['id' => 5]);
        $request = Request::create('https://example.com/cern?page_n=2');

        $generator = $this->createMock(ContentUrlGenerator::class);
        $generator
            ->expects($this->once())
            ->method('generate')
            ->with($page, [], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('https://www.example.com/de/cern?x=1#top')
        ;

        $this->assertSame('https://www.example.com/de/cern', $this->pageUrl($request, $page, $generator));
    }

    public function testPageUrlFallsBackToRequestWhenPageHasNoRoute(): void
    {
        $page = $this->createClassWithPropertiesStub(PageModel::class, ['id' => 5]);
        $request = Request::create('https://example.com/cern');

        $generator = $this->createStub(ContentUrlGenerator::class);
        $generator
            ->method('generate')
            ->willThrowException(new RouteNotFoundException('No route'))
        ;

        $this->assertSame('https://example.com/cern', $this->pageUrl($request, $page, $generator));
    }

    private function pageUrl(Request $request, PageModel|null $page, ContentUrlGenerator $generator): string
    {
        $pageFinder = $this->createMock(PageFinder::class);
        $pageFinder
            ->expects($this->once())
            ->method('getCurrentPage')
            ->with($request)
            ->willReturn($page)
        ;

        $controller = new GroundingPageController(
            $this->createContaoFrameworkStub(),
            new GroundingPageComposer(),
            new GroundingJsonLdBuilder($this->createStub(ResponseContextAccessor::class)),
            new EntityIdentityResolver(),
            new ConformanceResolver(new SameAsPolicy()),
            $generator,
            $pageFinder,
        );

        return (new \ReflectionMethod($controller, 'pageUrl'))->invoke($controller, $request);
    }

    private function invokeGetResponse(object $adapter, ResponseContextAccessor $accessor, FragmentTemplate $template): Response
    {
        $framework = $this->createContaoFrameworkStub([GroundingPageModel::class => $adapter]);
        $controller = new GroundingPageController(
            $framework,
            new GroundingPageComposer(),
            new GroundingJsonLdBuilder($accessor),
            new EntityIdentityResolver(),
            new ConformanceResolver(new SameAsPolicy()),
            $this->createStub(ContentUrlGenerator::class),
            $this->createStub(PageFinder::class),
        );
        $model = $this->createClassWithPropertiesStub(ContentModel::class, ['groundingPage' => 42]);

        return (new \ReflectionMethod($controller, 'getResponse'))->invoke($controller, $template, $model, new Request());
    }
}
