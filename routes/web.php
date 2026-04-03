<?php

use App\Http\Controllers\LibraryController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'landing'])->name('landing');
Route::get('/library', [PageController::class, 'library'])->name('library');
Route::get('/search', [PageController::class, 'search'])->name('search');
Route::post('/library/save', [LibraryController::class, 'addToLibrary'])->name('library.save');
Route::post('/library/{book}/shelf', [LibraryController::class, 'updateShelf'])->name('library.updateShelf');
Route::post('/library/{book}/progress', [LibraryController::class, 'updateProgress'])->name('library.updateProgress');
Route::delete('/library/{book}', [LibraryController::class, 'destroy'])->name('library.destroy');
Route::get('/nextread', [PageController::class, 'nextread'])->name('nextread');

// Route->group(['prefix' => 'library'], function () {});
