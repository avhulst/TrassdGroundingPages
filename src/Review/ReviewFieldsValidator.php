<?php

declare(strict_types=1);

namespace Trassd\Contao\GroundingPages\Review;

/*
 * Prüfregeln für das menschliche Fakten-Review (Standard v1.7), ohne Framework-
 * Abhängigkeit: Name und Datum gibt es nur zusammen, und das Datum liegt nicht
 * in der Zukunft.
 */
final class ReviewFieldsValidator
{
    public const string ERROR_INCOMPLETE = 'reviewIncomplete';

    public const string ERROR_FUTURE = 'reviewInFuture';

    /**
     * @param string $date Timestamp, formatierter POST-Wert oder ''. Die
     *                     Zukunftsprüfung greift nur bei Timestamps.
     */
    public function validate(string $name, string $date, int $now): string|null
    {
        $date = trim($date);
        $hasName = '' !== trim($name);
        $hasDate = '' !== $date && '0' !== $date;

        if ($hasName !== $hasDate) {
            return self::ERROR_INCOMPLETE;
        }

        // Tagesvergleich: Ein Review von heute ist gültig, egal zu welcher Uhrzeit.
        if ($hasDate && ctype_digit($date) && date('Y-m-d', (int) $date) > date('Y-m-d', $now)) {
            return self::ERROR_FUTURE;
        }

        return null;
    }
}
