<?php

use App\Filament\Pages\ChooseRole;
use App\Filament\Pages\Login;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// route for custom login

// Route::get('/admin/login', [Login::class, '__invoke'])
//     ->name('filament.admin.pages.login');

Route::get('/admin/login', [Login::class, '__invoke'])
    ->name('filament.auth.auth.login');

Route::post('/admin/select-school', function (Request $request) {
    $schoolId = $request->input('school_id');

    if (! $schoolId) {
        return back()->withErrors(['school_id' => 'Please select a school.']);
    }

    session(['active_school_id' => $schoolId]);

    return redirect()->intended(filament()->getUrl());
})->name('select.school');

Route::get('/admin/choose-role/{school}', [ChooseRole::class, '__invoke'])
    ->name('choose.role');

// THIS IS THE ROUTE YOU NEED TO ADD OR UPDATE:
Route::post('/admin/select-school', function (Request $request) {
    $schoolId = $request->input('school_id');
    $role = $request->input('role');

    if (! $schoolId || ! $role) {
        return back()->withErrors(['role' => 'Please select a school and role.']);
    }

    session([
        'active_school_id' => $schoolId,
        'active_role' => $role,
    ]);

    return redirect()->intended(filament()->getUrl());
})->name('select.school');
