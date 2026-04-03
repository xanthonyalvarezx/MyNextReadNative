<x-main>
    <div class="app-landing">
        <div class="app-landing-headlines">
            <div class="app-landing-text">
                <h1>Find Your Next Read</h1>
            </div>
            <div class="app-landing-text">
                <h1>Track Your Journey</h1>
            </div>
            <div class="app-landing-text">
                <h1>Build Your Library</h1>
            </div>
        </div>

        @if (session('success'))
            <p class="app-landing__flash app-landing__flash--success" role="status">{{ session('success') }}</p>
        @endif

        <div class="app-landing-chart-blocks">
            <div class="app-landing-charts" aria-label="Reading by genre">
                <p class="app-landing-charts__title">Reading by genre</p>
                @if ($genrePie !== null)
                    <div class="app-landing-pie-layout">
                        <div class="app-landing-pie-ring" role="img" aria-label="{{ $genrePie['aria_label'] }}">
                            <div class="app-landing-pie" style="background: {{ $genrePie['gradient'] }}"
                                aria-hidden="true"></div>
                        </div>
                        <ul class="app-landing-pie-legend">
                            @foreach ($genrePie['slices'] as $slice)
                                <li>
                                    <span class="app-landing-pie-legend__swatch"
                                        style="background: {{ $slice['color'] }}"></span>
                                    <span class="app-landing-pie-legend__label">{{ $slice['label'] }}</span>
                                    <span class="app-landing-pie-legend__pct">{{ $slice['pct_display'] }}%</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @else
                    <p class="app-landing-charts__empty">
                        No genre data yet. Add books from search — categories are saved as genres — to see your
                        library mix here.
                    </p>
                @endif
            </div>

            <div class="app-landing-charts" aria-label="Read shelf by book length">
                <p class="app-landing-charts__title">Read shelf: book length</p>
                @if ($readShelfSizePie !== null)
                    <div class="app-landing-pie-layout">
                        <div class="app-landing-pie-ring" role="img"
                            aria-label="{{ $readShelfSizePie['aria_label'] }}">
                            <div class="app-landing-pie" style="background: {{ $readShelfSizePie['gradient'] }}"
                                aria-hidden="true"></div>
                        </div>
                        <ul class="app-landing-pie-legend">
                            @foreach ($readShelfSizePie['slices'] as $slice)
                                <li>
                                    <span class="app-landing-pie-legend__swatch"
                                        style="background: {{ $slice['color'] }}"></span>
                                    <span class="app-landing-pie-legend__label">{{ $slice['label'] }}</span>
                                    <span class="app-landing-pie-legend__pct">{{ $slice['pct_display'] }}%</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @else
                    <p class="app-landing-charts__empty">
                        No finished books with page counts yet. Mark books as read and ensure page count is set to
                        see this split.
                    </p>
                @endif
            </div>

            <div class="app-landing-charts" aria-label="Books read this year">
                <p class="app-landing-charts__title">Books read · {{ now()->year }}</p>
                <div class="app-landing-stat">
                    <p class="app-landing-stat__value" aria-live="polite">{{ $booksReadThisYear }}</p>
                    <p class="app-landing-stat__sub">finished on your read shelf</p>
                </div>
            </div>

            <div class="app-landing-charts" aria-label="Currently reading">
                <p class="app-landing-charts__title">Currently reading</p>
                <div id="landing-currently-reading-root">
                    @if ($currentlyReading !== null)
                        <div id="landing-currently-reading-active" class="app-landing-spotlight">
                            @if (filled($currentlyReading->cover_image))
                                <div class="app-landing-spotlight__cover">
                                    <img src="{{ $currentlyReading->cover_image }}"
                                        alt="Cover: {{ $currentlyReading->title }}" class="app-landing-spotlight__img"
                                        width="160" height="240" loading="lazy" decoding="async">
                                </div>
                            @endif
                            <h2 class="app-landing-spotlight__title">{{ $currentlyReading->title }}</h2>
                            @if (filled($currentlyReading->author))
                                <p class="app-landing-spotlight__meta">{{ $currentlyReading->author }}</p>
                            @endif
                            @php
                                $pct = $currentlyReading->percentRead();
                            @endphp
                            <p class="app-landing-spotlight__progress" id="landing-reading-percent" aria-live="polite"
                                @if ($pct === null) hidden aria-hidden="true" @endif>
                                @if ($pct !== null)
                                    {{ $pct }}% read
                                @endif
                            </p>

                            <form id="landing-progress-form" class="app-landing-spotlight__progress-form"
                                action="{{ route('library.updateProgress', $currentlyReading) }}" method="post">
                                @csrf
                                <input type="hidden" name="return_to" value="landing">
                                <label class="app-landing-spotlight__progress-label"
                                    for="landing-pages-read-{{ $currentlyReading->id }}">Pages read</label>
                                <div class="app-landing-spotlight__progress-row">
                                    <input class="app-landing-spotlight__progress-input" type="number"
                                        name="pages_read" id="landing-pages-read-{{ $currentlyReading->id }}"
                                        value="{{ old('pages_read', (int) ($currentlyReading->pages_read ?? 0)) }}"
                                        min="0"
                                        @if (filled($currentlyReading->pages) && (int) $currentlyReading->pages > 0) max="{{ (int) $currentlyReading->pages }}" @endif
                                        required inputmode="numeric">
                                    <button type="submit" class="app-landing-spotlight__progress-btn"
                                        id="landing-progress-submit">Update</button>
                                    <button type="button" class="app-landing-spotlight__finished-btn"
                                        id="landing-finished-btn"
                                        data-shelf-url="{{ route('library.updateShelf', $currentlyReading) }}">Finished</button>
                                </div>
                                <p class="app-landing-spotlight__error" id="landing-pages-read-ajax-error"
                                    role="alert" hidden></p>
                                @error('pages_read')
                                    <p class="app-landing-spotlight__error" role="alert">{{ $message }}</p>
                                @enderror
                            </form>
                        </div>
                        <p id="landing-currently-reading-empty" class="app-landing-charts__empty" hidden>Nothing on your
                            reading shelf yet.</p>

                        <script>
                            (function() {
                                const form = document.getElementById('landing-progress-form');
                                const percentEl = document.getElementById('landing-reading-percent');
                                const ajaxError = document.getElementById('landing-pages-read-ajax-error');
                                const submitBtn = document.getElementById('landing-progress-submit');
                                const finishedBtn = document.getElementById('landing-finished-btn');
                                const activePanel = document.getElementById('landing-currently-reading-active');
                                const emptyPanel = document.getElementById('landing-currently-reading-empty');
                                if (!form || !percentEl) {
                                    return;
                                }

                                function clearAjaxError() {
                                    if (ajaxError) {
                                        ajaxError.textContent = '';
                                        ajaxError.hidden = true;
                                    }
                                }

                                function setAjaxError(msg) {
                                    if (ajaxError) {
                                        ajaxError.textContent = msg;
                                        ajaxError.hidden = false;
                                    }
                                }

                                function setReadingActionsDisabled(disabled) {
                                    if (submitBtn) {
                                        submitBtn.disabled = disabled;
                                    }
                                    if (finishedBtn) {
                                        finishedBtn.disabled = disabled;
                                    }
                                }

                                form.addEventListener('submit', async function(e) {
                                    e.preventDefault();
                                    clearAjaxError();
                                    setReadingActionsDisabled(true);
                                    try {
                                        const body = new FormData(form);
                                        const res = await fetch(form.action, {
                                            method: 'POST',
                                            headers: {
                                                Accept: 'application/json',
                                                'X-Requested-With': 'XMLHttpRequest',
                                            },
                                            body,
                                        });
                                        const data = await res.json().catch(function() {
                                            return {};
                                        });
                                        if (!res.ok) {
                                            const msg =
                                                (data.errors && data.errors.pages_read && data.errors.pages_read[0]) ||
                                                data.message ||
                                                'Could not save.';
                                            setAjaxError(msg);
                                            return;
                                        }
                                        const pagesInput = form.querySelector('[name="pages_read"]');
                                        if (pagesInput && typeof data.pages_read === 'number') {
                                            pagesInput.value = String(data.pages_read);
                                        }
                                        if (data.percent_read !== null && data.percent_read !== undefined) {
                                            percentEl.textContent = data.percent_read + '% read';
                                            percentEl.hidden = false;
                                            percentEl.setAttribute('aria-hidden', 'false');
                                        } else {
                                            percentEl.textContent = '';
                                            percentEl.hidden = true;
                                            percentEl.setAttribute('aria-hidden', 'true');
                                        }
                                    } catch (err) {
                                        setAjaxError('Something went wrong. Try again.');
                                    } finally {
                                        setReadingActionsDisabled(false);
                                    }
                                });

                                if (finishedBtn && finishedBtn.dataset.shelfUrl && activePanel && emptyPanel) {
                                    finishedBtn.addEventListener('click', async function() {
                                        clearAjaxError();
                                        setReadingActionsDisabled(true);
                                        try {
                                            const fd = new FormData();
                                            fd.append('_token', form.querySelector('[name="_token"]').value);
                                            fd.append('shelf', 'read');
                                            fd.append('return_to', 'landing');
                                            const res = await fetch(finishedBtn.dataset.shelfUrl, {
                                                method: 'POST',
                                                headers: {
                                                    Accept: 'application/json',
                                                    'X-Requested-With': 'XMLHttpRequest',
                                                },
                                                body: fd,
                                            });
                                            const data = await res.json().catch(function() {
                                                return {};
                                            });
                                            if (!res.ok) {
                                                const msg =
                                                    (data.errors && data.errors.shelf && data.errors.shelf[0]) ||
                                                    data.message ||
                                                    'Could not update shelf.';
                                                setAjaxError(msg);
                                                return;
                                            }
                                            activePanel.hidden = true;
                                            emptyPanel.hidden = false;
                                        } catch (err) {
                                            setAjaxError('Something went wrong. Try again.');
                                        } finally {
                                            setReadingActionsDisabled(false);
                                        }
                                    });
                                }
                            })();
                        </script>
                    @else
                        <p class="app-landing-charts__empty">Nothing on your reading shelf yet.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-main>
