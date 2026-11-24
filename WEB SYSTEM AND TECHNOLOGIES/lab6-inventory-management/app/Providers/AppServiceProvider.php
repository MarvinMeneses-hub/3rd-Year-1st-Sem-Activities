<?php

namespace App\Providers;

use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Observers\InventoryTransactionObserver;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Preserve the existing inventory transaction observer.
        InventoryTransaction::observe(InventoryTransactionObserver::class);

        // Share dashboard widget data with the dashboard view.
        View::composer('dashboard', function ($view) {
            $view->with('dashboardWidgets', [
                'totalProducts' => Product::count(),

                'totalQuantity' => Product::sum('quantity'),

                'totalValue' => Product::selectRaw(
                    'COALESCE(SUM(price * quantity), 0) as total'
                )->value('total'),

                'lowStock' => Product::lowStock()->count(),

                'outOfStock' => Product::outOfStock()->count(),

                'pendingPurchaseOrders' => PurchaseOrder::where(
                    'status',
                    PurchaseOrder::STATUS_PENDING
                )->count(),
            ]);
        });
    }
}
