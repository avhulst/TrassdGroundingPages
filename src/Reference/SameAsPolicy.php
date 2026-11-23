<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\Reference;

use Contao\Idna;

/*
 * RI-3 (Standard 2.0): sameAs nur für offizielle Profile, Registereinträge und
 * Nachschlagewerk-Einträge zur Entität. Geprüft werden nur maschinell erkennbare
 * Ausschlüsse; ob ein Profil offiziell ist, beurteilt das menschliche Review.
 */
final class SameAsPolicy
{
    private const array DOI_ARXIV_HOSTS = ['doi.org', 'dx.doi.org', 'arxiv.org'];

    private const array SEARCH_QUERY_KEYS = ['q', 's', 'query', 'search'];

    /**
     * @param list<string> $ownHosts
     */
    public function check(string $url, array $ownHosts): SameAsViolation|null
    {
        if (!self::isAbsoluteHttpUrl($url)) {
            return SameAsViolation::NotHttp;
        }

        $host = (string) self::hostOf($url);

        foreach ($ownHosts as $ownHost) {
            $own = self::normaliseHost($ownHost);

            if ('' !== $own && ($host === $own || str_ends_with($host, '.'.$own))) {
                return SameAsViolation::OwnDomain;
            }
        }

        if (\in_array($host, self::DOI_ARXIV_HOSTS, true)) {
            return SameAsViolation::DoiOrArxiv;
        }

        if ($this->isSearchOrApi($url)) {
            return SameAsViolation::SearchOrApi;
        }

        if ('' !== (string) parse_url($url, PHP_URL_FRAGMENT)) {
            return SameAsViolation::SectionAnchor;
        }

        return null;
    }

    /**
     * @param list<string> $urls
     * @param list<string> $ownHosts
     *
     * @return array<string, SameAsViolation>
     */
    public function violations(array $urls, array $ownHosts): array
    {
        $out = [];

        foreach ($urls as $url) {
            $violation = $this->check($url, $ownHosts);

            if (null !== $violation) {
                $out[$url] = $violation;
            }
        }

        return $out;
    }

    public static function isAbsoluteHttpUrl(string $url): bool
    {
        $ascii = self::toAscii($url);

        if (null === $ascii || false === filter_var($ascii, FILTER_VALIDATE_URL)) {
            return false;
        }

        return \in_array(strtolower((string) parse_url($ascii, PHP_URL_SCHEME)), ['http', 'https'], true);
    }

    public static function hostOf(string $url): string|null
    {
        $host = parse_url($url, PHP_URL_HOST);

        return \is_string($host) && '' !== $host ? self::normaliseHost($host) : null;
    }

    /**
     * FILTER_VALIDATE_URL kennt nur ASCII: Host per IDNA in Punycode wandeln, übrige
     * Nicht-ASCII-Bytes in Pfad, Query und Fragment prozentcodieren.
     */
    private static function toAscii(string $url): string|null
    {
        $host = parse_url($url, PHP_URL_HOST);

        if (\is_string($host) && 1 === preg_match('/[^\x00-\x7F]/', $host)) {
            $encoded = Idna::encode($host);

            if ('' === $encoded) {
                return null;
            }

            $url = preg_replace('/'.preg_quote($host, '/').'/', $encoded, $url, 1) ?? $url;
        }

        return preg_replace_callback('/[^\x00-\x7F]+/', static fn (array $m): string => rawurlencode($m[0]), $url);
    }

    private static function normaliseHost(string $host): string
    {
        $host = strtolower(trim($host));

        return str_starts_with($host, 'www.') ? substr($host, 4) : $host;
    }

    private function isSearchOrApi(string $url): bool
    {
        $path = strtolower((string) parse_url($url, PHP_URL_PATH));

        if (1 === preg_match('#/(search|api)(/|$)#', $path) || str_contains($path, 'api.php') || str_contains($path, 'special:search')) {
            return true;
        }

        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

        return [] !== array_intersect(array_map(strval(...), array_keys($query)), self::SEARCH_QUERY_KEYS);
    }
}
