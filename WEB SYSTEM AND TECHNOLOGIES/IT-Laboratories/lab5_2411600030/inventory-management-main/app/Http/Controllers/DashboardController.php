<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $totalProducts = Product::count();
        $totalQuantity = Product::sum('quantity');
        $totalValue = Product::selectRaw('SUM(price * quantity) as total')->value('total') ?? 0;
        $lowStockProducts = Product::where('quantity', '<=', 5)->get();

        return view('dashboard', compact('totalProducts', 'totalQuantity', 'totalValue', 'lowStockProducts'));
    }
}