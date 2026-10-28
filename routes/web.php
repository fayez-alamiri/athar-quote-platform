<?php

use App\Http\Controllers\LocaleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/locale/{locale}', [LocaleController::class, 'switch'])
    ->whereIn('locale', ['ar', 'en'])
    ->name('locale.switch');