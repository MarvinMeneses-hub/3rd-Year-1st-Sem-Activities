<h2>Purchase Order Approved</h2>

<p>Hello {{ $purchaseOrder->creator->name ?? 'User' }},</p>

<p>Your purchase order has been approved.</p>

<p><strong>Purchase Order:</strong> {{ $purchaseOrder->po_number }}</p>
<p><strong>Supplier:</strong> {{ $purchaseOrder->supplier->company_name ?? 'N/A' }}</p>
<p><strong>Total Amount:</strong> ₱{{ number_format((float) $purchaseOrder->total_amount, 2) }}</p>
<p><strong>Status:</strong> {{ ucfirst($purchaseOrder->status) }}</p>

<p>The purchase order is now ready to be sent to the supplier.</p>

<p>Inventory Management System</p>