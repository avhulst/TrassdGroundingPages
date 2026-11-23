<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\JsonLd;

use Spatie\SchemaOrg\Type;

/**
 * Transportiert eine Liste von PropertyValue-Arrays als Wert von `identifier`.
 *
 * spatie/schema-org deutet `identifier` in toArray() als `@id` um, sofern der Wert kein
 * Type ist. Dieser Wrapper ist ein Type und serialisiert sich als reine Liste.
 */
final readonly class PropertyValueList implements Type
{
    /**
     * @param list<array<string, string>> $values
     */
    public function __construct(private array $values)
    {
    }

    public function __toString(): string
    {
        return $this->toScript();
    }

    /**
     * @return list<array<string, string>>
     */
    public function toArray(): array
    {
        return $this->values;
    }

    public function toScript(): string
    {
        return '<script type="application/ld+json">'.json_encode($this->values, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES).'</script>';
    }
}
