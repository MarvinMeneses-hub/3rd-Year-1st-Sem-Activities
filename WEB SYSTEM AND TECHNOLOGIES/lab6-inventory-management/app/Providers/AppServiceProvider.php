<?php

namespace App\Providers;

use App\Models\InventoryTransaction;
use App\Observers\InventoryTransactionObserver;
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
        InventoryTransaction::observe(InventoryTransactionObserver::class);
    }
}
