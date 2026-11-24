@php
    $formItems = isset($purchaseOrder)
        ? $purchaseOrder->items
        : collect([null]);
@endphp

<form method="POST"
    action="{{ isset($purchaseOrder) ? route('purchase-orders.update', $purchaseOrder) : route('purchase-orders.store') }}">

    @csrf
    @if(isset($purchaseOrder))
        @method('PUT')
    @endif

    @if($errors->any())
        <div class="po-alert error">
            <strong>Please correct the following:</strong>
            <ul style="list-style:disc;padding-left:20px;margin-top:8px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="po-card">
        <h2 style="font-size:19px;font-weight:750;margin-bottom:18px;">Order Information</h2>

        <div class="po-grid">
            <div class="po-field">
                <label for="supplier_id">Supplier *</label>
                <select class="po-select" id="supplier_id" name="supplier_id" required>
                    <option value="">Select supplier</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}"
                            @selected(old('supplier_id', $purchaseOrder->supplier_id ?? '') == $supplier->id)>
                            {{ $supplier->company_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="po-field">
                <label for="order_date">Order Date *</label>
                <input class="po-input" type="date" id="order_date" name="order_date"
                    value="{{ old('order_date', isset($purchaseOrder) ? $purchaseOrder->order_date->format('Y-m-d') : now()->format('Y-m-d')) }}"
                    required>
            </div>

            <div class="po-field">
                <label for="expected_delivery_date">Expected Delivery Date</label>
                <input class="po-input" type="date" id="expected_delivery_date"
                    name="expected_delivery_date"
                    value="{{ old('expected_delivery_date', isset($purchaseOrder) ? $purchaseOrder->expected_delivery_date?->format('Y-m-d') : '') }}">
            </div>

            <div class="po-field">
                <label for="tax_amount">Tax Amount (₱)</label>
                <input class="po-input" type="number" id="tax_amount" name="tax_amount"
                    min="0" step="0.01"
                    value="{{ old('tax_amount', $purchaseOrder->tax_amount ?? '0.00') }}">
            </div>
        </div>

        <div class="po-field">
            <label for="notes">Notes</label>
            <textarea class="po-textarea" id="notes" name="notes"
                placeholder="Additional order information...">{{ old('notes', $purchaseOrder->notes ?? '') }}</textarea>
        </div>
    </section>

    <section class="po-card">
        <div class="po-items-heading">
            <div>
                <h2 style="font-size:19px;font-weight:750;">Order Items</h2>
                <p class="po-muted">Select products and enter their order quantities.</p>
            </div>
            <button type="button" class="po-btn secondary" id="add-item">+ Add Product</button>
        </div>

        <div id="items-container">
            @foreach($formItems as $index => $item)
                <div class="po-item-row">
                    <div class="po-field" style="margin:0;">
                        <label>Product *</label>
                        <select class="po-select product-select"
                            name="items[{{ $index }}][product_id]" required>
                            <option value="">Select product</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}"
                                    data-cost="{{ $product->unit_price ?? $product->price ?? 0 }}"
                                    @selected(old("items.$index.product_id", $item->product_id ?? '') == $product->id)>
                                    {{ $product->name }} ({{ $product->sku ?? 'No SKU' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="po-field" style="margin:0;">
                        <label>Quantity *</label>
                        <input class="po-input quantity-input" type="number"
                            name="items[{{ $index }}][quantity]" min="1" step="1"
                            value="{{ old("items.$index.quantity", $item->quantity ?? 1) }}" required>
                    </div>

                    <div class="po-field" style="margin:0;">
                        <label>Unit Cost (₱) *</label>
                        <input class="po-input cost-input" type="number"
                            name="items[{{ $index }}][unit_cost]" min="0" step="0.01"
                            value="{{ old("items.$index.unit_cost", $item->unit_cost ?? '') }}"
                            required>
                    </div>

                    <button type="button" class="po-btn danger remove-item">Remove</button>
                </div>
            @endforeach
        </div>

        <div class="po-total">
            <span>Estimated Subtotal:</span>
            <span>₱<span id="subtotal-display">0.00</span></span>
        </div>
        <div class="po-total" style="border-top:1px solid #e5eaf1;">
            <span>Estimated Total:</span>
            <span>₱<span id="total-display">0.00</span></span>
        </div>
        <p class="po-muted" style="font-size:12px;text-align:right;">
            Totals are calculated again by the server when you save.
        </p>
    </section>

    <div class="po-form-actions">
        <button type="submit" class="po-btn">
            {{ isset($purchaseOrder) ? 'Update Purchase Order' : 'Create Purchase Order' }}
        </button>
        <a href="{{ isset($purchaseOrder) ? route('purchase-orders.show', $purchaseOrder) : route('purchase-orders.index') }}"
            class="po-btn secondary">Cancel</a>
    </div>
</form>

<template id="item-template">
    <div class="po-item-row">
        <div class="po-field" style="margin:0;">
            <label>Product *</label>
            <select class="po-select product-select" name="items[__INDEX__][product_id]" required>
                <option value="">Select product</option>
                @foreach($products as $product)
                    <option value="{{ $product->id }}"
                        data-cost="{{ $product->unit_price ?? $product->price ?? 0 }}">
                        {{ $product->name }} ({{ $product->sku ?? 'No SKU' }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="po-field" style="margin:0;">
            <label>Quantity *</label>
            <input class="po-input quantity-input" type="number"
                name="items[__INDEX__][quantity]" min="1" step="1" value="1" required>
        </div>
        <div class="po-field" style="margin:0;">
            <label>Unit Cost (₱) *</label>
            <input class="po-input cost-input" type="number"
                name="items[__INDEX__][unit_cost]" min="0" step="0.01" required>
        </div>
        <button type="button" class="po-btn danger remove-item">Remove</button>
    </div>
</template>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('items-container');
    const template = document.getElementById('item-template');
    const addButton = document.getElementById('add-item');
    const taxInput = document.getElementById('tax_amount');
    let nextIndex = {{ $formItems->count() }};

    function calculateTotals() {
        let subtotal = 0;

        container.querySelectorAll('.po-item-row').forEach(row => {
            const quantity = Number(row.querySelector('.quantity-input').value) || 0;
            const cost = Number(row.querySelector('.cost-input').value) || 0;
            subtotal += quantity * cost;
        });

        const tax = Number(taxInput.value) || 0;

        document.getElementById('subtotal-display').textContent = subtotal.toFixed(2);
        document.getElementById('total-display').textContent = (subtotal + tax).toFixed(2);
    }

    addButton.addEventListener('click', function () {
        const html = template.innerHTML.replaceAll('__INDEX__', nextIndex++);
        container.insertAdjacentHTML('beforeend', html);
        calculateTotals();
    });

    container.addEventListener('click', function (event) {
        if (event.target.classList.contains('remove-item')) {
            const rows = container.querySelectorAll('.po-item-row');

            if (rows.length <= 1) {
                alert('At least one product is required.');
                return;
            }

            event.target.closest('.po-item-row').remove();
            calculateTotals();
        }
    });

    container.addEventListener('change', function (event) {
        if (event.target.classList.contains('product-select')) {
            const selected = event.target.selectedOptions[0];
            const cost = selected?.dataset.cost;

            if (cost !== undefined && cost !== '') {
                event.target.closest('.po-item-row').querySelector('.cost-input').value = cost;
            }
        }

        calculateTotals();
    });

    container.addEventListener('input', calculateTotals);
    taxInput.addEventListener('input', calculateTotals);

    calculateTotals();
});
</script>
