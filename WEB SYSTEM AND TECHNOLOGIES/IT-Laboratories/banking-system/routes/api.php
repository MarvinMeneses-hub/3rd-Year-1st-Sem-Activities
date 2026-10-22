
<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransactionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

Route::get('/transactions', [TransactionController::class, 'index'])
    ->name('transactions.index');

Route::get('/transactions/create', [TransactionController::class, 'create'])
    ->name('transactions.create');

Route::post('/transactions', [TransactionController::class, 'store'])
    ->name('transactions.store');
