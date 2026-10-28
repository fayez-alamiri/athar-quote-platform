<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\QuoteLikeController;
use App\Http\Controllers\SavedQuoteController;
use Illuminate\Support\Facades\Route;

Route::get('/', [QuoteController::class, 'index'])->name('home');

Route::get('/locale/{locale}', [LocaleController::class, 'switch'])
    ->whereIn('locale', ['ar', 'en'])
    ->name('locale.switch');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Quick User Quote creation
    Route::get('/quotes/create/quick', [QuoteController::class, 'createQuick'])->name('quotes.create-quick');
    Route::post('/quotes/quick', [QuoteController::class, 'storeQuick'])->name('quotes.store-quick');

    // Book Quote creation
    Route::get('/quotes/create/book', [QuoteController::class, 'createBook'])->name('quotes.create-book');
    Route::post('/quotes/book', [QuoteController::class, 'storeBook'])->name('quotes.store-book');

    // Likes & Saved Quotes (المحفوظات)
    Route::post('/quotes/{quote}/like', [QuoteLikeController::class, 'toggle'])->name('quotes.like.toggle');
    Route::post('/quotes/{quote}/save', [SavedQuoteController::class, 'toggle'])->name('quotes.save.toggle');
    Route::get('/saved-quotes', [SavedQuoteController::class, 'index'])->name('saved.index');
});