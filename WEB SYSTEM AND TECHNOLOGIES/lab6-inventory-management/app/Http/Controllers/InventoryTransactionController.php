<?php

namespace App\Http\Controllers;

use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class InventoryTransactionController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'type' => ['nullable', Rule::in(['stock_in', 'stock_out', 'adjustment'])],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $transactions = InventoryTransaction::with(['product', 'user'])
            ->when($request->filled('product_id'), fn ($query) => $query->where('product_id', $validated['product_id']))
            ->when($request->filled('type'), fn ($query) => $query->where('type', $validated['type']))
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('created_at', '>=', $validated['date_from']))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('created_at', '<=', $validated['date_to']))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $products = Product::orderBy('name')->get(['id', 'name']);

        $summary = [
            'total' => InventoryTransaction::count(),
            'stock_in' => (int) InventoryTransaction::where('type', 'stock_in')->sum('quantity'),
            'stock_out' => (int) InventoryTransaction::where('type', 'stock_out')->sum('quantity'),
            'low_stock' => Product::lowStock()->count(),
            'out_of_stock' => Product::outOfStock()->count(),
        ];

        return view('transactions.index', compact('transactions', 'products', 'summary'));
    }

    public function create()
    {
        $products = Product::orderBy('name')->get(['id', 'name', 'quantity', 'reorder_level']);

        $submissionToken = (string) Str::uuid();

        return view('transactions.create', compact('products', 'submissionToken'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'type' => ['required', Rule::in(['stock_in', 'stock_out', 'adjustment'])],
            'quantity' => ['required', 'integer', 'not_in:0'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'submission_token' => ['required', 'uuid'],
        ]);

        if (InventoryTransaction::where('submission_token', $validated['submission_token'])->exists()) {
            return redirect()
                ->route('transactions.index')
                ->with('success', 'This transaction was already recorded. No duplicate was created.');
        }

        if (
            in_array($validated['type'], ['stock_in', 'stock_out'], true)
            && $validated['quantity'] < 1
        ) {
            throw ValidationException::withMessages([
                'quantity' => 'Stock-in and stock-out quantities must be positive whole numbers.',
            ]);
        }

        try {
            DB::transaction(function () use ($validated) {
                $product = Product::whereKey($validated['product_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                $delta = match ($validated['type']) {
                    'stock_in' => abs($validated['quantity']),
                    'stock_out' => -abs($validated['quantity']),
                    'adjustment' => $validated['quantity'],
                };

                if (($product->quantity + $delta) < 0) {
                    throw ValidationException::withMessages([
                        'quantity' => "Insufficient stock. Current available quantity: {$product->quantity}.",
                    ]);
                }

                // The InventoryTransactionObserver handles the stock update.
                // Do not update the product quantity here, or stock changes twice.
                InventoryTransaction::create([
                    'product_id' => $product->id,
                    'user_id' => auth()->id(),
                    'type' => $validated['type'],
                    'quantity' => $validated['quantity'],
                    'reference_number' => $validated['reference_number'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                    'submission_token' => $validated['submission_token'],
                ]);
            });
        } catch (QueryException $exception) {
            $alreadyRecorded = InventoryTransaction::where(
                'submission_token',
                $validated['submission_token']
            )->exists();

            if (!$alreadyRecorded) {
                throw $exception;
            }

            return redirect()
                ->route('transactions.index')
                ->with('success', 'This transaction was already recorded. No duplicate was created.');
        }

        return redirect()
            ->route('transactions.index')
            ->with('success', 'Inventory transaction recorded successfully.');
    }

    public function show(InventoryTransaction $transaction)
    {
        $transaction->load(['product', 'user']);

        return view('transactions.show', compact('transaction'));
    }

    public function report(Request $request)
    {
        $validated = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $query = InventoryTransaction::query()
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('created_at', '>=', $validated['date_from']))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('created_at', '<=', $validated['date_to']));

        $summary = [
            'transactions' => (clone $query)->count(),
            'stock_in' => (int) (clone $query)->where('type', 'stock_in')->sum('quantity'),
            'stock_out' => (int) (clone $query)->where('type', 'stock_out')->sum('quantity'),
            'adjustments' => (int) (clone $query)->where('type', 'adjustment')->sum('quantity'),
        ];

        $transactions = (clone $query)
            ->with(['product', 'user'])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('transactions.report', compact('summary', 'transactions'));
    }

    public function summary()
    {
        return response()->json([
            'generated_at' => now()->toIso8601String(),
            'total_transactions' => InventoryTransaction::count(),
            'stock_in_units' => (int) InventoryTransaction::where('type', 'stock_in')->sum('quantity'),
            'stock_out_units' => (int) InventoryTransaction::where('type', 'stock_out')->sum('quantity'),
            'total_products' => Product::count(),
            'total_units' => (int) Product::sum('quantity'),
            'low_stock_count' => Product::lowStock()->count(),
            'out_of_stock_count' => Product::outOfStock()->count(),
        ]);
    }
}
