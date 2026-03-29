<x-main>
    <div class="search-page" data-book-scan data-search-url="{{ route('search') }}">
        <div class="search-page__inner">
            <h1 class="search-page__title">Search books</h1>

            <form class="search-page__form" action="{{ route('search') }}" method="get">
                <input class="search-page__query" type="search" name="q" value="{{ $query }}"
                    placeholder="Title, author, ISBN, or scan barcode…" autocomplete="off" autofocus>
                <button class="search-page__submit" type="submit">Search</button>
                <button class="search-page__scan-open" type="button" data-book-scan-open>
                    Scan with camera
                </button>
            </form>
            <br>

            <p class="search-page__scanner-hint">
                <strong>Camera:</strong> use <em>Scan with camera</em>.
                <strong>Hand scanner:</strong> click in the search box and scan (most send <kbd>Enter</kbd>)
            </p>

            <dialog class="search-page__scan-dialog" data-book-scan-dialog aria-labelledby="book-scan-title">
                <div class="search-page__scan-panel">
                    <h2 class="search-page__scan-title" id="book-scan-title">Scan ISBN barcode</h2>
                    <p class="search-page__scan-status" data-book-scan-status aria-live="polite"></p>
                    <video class="search-page__scan-video" data-book-scan-video playsinline muted></video>
                    <div class="search-page__scan-actions">
                        <button class="search-page__scan-cancel" type="button" data-book-scan-cancel>Cancel</button>
                    </div>
                </div>
            </dialog>

            @if ($error)
                <p class="search-page__error" role="alert">{{ $error }}</p>
            @endif

            @if ($query !== '' && !$error && $totalItems !== null)
                <p class="search-page__count">
                    About {{ number_format($totalItems) }} results
                </p>

                @if ($volumes === [])
                    <p class="search-page__empty">No books matched that search.</p>
                @else
                    <ul class="search-page__list">
                        @foreach ($volumes as $book)
                            <li class="search-page__card">
                                @if ($book->thumbnailUrl)
                                    <img class="search-page__cover" src="{{ $book->thumbnailUrl }}" alt="">
                                @else
                                    <div class="search-page__cover search-page__cover--placeholder" aria-hidden="true">
                                        No cover
                                    </div>
                                @endif
                                <div class="search-page__body">
                                    <h2 class="search-page__book-title">{{ $book->title }}</h2>
                                    @if ($book->authorsLine)
                                        <p class="search-page__authors">{{ $book->authorsLine }}</p>
                                    @endif
                                    @if ($book->published)
                                        <p class="search-page__published">{{ $book->published }}</p>
                                    @endif
                                    @if ($book->previewUrl)
                                        <a class="search-page__preview" href="{{ $book->previewUrl }}" target="_blank"
                                            rel="noopener noreferrer">
                                            Preview on Google Books
                                        </a>
                                        <ul class="search-page__card-buttons" aria-label="Shelf actions">
                                            <li><button type="button" class="search-page__card-btn">Read</button></li>
                                            <li><button type="button" class="search-page__card-btn">Want to
                                                    Read</button>
                                            </li>
                                            <li><button type="button" class="search-page__card-btn">Currently
                                                    Reading</button>
                                            </li>
                                            <li>
                                                <button type="button" class="search-page__card-btn">Owned</button>
                                            </li>

                            </li>
                    </ul>
                @endif
        </div>
        </li>
        @endforeach
        </ul>
        @endif
        @endif
    </div>
    </div>
</x-main>
