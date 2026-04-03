<nav class="app-nav" aria-label="Main">
    <a class="app-nav-logo" href="{{ route('landing') }}">
        <img
            src="{{ str_replace(' ', '%20', asset('My Next Read logo design.png')) }}"
            alt="My Next Read"
            width="200"
            height="60"
            decoding="async"
        />
    </a>
    <div class="app-nav-links">
        <a class="app-nav-link" href="{{ route('landing') }}">Home</a>
        <a class="app-nav-link" href="{{ route('library') }}">Library</a>
        <a class="app-nav-link" href="{{ route('search') }}">Search</a>
        <a class="app-nav-link" href="{{ route('nextread') }}">Your Next Read</a>
    </div>
</nav>
