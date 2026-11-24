<x-app-layout>
    @include('purchase-orders.partials.styles')

    <div class="po-page">
        <div class="po-container">
            <header class="po-heading">
                <div>
                    <span class="po-eyebrow">INVENTORY MANAGEMENT</span>
                    <h1>Purchase Order Details</h1>
                    <p>{{ $purchaseOrder->po_number }}</p>
                </div>
                <a href="{{ route('purchase-orders.index') }}" class="po-btn secondary">
                    Back to Purchase Orders
                </a>
            </header>

            @if(session('success'))
                <div class="po-alert success">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="po-alert error">{{ session('error') }}</div>
            @endif

            <section class="po-card">
                <div class="po-toolbar">
                    <h2 style="font-size:20px;font-weight:750;">
                        {{ $purchaseOrder->po_number }}
                    </h2>
                    <span class="po-status {{ $purchaseOrder->status }}">
                        {{ $purchaseOrder->status }}
                    </span>
                </div>

                <div class="po-detail-grid">
                    <div>
                        <div class="po-detail-label">Supplier</div>
                        <div class="po-detail-value">
                            {{ $purchaseOrder->supplier->company_name ?? 'Unknown supplier' }}
                        </div>
                    </div>

                    <div>
                        <div class="po-detail-label">Created By</div>
                        <div class="po-detail-value">
                            {{ $purchaseOrder->creator->name ?? 'Unknown' }}
                        </div>
                    </div>

                    <div>
                        <div class="po-detail-label">Order Date</div>
                        <div class="po-detail-value">
                            {{ $purchaseOrder->order_date?->format('M d, Y') }}
                        </div>
                    </div>

                    <div>
                        <div class="po-detail-label">Expected Delivery</div>
                        <div class="po-detail-value">
                            {{ $purchaseOrder->expected_delivery_date?->format('M d, Y') ?? 'Not specified' }}
                        </div>
                    </div>

                    <div>
                        <div class="po-detail-label">Received At</div>
                        <div class="po-detail-value">
                            {{ $purchaseOrder->received_at?->format('M d, Y h:i A') ?? 'Not received' }}
                        </div>
                    </div>

                    <div>
                        <div class="po-detail-label">Supplier Contact</div>
                        <div class="po-detail-value">
                            {{ $purchaseOrder->supplier->contact_person ?? '—' }}
                        </div>
                    </div>
                </div>
            </section>

            <section class="po-card">
                <h2 style="font-size:19px;font-weight:750;margin-bottom:18px;">Ordered Products</h2>

                <div class="po-table-wrap">
                    <table class="po-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>SKU</th>
                                <th>Quantity</th>
                                <th>Unit Cost</th>
                                <th>Line Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($purchaseOrder->items as $item)
                                <tr>
                                    <td>{{ $item->product->name ?? 'Deleted product' }}</td>
                                    <td>{{ $item->product->sku ?? '—' }}</td>
                                    <td>{{ number_format($item->quantity) }}</td>
                                    <td>₱{{ number_format((float)$item->unit_cost, 2) }}</td>
                                    <td>₱{{ number_format((float)$item->line_total, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="po-empty">No products in this order.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div style="max-width:350px;margin-left:auto;">
                    <div class="po-total">
                        <span>Subtotal</span>
                        <span>₱{{ number_format((float)$purchaseOrder->subtotal, 2) }}</span>
                    </div>
                    <div class="po-total">
                        <span>Tax</span>
                        <span>₱{{ number_format((float)$purchaseOrder->tax_amount, 2) }}</span>
                    </div>
                    <div class="po-total" style="border-top:2px solid #e5eaf1;">
                        <span>Total</span>
                        <span>₱{{ number_format((float)$purchaseOrder->total_amount, 2) }}</span>
                    </div>
                </div>
            </section>

            @if($purchaseOrder->notes)
                <section class="po-card">
                    <h2 style="font-size:18px;font-weight:750;margin-bottom:8px;">Notes</h2>
                    <p style="white-space:pre-wrap;">{{ $purchaseOrder->notes }}</p>
                </section>
            @endif

            <section class="po-card">
                <h2 style="font-size:18px;font-weight:750;margin-bottom:15px;">Order Actions</h2>

                <div class="po-actions">
                    @if(in_array($purchaseOrder->status, ['draft', 'rejected'], true))
                        <a href="{{ route('purchase-orders.edit', $purchaseOrder) }}"
                            class="po-btn secondary">Edit Order</a>

                        <form method="POST"
                            action="{{ route('purchase-orders.destroy', $purchaseOrder) }}"
                            onsubmit="return confirm('Delete this purchase order?')">
                            @csrf
                            @method('DELETE')
                            <button class="po-btn danger" type="submit">Delete Order</button>
                        </form>
                    @endif

                    @if($purchaseOrder->status === 'pending')
                        <form method="POST" action="{{ route('purchase-orders.approve', $purchaseOrder) }}">
                            @csrf
                            <button class="po-btn success" type="submit">Approve Order</button>
                        </form>

                        <form method="POST" action="{{ route('purchase-orders.reject', $purchaseOrder) }}">
                            @csrf
                            <input type="hidden" name="notes" value="Purchase order rejected.">
                            <button class="po-btn danger" type="submit"
                                onclick="return confirm('Reject this purchase order?')">
                                Reject Order
                            </button>
                        </form>
                    @endif

                    @if($purchaseOrder->status === 'approved')
                        <form method="POST" action="{{ route('purchase-orders.send', $purchaseOrder) }}">
                            @csrf
                            <button class="po-btn" type="submit">Mark as Sent</button>
                        </form>
                    @endif

                    @if($purchaseOrder->status === 'sent')
                        <form method="POST" action="{{ route('purchase-orders.receive', $purchaseOrder) }}"
                            onsubmit="return confirm('Confirm that all items in this order have been received? This will increase inventory quantities.')">
                            @csrf
                            <button class="po-btn success" type="submit">Receive Order</button>
                        </form>
                    @endif

                    @if($purchaseOrder->status === 'received')
                        <span class="po-muted">This order has already been received.</span>
                    @endif
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
