<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\Enum;

/*
 * Wer das Fakten-Review verantwortet (Standard v1.7): eine Person oder eine
 * Organisation, beide gleichwertig konform.
 */
enum ReviewerType: string
{
    case PERSON = 'person';
    case ORGANIZATION = 'organization';

    public function schemaType(): string
    {
        return match ($this) {
            self::PERSON => 'Person',
            self::ORGANIZATION => 'Organization',
        };
    }

    public static function fromStringOrFallback(string $value): self
    {
        return self::tryFrom($value) ?? self::PERSON;
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
