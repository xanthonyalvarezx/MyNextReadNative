<?php

namespace App\Http\Controllers;

use App\Services\GoogleBooksClient;
use App\Support\IsbnQuery;
use App\ViewModels\BookVolumeView;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function landing()
    {
        return view('landing');
    }

    public function library()
    {
        return view('library.library');
    }

    public function search(Request $request, GoogleBooksClient $books)
    {
        $query = $request->string('q')->trim()->value();
        $results = null;
        $error = null;

        if ($query !== '') {
            try {
                $apiQuery = IsbnQuery::toGoogleBooksQuery($query);
                $results = $books->searchVolumes(
                    $apiQuery,
                    maxResults: (int) $request->integer('per_page', 20),
                    startIndex: (int) $request->integer('start', 0),
                );
            } catch (\Throwable $e) {
                report($e);
                $error = __('Search failed. Check your API key and try again.');
            }
        }

        $volumes = [];
        $totalItems = null;

        if (is_array($results)) {
            $totalItems = (int) ($results['totalItems'] ?? 0);
            $volumes = BookVolumeView::collectFromApiItems($results['items'] ?? []);
        }

        return view('search', [
            'query' => $query,
            'error' => $error,
            'totalItems' => $totalItems,
            'volumes' => $volumes,
        ]);
    }
}
