<?php

use App\Filament\Pages\Login;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// route for custom login

// Route::get('/admin/login', [Login::class, '__invoke'])
//     ->name('filament.admin.pages.login');

Route::get('/admin/login', [Login::class, '__invoke'])
    ->name('filament.auth.auth.login');
