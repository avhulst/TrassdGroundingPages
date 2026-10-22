<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\Tests\Enum;

use PHPUnit\Framework\TestCase;
use Trassd\Contao\GroundingPages\Enum\ReviewerType;

class ReviewerTypeTest extends TestCase
{
    public function testValuesAreStableBackendOptions(): void
    {
        $this->assertSame(['person', 'organization'], ReviewerType::values());
    }

    public function testMapsToSchemaOrgTypes(): void
    {
        $this->assertSame('Person', ReviewerType::PERSON->schemaType());
        $this->assertSame('Organization', ReviewerType::ORGANIZATION->schemaType());
    }

    public function testUnknownOrEmptyValueFallsBackToPerson(): void
    {
        $this->assertSame(ReviewerType::ORGANIZATION, ReviewerType::fromStringOrFallback('organization'));
        $this->assertSame(ReviewerType::PERSON, ReviewerType::fromStringOrFallback('team'));
        $this->assertSame(ReviewerType::PERSON, ReviewerType::fromStringOrFallback(''));
    }
}
