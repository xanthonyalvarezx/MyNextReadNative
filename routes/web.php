<?php

use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'landing'])->name('landing');
Route::get('/library', [PageController::class, 'library'])->name('library');
Route::get('/search', [PageController::class, 'search'])->name('search');


// Route->group(['prefix' => 'library'], function () {});
