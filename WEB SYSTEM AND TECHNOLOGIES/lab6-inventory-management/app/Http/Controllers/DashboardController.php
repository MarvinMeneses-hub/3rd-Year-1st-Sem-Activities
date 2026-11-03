<?php

namespace App\Http\Controllers;

use App\Models\Product;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $totalProducts = Product::count();

        $totalQuantity = Product::sum('quantity');

        $totalValue = Product::selectRaw(
            'COALESCE(SUM(price * quantity), 0) as total'
        )->value('total');

        // Respect each product's configured reorder level.
        $lowStockProducts = Product::lowStock()
            ->orderBy('quantity')
            ->get();

        // Products with no remaining stock.
        $outOfStockProducts = Product::outOfStock()
            ->orderBy('name')
            ->get();

        $outOfStockCount = $outOfStockProducts->count();

        return view('dashboard', compact(
            'totalProducts',
            'totalQuantity',
            'totalValue',
            'lowStockProducts',
            'outOfStockProducts',
            'outOfStockCount'
        ));
    }
}