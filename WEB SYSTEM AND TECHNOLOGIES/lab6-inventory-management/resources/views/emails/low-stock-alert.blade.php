<h2>Low Stock Alert</h2>

<p>The inventory system has detected a low-stock product.</p>

<p><strong>Product:</strong> {{ $product->name }}</p>
<p><strong>SKU:</strong> {{ $product->sku ?? 'N/A' }}</p>
<p><strong>Current Quantity:</strong> {{ $product->quantity }}</p>
<p><strong>Reorder Level:</strong> {{ $product->reorder_level }}</p>
<p><strong>Stock Status:</strong> {{ $product->stock_status }}</p>

<p>Please review this product and arrange replenishment if necessary.</p>

<p>Inventory Management System</p>