
<?php

use App\Http\Controllers\AccountController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard.index');
})->name('dashboard');

Route::get('/accounts', [AccountController::class, 'index'])
    ->name('accounts.index');

Route::get('/accounts/create', [AccountController::class, 'create'])
    ->name('accounts.create');

Route::post('/accounts', [AccountController::class, 'store'])
    ->name('accounts.store');
