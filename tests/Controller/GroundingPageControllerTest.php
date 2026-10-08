<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\Tests\Controller;

use Contao\ContentModel;
use Contao\CoreBundle\Routing\ResponseContext\ResponseContextAccessor;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\GroundingPageModel;
use Contao\TestCase\ContaoTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Trassd\Contao\GroundingPages\Composer\GroundingPageComposer;
use Trassd\Contao\GroundingPages\Controller\ContentElement\GroundingPageController;
use Trassd\Contao\GroundingPages\JsonLd\GroundingJsonLdBuilder;

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

    private function invokeGetResponse(object $adapter, ResponseContextAccessor $accessor, FragmentTemplate $template): Response
    {
        $framework = $this->createContaoFrameworkStub([GroundingPageModel::class => $adapter]);
        $controller = new GroundingPageController($framework, new GroundingPageComposer(), new GroundingJsonLdBuilder($accessor));
        $model = $this->createClassWithPropertiesStub(ContentModel::class, ['groundingPage' => 42]);

        return (new \ReflectionMethod($controller, 'getResponse'))->invoke($controller, $template, $model, new Request());
    }
}
