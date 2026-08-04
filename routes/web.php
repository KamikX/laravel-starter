<?php

use App\Http\Controllers\JoditTestController;
use Illuminate\Support\Facades\Route;

// Route::view('/', 'welcome')->name('home');
Route::view('/', 'pages.home')->name('home');

Route::get('/jodit-test', [JoditTestController::class, 'index'])
    ->name('jodit.test');
