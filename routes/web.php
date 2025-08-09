<?php

use Illuminate\Support\Facades\Route;

// Central app routes (e.g. /login, /register, /dev panel)
Route::view('/', 'home');

// Or move dev panel config here if not tenant-aware


Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
