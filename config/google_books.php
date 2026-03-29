<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Google Books API
    |--------------------------------------------------------------------------
    |
    | Create a key in Google Cloud Console: APIs & Services → Credentials.
    | Enable the "Books API" for your project, then create an API key.
    |
    */

    'api_key' => env('GOOGLE_BOOKS_API_KEY'),

    'base_url' => 'https://www.googleapis.com/books/v1',

];
