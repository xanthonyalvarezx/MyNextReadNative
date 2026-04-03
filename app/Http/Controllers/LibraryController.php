<?php

namespace App\Http\Controllers;

use App\Models\library;
use Illuminate\Http\Request;

class LibraryController extends Controller
{
    public function addToLibrary(Request $request)
    {

        $library = new library;
        $library->title = $request->input('title');
        $library->subtitle = $request->input('subtitle');
        $library->author = $request->input('author');
        $library->language = $request->input('language');
        $library->genre = $request->input('genre');
        $library->isbn = $request->input('isbn');
        $library->publisher = $request->input('publisher');
        $library->publication_date = $request->input('publication_date');
        $library->pages = $request->filled('pages') ? (int) $request->input('pages') : null;
        $library->cover_image = $request->input('cover_image');
        $library->description = $request->input('description');
        $library->shelf = $request->input('shelf');
        $library->save();

        if ($request->string('return_to')->value() === 'search') {
            $q = $request->string('search_q')->trim()->value();

            return $q !== ''
                ? redirect()->route('search', ['q' => $q])->with('success', 'Book added to library')
                : redirect()->route('search')->with('success', 'Book added to library');
        }

        return redirect()->route('library')->with('success', 'Book added to library');
    }

    public function updateShelf(Request $request, library $book)
    {
        $validated = $request->validate([
            'shelf' => ['required', 'in:read,want-to-read,reading,owned'],
            'return_to' => ['nullable', 'string', 'in:nextread,landing'],
        ]);

        $book->update([
            'shelf' => $validated['shelf'],
        ]);
        $book->refresh();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Shelf updated',
                'shelf' => $book->shelf,
            ]);
        }

        if (($validated['return_to'] ?? null) === 'nextread') {
            return redirect()->route('nextread', ['book' => $book->id])->with('success', 'Shelf updated');
        }

        if (($validated['return_to'] ?? null) === 'landing') {
            return redirect()->route('landing')->with('success', 'Shelf updated');
        }

        return redirect()->route('library')->with('success', 'Shelf updated');
    }

    public function updateProgress(Request $request, library $book)
    {
        $rules = [
            'pages_read' => ['required', 'integer', 'min:0'],
            'return_to' => ['nullable', 'string', 'in:landing'],
        ];
        if ($book->pages !== null && (int) $book->pages > 0) {
            $rules['pages_read'][] = 'max:'.(int) $book->pages;
        }

        $validated = $request->validate($rules);

        $book->pages_read = (int) $validated['pages_read'];
        $book->save();
        $book->refresh();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Progress updated',
                'pages_read' => $book->pages_read,
                'percent_read' => $book->percentRead(),
            ]);
        }

        if (($validated['return_to'] ?? null) === 'landing') {
            return redirect()->route('landing')->with('success', 'Progress updated');
        }

        return redirect()->route('library')->with('success', 'Progress updated');
    }

    public function destroy(library $book)
    {
        $book->delete();

        return redirect()->route('library')->with('success', 'Book deleted');
    }
}
