<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\Tests\Reference;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Trassd\Contao\GroundingPages\Reference\SameAsPolicy;
use Trassd\Contao\GroundingPages\Reference\SameAsViolation;

class SameAsPolicyTest extends TestCase
{
    private const array OWN = ['example.com'];

    /**
     * @return iterable<string, array{string, SameAsViolation|null}>
     */
    public static function urlProvider(): iterable
    {
        yield 'wikidata' => ['https://www.wikidata.org/wiki/Q42944', null];
        yield 'linkedin' => ['https://www.linkedin.com/company/cern/', null];
        yield 'orcid' => ['https://orcid.org/0000-0002-1825-0097', null];
        yield 'gnd' => ['https://d-nb.info/gnd/2005698-4', null];
        yield 'research path is no search' => ['https://ror.org/research-group', null];
        yield 'umlaut path' => ['https://de.wikipedia.org/wiki/Müller', null];
        yield 'umlaut host' => ['https://bücher.de/x', null];
        yield 'whitespace' => ['https://a b', SameAsViolation::NotHttp];
        yield 'ftp' => ['ftp://example.org/file', SameAsViolation::NotHttp];
        yield 'relative' => ['/about/', SameAsViolation::NotHttp];
        yield 'empty' => ['', SameAsViolation::NotHttp];
        yield 'own domain' => ['https://example.com/about/', SameAsViolation::OwnDomain];
        yield 'own domain www' => ['https://www.example.com/about/', SameAsViolation::OwnDomain];
        yield 'own subdomain' => ['https://shop.example.com/', SameAsViolation::OwnDomain];
        yield 'own domain uppercase' => ['https://EXAMPLE.com/', SameAsViolation::OwnDomain];
        yield 'search path' => ['https://www.google.com/search?q=cern', SameAsViolation::SearchOrApi];
        yield 'search query s' => ['https://news.example.org/?s=cern', SameAsViolation::SearchOrApi];
        yield 'api path' => ['https://api.example.org/api/entity/1', SameAsViolation::SearchOrApi];
        yield 'mediawiki api' => ['https://de.wikipedia.org/w/api.php?action=query', SameAsViolation::SearchOrApi];
        yield 'special search' => ['https://de.wikipedia.org/wiki/Special:Search/CERN', SameAsViolation::SearchOrApi];
        yield 'doi' => ['https://doi.org/10.1000/182', SameAsViolation::DoiOrArxiv];
        yield 'dx doi' => ['https://dx.doi.org/10.1000/182', SameAsViolation::DoiOrArxiv];
        yield 'arxiv' => ['https://arxiv.org/abs/1234.5678', SameAsViolation::DoiOrArxiv];
        yield 'section anchor' => ['https://de.wikipedia.org/wiki/Genf#Wissenschaft', SameAsViolation::SectionAnchor];
    }

    #[DataProvider('urlProvider')]
    public function testCheck(string $url, SameAsViolation|null $expected): void
    {
        $this->assertSame($expected, (new SameAsPolicy())->check($url, self::OWN));
    }

    public function testOwnHostsAreNormalised(): void
    {
        $this->assertSame(SameAsViolation::OwnDomain, (new SameAsPolicy())->check('https://example.com/', ['WWW.Example.com']));
    }

    public function testViolationsAreKeyedByUrlAndSkipValidOnes(): void
    {
        $violations = (new SameAsPolicy())->violations(
            ['https://www.wikidata.org/wiki/Q42944', 'https://doi.org/10.1000/182'],
            self::OWN,
        );

        $this->assertSame(['https://doi.org/10.1000/182' => SameAsViolation::DoiOrArxiv], $violations);
    }

    public function testIsAbsoluteHttpUrl(): void
    {
        $this->assertTrue(SameAsPolicy::isAbsoluteHttpUrl('https://example.com/about/#organization'));
        $this->assertTrue(SameAsPolicy::isAbsoluteHttpUrl('https://de.wikipedia.org/wiki/Müller'));
        $this->assertTrue(SameAsPolicy::isAbsoluteHttpUrl('https://bücher.de/x'));
        $this->assertFalse(SameAsPolicy::isAbsoluteHttpUrl('https://a b'));
        $this->assertFalse(SameAsPolicy::isAbsoluteHttpUrl('#organization'));
        $this->assertFalse(SameAsPolicy::isAbsoluteHttpUrl('mailto:a@example.com'));
        $this->assertFalse(SameAsPolicy::isAbsoluteHttpUrl(''));
    }

    public function testHostOf(): void
    {
        $this->assertSame('example.com', SameAsPolicy::hostOf('https://WWW.Example.com/x'));
        $this->assertNull(SameAsPolicy::hostOf('/relative'));
    }

    public function testViolationTranslationKey(): void
    {
        $this->assertSame('tl_grounding_page.error.sameAs.ownDomain', SameAsViolation::OwnDomain->translationKey());
    }
}
