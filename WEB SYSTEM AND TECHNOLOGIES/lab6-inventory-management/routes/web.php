<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryTransactionController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PurchaseOrderController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SupplierReportController;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/supplier-performance', [SupplierReportController::class, 'index'])
    ->name('suppliers.performance');
Route::get('/supplier-performance/export/csv', [SupplierReportController::class, 'exportCsv'])
    ->name('suppliers.performance.csv');
Route::get('/supplier-performance/export/pdf', [SupplierReportController::class, 'exportPdf'])
    ->name('suppliers.performance.pdf');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])
        ->name('notifications.index');

    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])
        ->name('notifications.read');

    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])
        ->name('notifications.markAllAsRead');

    // Products
    Route::resource('products', ProductController::class);

    // Suppliers
    Route::resource('suppliers', SupplierController::class);

// Purchase Orders
Route::post('/purchase-orders/{purchaseOrder}/approve', [PurchaseOrderController::class, 'approve'])
    ->name('purchase-orders.approve');

Route::post('/purchase-orders/{purchaseOrder}/reject', [PurchaseOrderController::class, 'reject'])
    ->name('purchase-orders.reject');

Route::post('/purchase-orders/{purchaseOrder}/send', [PurchaseOrderController::class, 'send'])
    ->name('purchase-orders.send');

Route::post('/purchase-orders/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive'])
    ->name('purchase-orders.receive');

Route::resource('purchase-orders', PurchaseOrderController::class);

    // Inventory transactions
    Route::get('/transactions/report', [InventoryTransactionController::class, 'report'])
        ->name('transactions.report');

    Route::get('/api/inventory/summary', [InventoryTransactionController::class, 'summary'])
        ->name('api.inventory.summary');

    Route::resource('transactions', InventoryTransactionController::class)
        ->only(['index', 'create', 'store', 'show']);
});

require __DIR__.'/auth.php';
