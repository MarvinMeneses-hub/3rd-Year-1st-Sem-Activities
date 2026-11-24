<x-app-layout>
    @include('purchase-orders.partials.styles')

    <div class="po-page">
        <div class="po-container">
            <header class="po-heading">
                <div>
                    <span class="po-eyebrow">INVENTORY MANAGEMENT</span>
                    <h1>Create Purchase Order</h1>
                    <p>Create an order for products from your registered supplier.</p>
                </div>
            </header>

            @include('purchase-orders.partials.form')
        </div>
    </div>
</x-app-layout>
