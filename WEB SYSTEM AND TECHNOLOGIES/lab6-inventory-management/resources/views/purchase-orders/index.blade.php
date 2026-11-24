<x-app-layout>
    @include('purchase-orders.partials.styles')

    <div class="po-page">
        <div class="po-container">
            <header class="po-heading">
                <div>
                    <span class="po-eyebrow">INVENTORY MANAGEMENT</span>
                    <h1>Purchase Orders</h1>
                    <p>Manage supplier orders and monitor incoming inventory.</p>
                </div>
                <a href="{{ route('purchase-orders.create') }}" class="po-btn">
                    + Create Purchase Order
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
                    <div>
                        <h2 style="font-size:19px;font-weight:750;">Order Directory</h2>
                        <p class="po-muted">Search and filter your purchase orders.</p>
                    </div>

                    <form method="GET" action="{{ route('purchase-orders.index') }}" class="po-search">
                        <input class="po-input" type="search" name="search"
                            value="{{ request('search') }}" placeholder="Search PO or supplier...">

                        <select class="po-select" name="status" style="width:auto;">
                            <option value="">All statuses</option>
                            @foreach(['draft', 'pending', 'approved', 'rejected', 'sent', 'received', 'cancelled'] as $status)
                                <option value="{{ $status }}" @selected(request('status') === $status)>
                                    {{ ucfirst($status) }}
                                </option>
                            @endforeach
                        </select>

                        <button class="po-btn" type="submit">Filter</button>
                        <a href="{{ route('purchase-orders.index') }}" class="po-btn secondary">Reset</a>
                    </form>
                </div>

                <div class="po-table-wrap">
                    <table class="po-table">
                        <thead>
                            <tr>
                                <th>PO Number</th>
                                <th>Supplier</th>
                                <th>Order Date</th>
                                <th>Expected Delivery</th>
                                <th>Total Amount</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($purchaseOrders as $order)
                                <tr>
                                    <td>
                                        <a href="{{ route('purchase-orders.show', $order) }}">
                                            {{ $order->po_number }}
                                        </a>
                                    </td>
                                    <td>{{ $order->supplier->company_name ?? 'Unknown supplier' }}</td>
                                    <td>{{ $order->order_date?->format('M d, Y') }}</td>
                                    <td>{{ $order->expected_delivery_date?->format('M d, Y') ?? '—' }}</td>
                                    <td>₱{{ number_format((float)$order->total_amount, 2) }}</td>
                                    <td>
                                        <span class="po-status {{ $order->status }}">
                                            {{ $order->status }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('purchase-orders.show', $order) }}">View details</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="po-empty">
                                        No purchase orders found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="po-pagination">
                    {{ $purchaseOrders->links() }}
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
