<x-main>
    @php
        $shelfOptions = [
            'read' => 'Read',
            'want-to-read' => 'Want to Read',
            'reading' => 'Currently Reading',
            'owned' => 'Owned',
        ];
    @endphp

    <section class="app-nextread">
        @if (session('success'))
            <p class="app-nextread__flash app-nextread__flash--success" role="status">{{ session('success') }}</p>
        @endif

        <h1 class="app-nextread__title">Your Next Read</h1>
        @if ($book !== null)
            <div class="app-nextread_book-card">
                <div class="app-nextread_book-card-title">
                    <h2>{{ $book->title }}</h2>
                </div>
                <div class="app-nextread_book-card-image">
                    <img src="{{ $book->cover_image }}" alt="{{ $book->title }}">
                </div>
                <div class="app-nextread_book-card-author">
                    <p>{{ $book->author }}</p>
                </div>
                <div class="app-nextread_book-card-published">
                    <p>{{ $book->publication_date }}</p>
                </div>
                <div class="app-nextread_book-card-shelf app-nextread_book-card-shelf--display">
                    <span class="app-library__shelf-label">Shelf</span>
                    <span
                        class="app-library__shelf-value">{{ $book->shelf ? ($shelfOptions[$book->shelf] ?? $book->shelf) : 'Unsorted' }}</span>
                </div>
                <form class="app-library__shelf-form app-nextread__shelf-form" action="{{ route('library.updateShelf', $book) }}"
                    method="post">
                    @csrf
                    <input type="hidden" name="return_to" value="nextread">
                    <label class="app-library__shelf-field-label" for="nextread-shelf-{{ $book->id }}">Change shelf</label>
                    <select class="app-library__shelf-select" name="shelf" id="nextread-shelf-{{ $book->id }}"
                        onchange="this.form.submit()">
                        @if (blank($book->shelf))
                            <option value="" disabled selected>Select shelf</option>
                        @endif
                        @foreach ($shelfOptions as $value => $label)
                            <option value="{{ $value }}" @selected($book->shelf === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </form>
                <div class="app-nextread_book-card-description">
                    <p>{{ $book->description }}</p>
                </div>
            </div>
        @else
            <p class="app-nextread__empty">No books found</p>
        @endif
    </section>
</x-main>
