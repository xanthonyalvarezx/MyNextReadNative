<?php

namespace Tests\Feature;

use App\Services\GoogleBooksClient;
use Illuminate\Http\Client\RequestException;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

class GoogleBooksIntegrationTest extends TestCase
{
    /**
     * Hits the real Google Books API. Requires GOOGLE_BOOKS_API_KEY in .env.
     *
     * Run explicitly: php artisan test --group=google-books-live
     *
     * If you see 403: enable "Books API" for the key's Google Cloud project,
     * and relax API key restrictions (e.g. "None" for local dev, or allow your IP).
     */
    #[Group('google-books-live')]
    public function test_live_api_search_returns_volumes(): void
    {
        if (config('google_books.api_key') === null || config('google_books.api_key') === '') {
            $this->markTestSkipped('Set GOOGLE_BOOKS_API_KEY in .env to run this integration test.');
        }

        $client = app(GoogleBooksClient::class);

        try {
            $result = $client->searchVolumes('dune frank herbert', maxResults: 5);
        } catch (RequestException $e) {
            $status = $e->response?->status();
            if (in_array($status, [401, 403], true)) {
                $this->fail(
                    "Google Books API returned HTTP {$status}. In Google Cloud: enable the "
                    .'"Books API" for the same project as this API key (API_KEY_SERVICE_BLOCKED usually means the API is off). '
                    .'Then check key restrictions if needed. Raw response: '.($e->response?->body() ?? $e->getMessage())
                );
            }

            throw $e;
        }

        $this->assertIsArray($result);
        $this->assertArrayHasKey('totalItems', $result);
        $this->assertGreaterThan(0, (int) ($result['totalItems'] ?? 0));
        $this->assertNotEmpty($result['items'] ?? []);
        $this->assertArrayHasKey('volumeInfo', $result['items'][0]);
        $this->assertArrayHasKey('title', $result['items'][0]['volumeInfo']);
    }
}
