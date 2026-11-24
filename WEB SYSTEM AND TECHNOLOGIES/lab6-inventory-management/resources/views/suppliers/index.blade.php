<x-app-layout>
    @include('suppliers.partials.styles')

    <div class="suppliers-page">
        <div class="suppliers-container">
            <header class="suppliers-heading">
                <div>
                    <span class="page-eyebrow">INVENTORY MANAGEMENT</span>
                    <h1>Suppliers</h1>
                    <p>Manage supplier information and keep your supplier records organized.</p>
                </div>

                <a href="{{ route('suppliers.create') }}" class="supplier-primary-btn">
                    + Add New Supplier
                </a>
            </header>

            @if (session('success'))
                <div class="supplier-notice success" role="status">{{ session('success') }}</div>
            @endif

            @if (session('error'))
                <div class="supplier-notice error" role="alert">{{ session('error') }}</div>
            @endif

            <section class="supplier-toolbar">
                <div>
                    <h2>Supplier Directory</h2>
                    <p class="muted-copy">Search, filter, and manage your registered suppliers.</p>
                </div>

                <form method="GET" action="{{ route('suppliers.index') }}" class="supplier-search">
                    <input type="search" name="search" value="{{ request('search') }}" placeholder="Search suppliers..." aria-label="Search suppliers">

                    <select name="status" aria-label="Filter by status">
                        <option value="">All statuses</option>
                        <option value="active" @selected(request('status') === 'active')>Active</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                    </select>

                    <select name="sort" aria-label="Sort suppliers">
                        <option value="created_at" @selected(request('sort', 'created_at') === 'created_at')>Date added</option>
                        <option value="company_name" @selected(request('sort') === 'company_name')>Company name</option>
                        <option value="lead_time_days" @selected(request('sort') === 'lead_time_days')>Lead time</option>
                        <option value="minimum_order_amount" @selected(request('sort') === 'minimum_order_amount')>Minimum order</option>
                    </select>

                    <select name="direction" aria-label="Sort direction">
                        <option value="desc" @selected(request('direction', 'desc') === 'desc')>Descending</option>
                        <option value="asc" @selected(request('direction') === 'asc')>Ascending</option>
                    </select>

                    <button type="submit">Apply</button>
                </form>
            </section>

            <section class="supplier-panel">
                <div class="supplier-panel-header">
                    <div>
                        <h2>All Suppliers</h2>
                        <p class="muted-copy">Supplier contact details and linked products</p>
                    </div>
                    <span class="supplier-record-count">
                        {{ $suppliers->total() }} {{ $suppliers->total() === 1 ? 'record' : 'records' }}
                    </span>
                </div>

                <div class="supplier-table-wrap">
                    <table class="supplier-table">
                        <thead>
                            <tr>
                                <th>COMPANY</th>
                                <th>CONTACT PERSON</th>
                                <th>EMAIL</th>
                                <th>LEAD TIME</th>
                                <th>PRODUCTS</th>
                                <th>STATUS</th>
                                <th>ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($suppliers as $supplier)
                                <tr>
                                    <td><strong>{{ $supplier->company_name }}</strong></td>
                                    <td>{{ $supplier->contact_person }}</td>
                                    <td>{{ $supplier->email }}</td>
                                    <td>{{ $supplier->lead_time_days }} days</td>
                                    <td>{{ $supplier->products_count }}</td>
                                    <td>
                                        <span class="supplier-status {{ $supplier->is_active ? 'active' : 'inactive' }}">
                                            {{ $supplier->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="supplier-actions">
                                            <a href="{{ route('suppliers.show', $supplier) }}">View</a>
                                            <a href="{{ route('suppliers.edit', $supplier) }}">Edit</a>
                                            <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}" onsubmit="return confirm('Move this supplier to trash?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="supplier-empty">
                                        No suppliers found. Add a supplier to get started.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($suppliers->hasPages())
                    <div class="supplier-pagination">
                        {{ $suppliers->links() }}
                    </div>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
