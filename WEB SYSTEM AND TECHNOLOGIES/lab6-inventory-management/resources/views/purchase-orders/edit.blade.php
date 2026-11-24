<x-app-layout>
    @include('purchase-orders.partials.styles')

    <div class="po-page">
        <div class="po-container">
            <header class="po-heading">
                <div>
                    <span class="po-eyebrow">INVENTORY MANAGEMENT</span>
                    <h1>Edit Purchase Order</h1>
                    <p>Update the details and products in this purchase order.</p>
                </div>
                <a href="{{ route('purchase-orders.show', $purchaseOrder) }}"
                    class="po-btn secondary">Back to Order</a>
            </header>

            @include('purchase-orders.partials.form')
        </div>
    </div>
</x-app-layout>
