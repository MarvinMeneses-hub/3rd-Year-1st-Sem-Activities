<h2>New Purchase Order</h2>

<p>Hello {{ $purchaseOrder->supplier->contact_person ?? 'Supplier' }},</p>

<p>A new purchase order has been created for your company.</p>

<p><strong>Purchase Order:</strong> {{ $purchaseOrder->po_number }}</p>
<p><strong>Supplier:</strong> {{ $purchaseOrder->supplier->company_name ?? 'N/A' }}</p>
<p><strong>Order Date:</strong> {{ $purchaseOrder->order_date?->format('F d, Y') }}</p>
<p><strong>Expected Delivery:</strong> {{ $purchaseOrder->expected_delivery_date?->format('F d, Y') ?? 'Not specified' }}</p>
<p><strong>Total Amount:</strong> ₱{{ number_format((float) $purchaseOrder->total_amount, 2) }}</p>

<h3>Ordered Products</h3>

<ul>
    @foreach ($purchaseOrder->items as $item)
        <li>
            {{ $item->product->name ?? 'Product' }}
            — Quantity: {{ $item->quantity }}
            — ₱{{ number_format((float) $item->line_total, 2) }}
        </li>
    @endforeach
</ul>

<p>Please review this purchase order.</p>
<p>Inventory Management System</p>