<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GoogleBooksSearchTest extends TestCase
{
    public function test_search_page_loads_without_query(): void
    {
        $response = $this->get(route('search'));

        $response->assertOk();
        $response->assertSee('Search books', false);
    }

    public function test_search_with_query_calls_google_books_and_renders_results(): void
    {
        config(['google_books.api_key' => 'test-api-key']);

        Http::fake([
            'https://www.googleapis.com/books/v1/volumes*' => Http::response([
                'kind' => 'books#volumes',
                'totalItems' => 1,
                'items' => [
                    [
                        'id' => 'test-volume-id',
                        'volumeInfo' => [
                            'title' => 'The Test Book',
                            'authors' => ['Jane Writer'],
                            'publishedDate' => '2021',
                            'imageLinks' => [
                                'thumbnail' => 'https://books.google.com/thumb.jpg',
                            ],
                            'previewLink' => 'https://books.google.com/preview',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->get(route('search', ['q' => 'test query']));

        $response->assertOk();
        $response->assertSee('The Test Book', false);
        $response->assertSee('Jane Writer', false);
        $response->assertSee('About 1 results', false);

        Http::assertSent(function ($request) {
            return str_starts_with($request->url(), 'https://www.googleapis.com/books/v1/volumes')
                && $request['q'] === 'test query'
                && $request['key'] === 'test-api-key'
                && (int) $request['maxResults'] === 20;
        });
    }

    public function test_search_with_isbn_sends_isbn_field_to_google_books(): void
    {
        config(['google_books.api_key' => 'test-api-key']);

        Http::fake([
            'https://www.googleapis.com/books/v1/volumes*' => Http::response([
                'kind' => 'books#volumes',
                'totalItems' => 1,
                'items' => [
                    [
                        'id' => 'isbn-vol',
                        'volumeInfo' => [
                            'title' => 'ISBN Match',
                            'authors' => ['Author'],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->get(route('search', ['q' => '978-0-7432-7356-5']));

        $response->assertOk();
        $response->assertSee('ISBN Match', false);

        Http::assertSent(function ($request) {
            return $request['q'] === 'isbn:9780743273565';
        });
    }
}
