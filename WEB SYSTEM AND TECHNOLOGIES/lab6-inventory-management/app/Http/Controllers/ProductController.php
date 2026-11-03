<?php

namespace App\Http\Controllers;

use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $products = Product::when($search, function ($query, $search) {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('supplier', 'like', "%{$search}%");
            });
        })->paginate(5)->withQueryString();

        return view('products.index', compact('products'));
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:255', Rule::unique('products', 'sku')],
            'description' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:0'],
            'reorder_level' => ['required', 'integer', 'min:0'],
            'price' => ['required', 'numeric', 'min:0'],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
            'supplier' => ['nullable', 'string', 'max:255'],
        ]);

        $validated['unit_price'] = $validated['unit_price'] ?? $validated['price'];
        $validated['price'] = $validated['unit_price'];

        Product::create($validated);

        return redirect()->route('products.index')->with('success', 'Product created successfully!');
    }

    public function show(Product $product)
    {
        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:255', Rule::unique('products', 'sku')->ignore($product->id)],
            'description' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:0'],
            'reorder_level' => ['required', 'integer', 'min:0'],
            'price' => ['required', 'numeric', 'min:0'],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
            'supplier' => ['nullable', 'string', 'max:255'],
        ]);

        $validated['unit_price'] = $validated['unit_price'] ?? $validated['price'];
        $validated['price'] = $validated['unit_price'];

        DB::transaction(function () use ($validated, $product) {
            $lockedProduct = Product::whereKey($product->id)->lockForUpdate()->firstOrFail();
            $requestedQuantity = (int) $validated['quantity'];
            $quantityDelta = $requestedQuantity - $lockedProduct->quantity;

            unset($validated['quantity']);
            $lockedProduct->update($validated);

            if ($quantityDelta !== 0) {
                InventoryTransaction::create([
                    'product_id' => $lockedProduct->id,
                    'user_id' => auth()->id(),
                    'type' => 'adjustment',
                    'quantity' => $quantityDelta,
                    'reference_number' => 'PRODUCT-EDIT-' . now()->format('YmdHis'),
                    'notes' => 'Stock quantity adjusted through the product edit form.',
                ]);
            }
        });

        return redirect()->route('products.index')->with('success', 'Product updated successfully!');
    }

    public function destroy(Product $product)
    {
        if ($product->inventoryTransactions()->exists()) {
            return redirect()->route('products.index')->with(
                'error',
                'This product has transaction history and cannot be deleted. Keep it to preserve the inventory audit trail.'
            );
        }

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted successfully!');
    }
}
