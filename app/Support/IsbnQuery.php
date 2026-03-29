<?php

namespace App\Support;

/**
 * Maps user input to a Google Books "q" value. Uses the isbn: field when input looks like ISBN-10/13.
 *
 * @see https://developers.google.com/books/docs/v1/using#PerformingSearch
 */
final class IsbnQuery
{
    public static function toGoogleBooksQuery(string $trimmedUserInput): string
    {
        if ($trimmedUserInput === '') {
            return '';
        }

        if (preg_match('/^isbn:\s*/i', $trimmedUserInput, $m)) {
            $rest = trim(substr($trimmedUserInput, strlen($m[0])));
            $canonical = self::canonicalIsbn($rest);

            return $canonical !== null ? 'isbn:'.$canonical : 'isbn:'.$rest;
        }

        $canonical = self::canonicalIsbn($trimmedUserInput);

        return $canonical !== null ? 'isbn:'.$canonical : $trimmedUserInput;
    }

    /**
     * Returns normalized ISBN (10 or 13 chars) or null if not a plain ISBN-like string.
     */
    public static function canonicalIsbn(string $value): ?string
    {
        $clean = strtoupper(str_replace([' ', '-'], '', $value));

        if (preg_match('/^(97[89]\d{10})$/', $clean, $m)) {
            return $m[1];
        }

        if (preg_match('/^(\d{9}[\dX])$/', $clean, $m)) {
            return $m[1];
        }

        return null;
    }
}
