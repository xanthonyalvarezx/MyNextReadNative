<?php

namespace App\Http\Controllers;

use App\Models\library as LibraryModel;
use App\Services\GoogleBooksClient;
use App\Support\IsbnQuery;
use App\Support\LibraryGenrePie;
use App\Support\LibraryReadShelfSizePie;
use App\ViewModels\BookVolumeView;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class PageController extends Controller
{
    public function landing()
    {
        $yearStart = Carbon::now()->startOfYear();
        $yearEnd = Carbon::now()->endOfYear();

        // No dedicated "finished at" column — use last update in this calendar year on read shelf.
        $booksReadThisYear = LibraryModel::query()
            ->where('shelf', 'read')
            ->whereBetween('updated_at', [$yearStart, $yearEnd])
            ->count();

        $currentlyReading = LibraryModel::query()
            ->where('shelf', 'reading')
            ->orderBy('title')
            ->first();

        return view('landing', [
            'genrePie' => LibraryGenrePie::fromDatabase(),
            'readShelfSizePie' => LibraryReadShelfSizePie::fromDatabase(),
            'booksReadThisYear' => $booksReadThisYear,
            'currentlyReading' => $currentlyReading,
        ]);
    }

    public function library()
    {
        $libraries = LibraryModel::query()
            ->orderByRaw('LOWER(title)')
            ->get();
        // $userId = Auth::id();

        // $libraries = LibraryModel::query()
        //     ->when($userId, fn($query, $id) => $query->where('user_id', $id))
        //     ->orderByRaw('LOWER(title)')
        //     ->get();

        return view('library.library', [
            'books' => $libraries,
        ]);
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
            'searchQuery' => $query,
            'error' => $error,
            'totalItems' => $totalItems,
            'volumes' => $volumes,
        ]);
    }

    public function nextread(Request $request)
    {
        $book = null;
        if ($request->filled('book')) {
            $book = LibraryModel::find($request->integer('book'));
        }
        if ($book === null) {
            $book = LibraryModel::query()
                ->where(function ($q) {
                    $q->whereNull('shelf')->orWhereNot('shelf', 'read');
                })
                ->inRandomOrder()
                ->first();
        }

        return view('nextread', [
            'book' => $book,
        ]);
    }
}
