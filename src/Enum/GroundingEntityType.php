<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\Enum;

enum GroundingEntityType: string
{
    case ORGANIZATION = 'organization';
    case PERSON = 'person';
    case GROUP_OR_ROLE = 'group-or-role';
    case PRODUCT = 'product';
    case SERVICE = 'service';
    case TOOL_OR_PLATFORM = 'tool-or-platform';
    case FEATURE = 'feature';
    case SEGMENT = 'segment';
    case FIELD_OF_KNOWLEDGE = 'field-of-knowledge';
    case CONCEPT = 'concept';
    case PUBLICATION = 'publication';
    case DATASET = 'dataset';
    case STANDARD = 'standard';
    case METHOD = 'method';
    case PLACE = 'place';
    case EVENT = 'event';
    case METRIC = 'metric';
    case PROJECT = 'project';

    public function schemaType(): string
    {
        return match ($this) {
            self::ORGANIZATION, self::GROUP_OR_ROLE => 'Organization',
            self::PERSON => 'Person',
            self::PRODUCT => 'Product',
            self::SERVICE => 'Service',
            self::TOOL_OR_PLATFORM => 'SoftwareApplication',
            self::FIELD_OF_KNOWLEDGE, self::CONCEPT => 'DefinedTerm',
            self::PUBLICATION, self::STANDARD => 'CreativeWork',
            self::DATASET => 'Dataset',
            self::METHOD => 'HowTo',
            self::PLACE => 'Place',
            self::EVENT => 'Event',
            self::PROJECT => 'Project',
            self::FEATURE, self::SEGMENT, self::METRIC => 'Thing',
        };
    }

    public static function fromStringOrFallback(string $value): self
    {
        return self::tryFrom($value) ?? self::SEGMENT;
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $out = [];

        foreach (self::cases() as $case) {
            $out[$case->value] = $case->value;
        }

        return $out;
    }
}
