<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GoogleBooksClient
{
    public function searchVolumes(string $query, int $maxResults = 20, int $startIndex = 0): array
    {
        $key = config('google_books.api_key');

        if ($key === null || $key === '') {
            throw new \InvalidArgumentException(
                'GOOGLE_BOOKS_API_KEY is not set. Add it to your .env file.'
            );
        }

        $maxResults = max(1, min($maxResults, 40));

        $response = Http::baseUrl(config('google_books.base_url'))
            ->timeout(15)
            ->acceptJson()
            ->get('/volumes', [
                'q' => $query,
                'maxResults' => $maxResults,
                'startIndex' => max(0, $startIndex),
                'key' => $key,
            ]);

        $response->throw();

        return $response->json();
    }

    public function volume(string $volumeId): array
    {
        $key = config('google_books.api_key');

        if ($key === null || $key === '') {
            throw new \InvalidArgumentException(
                'GOOGLE_BOOKS_API_KEY is not set. Add it to your .env file.'
            );
        }

        $response = Http::baseUrl(config('google_books.base_url'))
            ->timeout(15)
            ->acceptJson()
            ->get("/volumes/{$volumeId}", [
                'key' => $key,
            ]);

        $response->throw();

        return $response->json();
    }
}
