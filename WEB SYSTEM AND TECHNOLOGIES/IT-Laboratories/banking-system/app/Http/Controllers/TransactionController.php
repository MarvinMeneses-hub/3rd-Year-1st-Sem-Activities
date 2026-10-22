<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class TransactionController extends Controller
{
    public function index(): JsonResponse
    {
        $transactions = Transaction::with('account')
            ->latest()
            ->paginate(15);

        return response()->json($transactions);
    }

    public function create(): JsonResponse
    {
        $accounts = Account::orderBy('account_number')->get();

        return response()->json($accounts);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'account_id' => [
                'required',
                'integer',
                'exists:accounts,id',
            ],
            'type' => [
                'required',
                'in:deposit,withdrawal',
            ],
            'amount' => [
                'required',
                'numeric',
                'min:1',
                'max:9999999999999.99',
            ],
            'reference_number' => [
                'nullable',
                'string',
                'max:255',
            ],
            'notes' => [
                'nullable',
                'string',
            ],
        ]);

        try {
            $transaction = DB::transaction(
                function () use ($validated) {
                    $account = Account::whereKey(
                        $validated['account_id']
                    )
                        ->lockForUpdate()
                        ->firstOrFail();

                    $amount = (float) $validated['amount'];

                    if ($validated['type'] === 'withdrawal') {
                        $remainingBalance =
                            (float) $account->balance - $amount;

                        if (
                            $remainingBalance < 0
                            || $remainingBalance
                                < (float) $account->minimum_balance
                        ) {
                            throw new RuntimeException(
                                'Insufficient funds. You must maintain the minimum balance of ₱'
                                . number_format(
                                    (float) $account->minimum_balance,
                                    2
                                )
                                . '.'
                            );
                        }
                    }

                    return Transaction::create([
                        'account_id' => $account->id,
                        'type' => $validated['type'],
                        'amount' => $amount,
                        'reference_number' =>
                            $validated['reference_number'] ?? null,
                        'notes' => $validated['notes'] ?? null,
                    ]);
                }
            );
        } catch (RuntimeException $exception) {
            throw ValidationException::withMessages([
                'amount' => $exception->getMessage(),
            ]);
        }

        $transaction->load('account');

        return response()->json([
            'message' => 'Transaction recorded successfully.',
            'transaction' => $transaction,
            'account_balance' => $transaction->account
                ->fresh()
                ->balance,
        ], 201);
    }
}
