<?php

namespace App\Http\Controllers;

use App\Mail\PurchaseOrderApproved;
use App\Mail\PurchaseOrderCreated;
use App\Mail\PurchaseOrderSent;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use Illuminate\Mail\Mailable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class PurchaseOrderController extends Controller
{
    /**
     * Display all purchase orders.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', PurchaseOrder::class);

        $query = PurchaseOrder::with(['supplier', 'creator'])
            ->latest();

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('po_number', 'like', "%{$search}%")
                    ->orWhereHas('supplier', function ($supplierQuery) use ($search) {
                        $supplierQuery->where('company_name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $purchaseOrders = $query->paginate(10)->withQueryString();

        return view('purchase-orders.index', compact('purchaseOrders'));
    }

    /**
     * Show the purchase order creation form.
     */
    public function create(): View
    {
        $this->authorize('create', PurchaseOrder::class);

        $suppliers = Supplier::active()
            ->orderBy('company_name')
            ->get();

        $products = Product::orderBy('name')->get();

        return view('purchase-orders.create', compact('suppliers', 'products'));
    }

    /**
     * Store a new purchase order and notify the supplier.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', PurchaseOrder::class);

        $validated = $request->validate([
            'supplier_id' => [
                'required',
                'exists:suppliers,id',
                Rule::exists('suppliers', 'id')->where('is_active', true),
            ],
            'order_date' => ['required', 'date'],
            'expected_delivery_date' => [
                'nullable',
                'date',
                'after_or_equal:order_date',
            ],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => [
                'required',
                'distinct',
                'exists:products,id',
            ],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ]);

        $purchaseOrder = DB::transaction(function () use ($validated) {
            $subtotal = 0;

            foreach ($validated['items'] as $item) {
                $subtotal += round(
                    (int) $item['quantity'] * (float) $item['unit_cost'],
                    2
                );
            }

            $taxAmount = round((float) ($validated['tax_amount'] ?? 0), 2);

            $purchaseOrder = PurchaseOrder::create([
                'supplier_id' => $validated['supplier_id'],
                'created_by' => auth()->id(),
                'po_number' => $this->generatePoNumber(),
                'status' => PurchaseOrder::STATUS_PENDING,
                'order_date' => $validated['order_date'],
                'expected_delivery_date' => $validated['expected_delivery_date'] ?? null,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => round($subtotal + $taxAmount, 2),
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                $quantity = (int) $item['quantity'];
                $unitCost = round((float) $item['unit_cost'], 2);

                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $quantity,
                    'unit_cost' => $unitCost,
                    'line_total' => round($quantity * $unitCost, 2),
                ]);
            }

            return $purchaseOrder;
        });

        $purchaseOrder->load(['supplier', 'items.product']);

        $emailSent = $this->sendMailSafely(
            $purchaseOrder->supplier?->email,
            new PurchaseOrderCreated($purchaseOrder)
        );

        $message = 'Purchase order created successfully and submitted for approval.';

        if (!$emailSent) {
            $message .= ' Email notification was not sent. Check the supplier email and mail configuration.';
        }

        return redirect()
            ->route('purchase-orders.show', $purchaseOrder)
            ->with('success', $message);
    }

    /**
     * Display a purchase order.
     */
    public function show(PurchaseOrder $purchaseOrder): View
    {
        $this->authorize('view', $purchaseOrder);

        $purchaseOrder->load([
            'supplier',
            'creator',
            'items.product',
        ]);

        return view('purchase-orders.show', compact('purchaseOrder'));
    }

    /**
     * Show the edit form.
     */
    public function edit(PurchaseOrder $purchaseOrder): View
    {
        $this->authorize('update', $purchaseOrder);

        $purchaseOrder->load('items');

        $suppliers = Supplier::active()
            ->orderBy('company_name')
            ->get();

        $products = Product::orderBy('name')->get();

        return view('purchase-orders.edit', compact(
            'purchaseOrder',
            'suppliers',
            'products'
        ));
    }

    /**
     * Update a draft or rejected purchase order.
     */
    public function update(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $this->authorize('update', $purchaseOrder);

        $validated = $request->validate([
            'supplier_id' => [
                'required',
                'exists:suppliers,id',
                Rule::exists('suppliers', 'id')->where('is_active', true),
            ],
            'order_date' => ['required', 'date'],
            'expected_delivery_date' => [
                'nullable',
                'date',
                'after_or_equal:order_date',
            ],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => [
                'required',
                'distinct',
                'exists:products,id',
            ],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($validated, $purchaseOrder) {
            $subtotal = 0;

            foreach ($validated['items'] as $item) {
                $subtotal += round(
                    (int) $item['quantity'] * (float) $item['unit_cost'],
                    2
                );
            }

            $taxAmount = round((float) ($validated['tax_amount'] ?? 0), 2);

            $purchaseOrder->update([
                'supplier_id' => $validated['supplier_id'],
                'order_date' => $validated['order_date'],
                'expected_delivery_date' => $validated['expected_delivery_date'] ?? null,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => round($subtotal + $taxAmount, 2),
                'notes' => $validated['notes'] ?? null,
                'status' => PurchaseOrder::STATUS_PENDING,
            ]);

            $purchaseOrder->items()->delete();

            foreach ($validated['items'] as $item) {
                $quantity = (int) $item['quantity'];
                $unitCost = round((float) $item['unit_cost'], 2);

                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $quantity,
                    'unit_cost' => $unitCost,
                    'line_total' => round($quantity * $unitCost, 2),
                ]);
            }
        });

        return redirect()
            ->route('purchase-orders.show', $purchaseOrder)
            ->with('success', 'Purchase order updated and submitted for approval.');
    }

    /**
     * Delete a draft purchase order.
     */
    public function destroy(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $this->authorize('delete', $purchaseOrder);

        $purchaseOrder->delete();

        return redirect()
            ->route('purchase-orders.index')
            ->with('success', 'Purchase order deleted successfully.');
    }

    /**
     * Approve a pending purchase order and notify its creator.
     */
    public function approve(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $this->authorize('approve', $purchaseOrder);

        $purchaseOrder->update([
            'status' => PurchaseOrder::STATUS_APPROVED,
        ]);

        $purchaseOrder->load('creator', 'supplier', 'items.product');

        $emailSent = $this->sendMailSafely(
            $purchaseOrder->creator?->email,
            new PurchaseOrderApproved($purchaseOrder)
        );

        $message = 'Purchase order approved successfully.';

        if (!$emailSent) {
            $message .= ' Email notification was not sent. Check the creator email and mail configuration.';
        }

        return back()->with('success', $message);
    }

    /**
     * Reject a pending purchase order.
     */
    public function reject(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $this->authorize('reject', $purchaseOrder);

        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $purchaseOrder->update([
            'status' => PurchaseOrder::STATUS_REJECTED,
            'notes' => $validated['notes'] ?? $purchaseOrder->notes,
        ]);

        return back()->with('success', 'Purchase order rejected.');
    }

    /**
     * Mark an approved purchase order as sent and notify the supplier.
     */
    public function send(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $this->authorize('send', $purchaseOrder);

        $purchaseOrder->update([
            'status' => PurchaseOrder::STATUS_SENT,
        ]);

        $purchaseOrder->load(['supplier', 'items.product']);

        $emailSent = $this->sendMailSafely(
            $purchaseOrder->supplier?->email,
            new PurchaseOrderSent($purchaseOrder)
        );

        $message = 'Purchase order marked as sent.';

        if (!$emailSent) {
            $message .= ' Email notification was not sent. Check the supplier email and mail configuration.';
        }

        return back()->with('success', $message);
    }

    /**
     * Receive a purchase order and add its quantities to inventory.
     */
    public function receive(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $this->authorize('receive', $purchaseOrder);

        DB::transaction(function () use ($purchaseOrder) {
            $lockedOrder = PurchaseOrder::whereKey($purchaseOrder->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedOrder->status !== PurchaseOrder::STATUS_SENT) {
                abort(403, 'This purchase order cannot be received in its current status.');
            }

            $items = $lockedOrder->items()->get();

            if ($items->isEmpty()) {
                abort(422, 'This purchase order has no items to receive.');
            }

            foreach ($items as $item) {
                $product = Product::whereKey($item->product_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $product->increment('quantity', $item->quantity);

                InventoryTransaction::create([
                    'product_id' => $product->id,
                    'user_id' => auth()->id(),
                    'type' => 'stock_in',
                    'quantity' => $item->quantity,
                    'reference_number' => $lockedOrder->po_number,
                    'notes' => 'Stock received from purchase order ' . $lockedOrder->po_number,
                ]);
            }

            $lockedOrder->update([
                'status' => PurchaseOrder::STATUS_RECEIVED,
                'received_at' => now(),
            ]);
        });

        return back()->with(
            'success',
            'Purchase order received. Product quantities and inventory transactions have been updated.'
        );
    }

    /**
     * Send an email without interrupting the purchase-order operation
     * if mail delivery is unavailable.
     */
    private function sendMailSafely(?string $recipient, Mailable $mail): bool
    {
        if (!$recipient || !filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        try {
            Mail::to($recipient)->send($mail);

            return true;
        } catch (Throwable $exception) {
            report($exception);

            return false;
        }
    }

    /**
     * Generate a unique purchase order number.
     */
    private function generatePoNumber(): string
    {
        do {
            $number = 'PO-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6));
        } while (PurchaseOrder::where('po_number', $number)->exists());

        return $number;
    }
}
