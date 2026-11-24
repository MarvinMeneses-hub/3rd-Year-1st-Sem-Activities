<x-app-layout>
    @include('suppliers.partials.styles')

    <div class="suppliers-page">
        <div class="suppliers-container">
            <header class="suppliers-heading">
                <div>
                    <span class="page-eyebrow">SUPPLIER MANAGEMENT</span>
                    <h1>{{ $supplier->company_name }}</h1>
                    <p>Supplier profile and associated product records.</p>
                </div>

                <div style="display:flex; gap:10px; flex-wrap:wrap;">
                    <a href="{{ route('suppliers.index') }}" class="supplier-secondary-btn">Back</a>
                    <a href="{{ route('suppliers.edit', $supplier) }}" class="supplier-primary-btn">Edit Supplier</a>
                </div>
            </header>

            @if (session('success'))
                <div class="supplier-notice success">{{ session('success') }}</div>
            @endif

            <div class="supplier-detail-grid">
                <section class="supplier-detail-panel">
                    <h2>Company Information</h2>

                    <div class="supplier-detail-item">
                        <small>Company Name</small>
                        <strong>{{ $supplier->company_name }}</strong>
                    </div>

                    <div class="supplier-detail-item">
                        <small>Physical Address</small>
                        <strong>{{ $supplier->physical_address }}</strong>
                    </div>

                    <div class="supplier-detail-item">
                        <small>Supplier Status</small>
                        <span class="supplier-status {{ $supplier->is_active ? 'active' : 'inactive' }}">
                            {{ $supplier->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>

                    <div class="supplier-detail-item">
                        <small>Date Registered</small>
                        <strong>{{ $supplier->created_at?->format('M d, Y') ?? '—' }}</strong>
                    </div>
                </section>

                <section class="supplier-detail-panel">
                    <h2>Contact Information</h2>

                    <div class="supplier-detail-item">
                        <small>Contact Person</small>
                        <strong>{{ $supplier->contact_person }}</strong>
                    </div>

                    <div class="supplier-detail-item">
                        <small>Email Address</small>
                        <strong>{{ $supplier->email }}</strong>
                    </div>

                    <div class="supplier-detail-item">
                        <small>Phone Number</small>
                        <strong>{{ $supplier->phone }}</strong>
                    </div>

                    <div class="supplier-detail-item">
                        <small>Payment Terms</small>
                        <strong>{{ $supplier->payment_terms ?: 'Not specified' }}</strong>
                    </div>
                </section>

                <section class="supplier-detail-panel">
                    <h2>Ordering Information</h2>

                    <div class="supplier-detail-item">
                        <small>Lead Time</small>
                        <strong>{{ $supplier->lead_time_days }} days</strong>
                    </div>

                    <div class="supplier-detail-item">
                        <small>Minimum Order Amount</small>
                        <strong>{{ number_format((float) $supplier->minimum_order_amount, 2) }}</strong>
                    </div>
                </section>

                <section class="supplier-detail-panel">
                    <h2>Supplier Summary</h2>

                    <div class="supplier-detail-item">
                        <small>Linked Products</small>
                        <strong>{{ $supplier->products->count() }}</strong>
                    </div>

                    <div class="supplier-detail-item">
                        <small>Contact Summary</small>
                        <strong>{{ $supplier->full_contact }}</strong>
                    </div>
                </section>
            </div>

            <section class="supplier-panel" style="margin-top:18px;">
                <div class="supplier-panel-header">
                    <div>
                        <h2>Products Supplied</h2>
                        <p class="muted-copy">Products currently linked to this supplier</p>
                    </div>
                    <span class="supplier-record-count">{{ $supplier->products->count() }} products</span>
                </div>

                <div class="supplier-table-wrap">
                    <table class="supplier-table">
                        <thead>
                            <tr>
                                <th>PRODUCT</th>
                                <th>SKU</th>
                                <th>CATEGORY</th>
                                <th>QUANTITY</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($supplier->products as $product)
                                <tr>
                                    <td><strong>{{ $product->name }}</strong></td>
                                    <td>{{ $product->sku ?: '—' }}</td>
                                    <td>{{ $product->category ?: '—' }}</td>
                                    <td>{{ $product->quantity }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="supplier-empty">
                                        No products are linked to this supplier yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
