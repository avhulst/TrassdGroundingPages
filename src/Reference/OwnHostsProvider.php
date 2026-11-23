<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\Reference;

use Doctrine\DBAL\Connection;

/*
 * Eigene Hosts beim Speichern: Startseiten-DNS plus Hosts der übergebenen URLs.
 * Bewusst weder final noch readonly: PHPUnit kann readonly Klassen nicht
 * zuverlässig doubeln, die Listener-Tests stubben ihn ohne Datenbank.
 */
class OwnHostsProvider
{
    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * @return list<string>
     */
    public function hosts(string ...$urls): array
    {
        $hosts = $this->connection->fetchFirstColumn("SELECT dns FROM tl_page WHERE type = 'root' AND dns != ''");

        foreach ($urls as $url) {
            $hosts[] = SameAsPolicy::hostOf($url);
        }

        return array_values(array_unique(array_filter(array_map(strval(...), $hosts))));
    }
}
