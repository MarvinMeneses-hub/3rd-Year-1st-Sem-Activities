<x-app-layout>
    @include('suppliers.partials.styles')

    <div class="suppliers-page">
        <div class="suppliers-container">
            <header class="suppliers-heading">
                <div>
                    <span class="page-eyebrow">SUPPLIER MANAGEMENT</span>
                    <h1>Edit Supplier</h1>
                    <p>Update the information for {{ $supplier->company_name }}.</p>
                </div>
                <a href="{{ route('suppliers.index') }}" class="supplier-secondary-btn">Back to Suppliers</a>
            </header>

            @if ($errors->any())
                <div class="supplier-notice error">
                    Please check the form for errors and try again.
                </div>
            @endif

            <section class="supplier-form-panel">
                <form method="POST" action="{{ route('suppliers.update', $supplier) }}">
                    @csrf
                    @method('PUT')
                    @include('suppliers.partials.form', ['submitLabel' => 'Update Supplier'])
                </form>
            </section>
        </div>
    </div>
</x-app-layout>