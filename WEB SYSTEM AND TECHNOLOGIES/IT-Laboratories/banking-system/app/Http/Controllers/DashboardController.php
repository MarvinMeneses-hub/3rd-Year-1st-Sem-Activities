<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function stats(): JsonResponse
    {
        return response()->json([
            'total_accounts' => Account::count(),

            'total_money_in_bank' => (float) Account::sum('balance'),

            'total_transactions_today' => Transaction::whereDate(
                'created_at',
                now()->toDateString()
            )->count(),
        ]);
    }
}