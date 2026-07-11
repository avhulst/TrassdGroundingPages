<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\Tests\Model;

use Contao\GroundingPageModel;
use Contao\GroundingSectionModel;
use Contao\Model;
use Contao\TestCase\ContaoTestCase;

class GroundingModelsTest extends ContaoTestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        require_once \dirname(__DIR__, 2).'/contao/models/GroundingPageModel.php';
        require_once \dirname(__DIR__, 2).'/contao/models/GroundingSectionModel.php';
    }

    public function testPageModelMapsToTable(): void
    {
        $this->assertSame('tl_grounding_page', GroundingPageModel::getTable());
        $this->assertTrue(is_subclass_of(GroundingPageModel::class, Model::class));
    }

    public function testSectionModelMapsToTable(): void
    {
        $this->assertSame('tl_grounding_section', GroundingSectionModel::getTable());
        $this->assertTrue(is_subclass_of(GroundingSectionModel::class, Model::class));
    }

    public function testPageModelHasConvenienceMethods(): void
    {
        $this->assertTrue(method_exists(GroundingPageModel::class, 'findPublishedByAlias'));
        $this->assertTrue(method_exists(GroundingPageModel::class, 'getPublishedSections'));
    }
}
