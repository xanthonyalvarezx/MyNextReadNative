<x-main>
    @php
        $shelfOptions = [
            'read' => 'Read',
            'want-to-read' => 'Want to Read',
            'reading' => 'Currently Reading',
            'owned' => 'Owned',
        ];
    @endphp

    <section class="app-library">
        @if (session('success'))
            <p class="app-library__flash app-library__flash--success" role="status">{{ session('success') }}</p>
        @endif
        @if (session('error'))
            <p class="app-library__flash app-library__flash--error" role="alert">{{ session('error') }}</p>
        @endif
        @if (session('warning'))
            <p class="app-library__flash app-library__flash--warning" role="status">{{ session('warning') }}</p>
        @endif

        <header class="app-library__header">
            <h1 class="app-library__title">My Library</h1>
            <p class="app-library__subtitle">{{ ($books ?? collect())->count() }} books saved</p>
        </header>

        @if (($books ?? collect())->isEmpty())
            <div class="app-library__empty">
                <p>Your library is empty. Search for a book and add it to a shelf.</p>
            </div>
        @else
            <div class="app-library__grid">
                @foreach ($books as $book)
                    <article class="app-library__book-card">
                        <img src="{{ $book->cover_image }}" alt="{{ $book->title }}"
                            class="app-library__book-card-image">
                        <h2 class="app-library__book-card-title">{{ $book->title }}</h2>
                        <p class="app-library__book-card-author">{{ $book->author ?: 'Unknown author' }}</p>
                        @php
                            $bookGenres = filled($book->genre)
                                ? array_values(
                                    array_filter(
                                        array_map('trim', explode(',', $book->genre)),
                                        fn ($g) => $g !== '',
                                    ),
                                )
                                : [];
                        @endphp
                        @if ($bookGenres !== [])
                            <ul class="app-library__book-card-genres" aria-label="Genres">
                                @foreach ($bookGenres as $genreLabel)
                                    <li class="app-library__book-card-genre-tag">{{ $genreLabel }}</li>
                                @endforeach
                            </ul>
                        @endif
                        <p class="app-library__book-card-pages">{{ $book->pages }} pages</p>
                        <div class="app-library__book-card-shelf">
                            <span class="app-library__shelf-label">Shelf</span>
                            <span
                                class="app-library__shelf-value">{{ $book->shelf ? $shelfOptions[$book->shelf] ?? $book->shelf : 'Unsorted' }}</span>
                        </div>
                        <form class="app-library__shelf-form" action="{{ route('library.updateShelf', $book) }}"
                            method="post">
                            @csrf
                            <label class="app-library__shelf-field-label" for="shelf-{{ $book->id }}">Change
                                shelf</label>
                            <select class="app-library__shelf-select" name="shelf" id="shelf-{{ $book->id }}"
                                onchange="this.form.submit()">
                                @if (blank($book->shelf))
                                    <option value="" disabled selected>Select shelf</option>
                                @endif
                                @foreach ($shelfOptions as $value => $label)
                                    <option value="{{ $value }}" @selected($book->shelf === $value)>
                                        {{ $label }}</option>
                                @endforeach
                            </select>
                        </form>
                        <form action="{{ route('library.destroy', $book) }}" method="post"
                            data-library-delete-form="{{ $book->id }}">
                            @csrf
                            @method('delete')
                            <button type="button" class="app-library__delete-btn"
                                data-library-delete-open="{{ $book->id }}">Delete</button>
                        </form>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    @if (!($books ?? collect())->isEmpty())
        <dialog class="app-library__delete-dialog" id="library-delete-dialog" aria-labelledby="library-delete-title">
            <div class="app-library__delete-dialog-inner">
                <h2 id="library-delete-title" class="app-library__delete-dialog-title">Delete Book</h2>
                <p class="app-library__delete-dialog-text">Remove this book from your library?</p>
                <div class="app-library__delete-dialog-actions">
                    <button type="button" class="app-library__delete-dialog-cancel"
                        id="library-delete-cancel">Cancel</button>
                    <button type="button" class="app-library__delete-dialog-confirm"
                        id="library-delete-confirm">Delete</button>
                </div>
            </div>
        </dialog>
        <script>
            (function() {
                const deleteDialog = document.getElementById('library-delete-dialog');
                const deleteCancel = document.getElementById('library-delete-cancel');
                const deleteConfirm = document.getElementById('library-delete-confirm');
                let pendingDeleteForm = null;
                document.querySelectorAll('[data-library-delete-open]').forEach(function(btn) {
                    btn.addEventListener('click', function() {
                        const id = btn.getAttribute('data-library-delete-open');
                        pendingDeleteForm = document.querySelector('[data-library-delete-form="' + id +
                            '"]');
                        if (deleteDialog) {
                            deleteDialog.showModal();
                        }
                    });
                });
                deleteCancel?.addEventListener('click', function() {
                    deleteDialog?.close();
                    pendingDeleteForm = null;
                });
                deleteConfirm?.addEventListener('click', function() {
                    if (pendingDeleteForm) {
                        pendingDeleteForm.submit();
                    }
                });
            })();
        </script>
    @endif
</x-main>
