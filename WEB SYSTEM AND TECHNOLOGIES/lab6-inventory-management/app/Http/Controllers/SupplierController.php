<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::query()->withCount('products');

        if ($request->filled('search')) {
            $search = $request->input('search');

            $query->where(function ($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->input('status') === 'active') {
            $query->where('is_active', true);
        } elseif ($request->input('status') === 'inactive') {
            $query->where('is_active', false);
        }

        $allowedSorts = [
            'company_name',
            'lead_time_days',
            'minimum_order_amount',
            'created_at',
        ];

        $sort = $request->input('sort', 'created_at');
        $direction = $request->input('direction', 'desc');

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'created_at';
        }

        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'desc';
        }

        $suppliers = $query
            ->orderBy($sort, $direction)
            ->paginate(10)
            ->withQueryString();

        return view('suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'contact_person' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:suppliers,email'],
            'phone' => ['required', 'string', 'max:30'],
            'physical_address' => ['required', 'string'],
            'payment_terms' => ['nullable', 'string', 'max:255'],
            'lead_time_days' => ['required', 'integer', 'min:0'],
            'minimum_order_amount' => ['required', 'numeric', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ]);

        Supplier::create($validated);

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Supplier created successfully.');
    }

    public function show(Supplier $supplier)
    {
        $supplier->load([
            'products' => function ($query) {
                $query->latest();
            },
        ]);

        return view('suppliers.show', compact('supplier'));
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'contact_person' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('suppliers', 'email')->ignore($supplier->id),
            ],
            'phone' => ['required', 'string', 'max:30'],
            'physical_address' => ['required', 'string'],
            'payment_terms' => ['nullable', 'string', 'max:255'],
            'lead_time_days' => ['required', 'integer', 'min:0'],
            'minimum_order_amount' => ['required', 'numeric', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ]);

        $supplier->update($validated);

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Supplier updated successfully.');
    }

    public function destroy(Supplier $supplier)
    {
        /*
         * Purchase-order deletion protection will use the final
         * PurchaseOrder schema when that module is implemented.
         * Do not assume its columns before its migration exists.
         */
        if (Schema::hasTable('purchase_orders')
            && Schema::hasColumn('purchase_orders', 'supplier_id')
            && Schema::hasColumn('purchase_orders', 'status')) {

            $hasOpenOrders = DB::table('purchase_orders')
                ->where('supplier_id', $supplier->id)
                ->whereIn('status', ['pending', 'approved', 'sent'])
                ->exists();

            if ($hasOpenOrders) {
                return redirect()
                    ->route('suppliers.index')
                    ->with('error', 'This supplier cannot be deleted because it has open purchase orders.');
            }
        }

        $supplier->delete();

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Supplier moved to trash successfully.');
    }
}
