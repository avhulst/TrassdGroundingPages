<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\Tests\Reference;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Trassd\Contao\GroundingPages\Reference\OwnHostsProvider;

class OwnHostsProviderTest extends TestCase
{
    public function testMergesRootDnsWithUrlHostsDedupedWithoutEmpties(): void
    {
        $connection = $this->createStub(Connection::class);
        $connection
            ->method('fetchFirstColumn')
            ->willReturn(['example.net', '', 'example.com'])
        ;

        $hosts = (new OwnHostsProvider($connection))->hosts('https://example.com/about/', '', 'https://www.example.org/x', '/relativ');

        $this->assertSame(['example.net', 'example.com', 'example.org'], $hosts);
    }
}
