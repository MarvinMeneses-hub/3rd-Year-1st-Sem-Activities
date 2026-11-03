<x-app-layout>
    <div class="transactions-page">
        <div class="transactions-container">
            <header class="page-heading">
                <div>
                    <span class="page-eyebrow">INVENTORY MANAGEMENT</span>
                    <h1>Inventory Transactions</h1>
                    <p>Track stock movements, adjustments, and inventory activity.</p>
                </div>
                <div class="heading-actions">
                    <a href="{{ route('transactions.report') }}" class="secondary-btn">View Reports</a>
                    <a href="{{ route('transactions.create') }}" class="primary-btn">＋ Record Transaction</a>
                </div>
            </header>

            @if (session('success'))
                <div class="notice-success" role="status">✓ {{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="notice-error" role="alert">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="notice-error" role="alert">
                    <strong>Please correct the following:</strong>
                    <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <section class="summary-grid" id="inventory-summary">
                <article class="summary-card"><span>Total Transactions</span><strong>{{ number_format($summary['total']) }}</strong><small>Recorded stock movements</small></article>
                <article class="summary-card"><span>Units Received</span><strong class="positive">{{ number_format($summary['stock_in']) }}</strong><small>Stock-in quantity recorded</small></article>
                <article class="summary-card"><span>Low Stock Items</span><strong class="warning">{{ number_format($summary['low_stock']) }}</strong><small>At or below reorder level</small></article>
                <article class="summary-card"><span>Out of Stock</span><strong class="negative">{{ number_format($summary['out_of_stock']) }}</strong><small>Products with zero stock</small></article>
            </section>

            <section class="filter-panel">
                <div class="panel-heading"><div><h2>Filter Transactions</h2><p>Search records by product, type, and date range.</p></div></div>
                <form method="GET" action="{{ route('transactions.index') }}" class="filter-form">
                    <label>Product
                        <select name="product_id">
                            <option value="">All products</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}" @selected(request('product_id') == $product->id)>{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>Transaction Type
                        <select name="type">
                            <option value="">All types</option>
                            <option value="stock_in" @selected(request('type') === 'stock_in')>Stock In</option>
                            <option value="stock_out" @selected(request('type') === 'stock_out')>Stock Out</option>
                            <option value="adjustment" @selected(request('type') === 'adjustment')>Adjustment</option>
                        </select>
                    </label>
                    <label>Date From<input type="date" name="date_from" value="{{ request('date_from') }}"></label>
                    <label>Date To<input type="date" name="date_to" value="{{ request('date_to') }}"></label>
                    <div class="filter-actions"><button class="filter-btn" type="submit">Apply Filters</button><a class="reset-btn" href="{{ route('transactions.index') }}">Reset</a></div>
                </form>
            </section>

            <section class="table-panel">
                <div class="panel-heading">
                    <div><h2>Transaction History</h2><p>Most recent transactions appear first.</p></div>
                    <span class="record-label">{{ $transactions->total() }} records</span>
                </div>
                <div class="table-scroll">
                    <table>
                        <thead><tr><th>DATE</th><th>PRODUCT</th><th>TYPE</th><th>QUANTITY CHANGE</th><th>REFERENCE</th><th>RECORDED BY</th><th></th></tr></thead>
                        <tbody>
                        @forelse ($transactions as $transaction)
                            <tr>
                                <td data-label="Date"><strong>{{ $transaction->created_at->format('M d, Y') }}</strong><small>{{ $transaction->created_at->format('h:i A') }}</small></td>
                                <td data-label="Product"><strong>{{ $transaction->product?->name ?? 'Deleted product' }}</strong></td>
                                <td data-label="Type"><span class="type-badge {{ $transaction->type }}">{{ str_replace('_', ' ', ucfirst($transaction->type)) }}</span></td>
                                <td data-label="Quantity">
                                    @php
                                        $displayQuantity = match ($transaction->type) {
                                            'stock_in' => abs($transaction->quantity),
                                            'stock_out' => -abs($transaction->quantity),
                                            default => $transaction->quantity,
                                        };
                                    @endphp
                                    <strong class="{{ $displayQuantity >= 0 ? 'positive' : 'negative' }}">{{ $displayQuantity > 0 ? '+' : '' }}{{ number_format($displayQuantity) }}</strong> units
                                </td>
                                <td data-label="Reference">{{ $transaction->reference_number ?: '—' }}</td>
                                <td data-label="Recorded By">{{ $transaction->user?->name ?? 'Unknown user' }}</td>
                                <td data-label="Action"><a class="view-link" href="{{ route('transactions.show', $transaction) }}">Details →</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="7"><div class="empty-state"><h3>No transactions found</h3><p>Record a stock movement or change your filters to see transaction history.</p><a class="primary-btn" href="{{ route('transactions.create') }}">Record First Transaction</a></div></td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($transactions->hasPages())
                    <div class="pagination-area"><span>Showing {{ $transactions->firstItem() }}–{{ $transactions->lastItem() }} of {{ $transactions->total() }}</span>{{ $transactions->links() }}</div>
                @endif
            </section>
            <p class="last-updated">Inventory summary refreshes automatically every 30 seconds.</p>
        </div>
    </div>

    <style>
        .transactions-page{min-height:calc(100vh - 76px);padding:36px 28px 48px;background:#f5f7fb;color:#17243b;font-family:Inter,ui-sans-serif,system-ui,sans-serif}
        .transactions-container{max-width:1440px;margin:auto}.page-heading{display:flex;justify-content:space-between;align-items:center;gap:20px;margin-bottom:25px}
        .page-eyebrow{color:#3975eb;font-size:10px;font-weight:800;letter-spacing:1.8px}.page-heading h1{margin:7px 0 5px;font-size:clamp(26px,3vw,34px);font-weight:800;letter-spacing:-1px}
        .page-heading p,.panel-heading p{margin:0;color:#8290a5;font-size:12px;line-height:1.7}.heading-actions{display:flex;gap:9px;flex-wrap:wrap}
        .primary-btn,.secondary-btn,.filter-btn,.reset-btn{display:inline-flex;align-items:center;justify-content:center;min-height:41px;padding:0 15px;border:0;border-radius:9px;font-size:12px;font-weight:750;text-decoration:none;cursor:pointer}
        .primary-btn,.filter-btn{background:linear-gradient(135deg,#3979f6,#2456d9);color:#fff;box-shadow:0 5px 12px #2456d927}.secondary-btn,.reset-btn{border:1px solid #dfe7f2;background:#fff;color:#52647d}
        .notice-success,.notice-error{margin-bottom:18px;padding:13px 16px;border-radius:10px;font-size:13px}.notice-success{border:1px solid #b8ebd0;background:#edfff5;color:#147a49}.notice-error{border:1px solid #fecaca;background:#fff1f2;color:#be3445}.notice-error ul{margin:6px 0 0;padding-left:20px}
        .summary-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:15px;margin-bottom:20px}.summary-card,.filter-panel,.table-panel{border:1px solid #e5eaf2;border-radius:14px;background:#fff;box-shadow:0 4px 18px #24395b08}
        .summary-card{display:grid;gap:7px;padding:19px 21px}.summary-card span{color:#8793a7;font-size:10px;font-weight:800;letter-spacing:.7px;text-transform:uppercase}.summary-card strong{font-size:28px;color:#1d2e49}.summary-card small{color:#9aa5b5;font-size:10px}
        .positive{color:#168452!important}.negative{color:#cb4653!important}.warning{color:#bd7a17!important}
        .panel-heading{display:flex;justify-content:space-between;align-items:center;gap:15px;padding:20px 23px;border-bottom:1px solid #edf0f5}.panel-heading h2{margin:0 0 5px;color:#202e46;font-size:16px;font-weight:800}
        .filter-panel{margin-bottom:20px}.filter-form{display:grid;grid-template-columns:repeat(4,minmax(130px,1fr)) auto;gap:13px;align-items:end;padding:19px 23px}
        .filter-form label{display:grid;gap:7px;color:#53647c;font-size:11px;font-weight:700}.filter-form input,.filter-form select{width:100%;min-height:40px;padding:0 10px;border:1px solid #dfe6ef;border-radius:8px;background:#fff;color:#34445c;font-size:12px}
        .filter-actions{display:flex;gap:7px}.filter-btn,.reset-btn{min-height:40px;font-size:11px}.record-label{padding:7px 11px;border-radius:8px;background:#f5f8ff;color:#4969a4;font-size:10px;font-weight:800}
        .table-panel{overflow:hidden}.table-scroll{overflow-x:auto}table{width:100%;border-collapse:collapse;text-align:left}thead{background:#f9fafe}th{padding:14px 17px;color:#8793a7;font-size:9px;letter-spacing:.8px;white-space:nowrap}td{padding:15px 17px;border-top:1px solid #f0f3f8;color:#526178;font-size:11px;vertical-align:middle}td strong{color:#34445d}td small{display:block;margin-top:4px;color:#929daf;font-size:10px}
        .type-badge{display:inline-block;padding:6px 9px;border-radius:20px;font-size:10px;font-weight:800;text-transform:capitalize;white-space:nowrap}.type-badge.stock_in{background:#eafaf1;color:#16834a}.type-badge.stock_out{background:#fff0f0;color:#c43d45}.type-badge.adjustment{background:#fff7e8;color:#a66a0b}
        .view-link{color:#326bc2;font-size:11px;font-weight:800;text-decoration:none;white-space:nowrap}.empty-state{padding:45px 15px;text-align:center}.empty-state h3{margin:0 0 7px;color:#34445c}.empty-state p{margin:0 0 16px;color:#8793a5;font-size:12px}
        .pagination-area{display:flex;justify-content:space-between;align-items:center;gap:15px;flex-wrap:wrap;padding:17px 22px;border-top:1px solid #edf0f5;color:#8290a5;font-size:11px}.last-updated{margin-top:14px;color:#929daf;font-size:10px;text-align:right}
        @media(max-width:1050px){.filter-form{grid-template-columns:repeat(2,minmax(0,1fr))}.filter-actions{grid-column:1/-1}.summary-grid{grid-template-columns:repeat(2,1fr)}}
        @media(max-width:650px){.transactions-page{padding:24px 12px 32px}.page-heading{align-items:flex-start;flex-direction:column}.heading-actions{width:100%}.heading-actions a{flex:1}.filter-form{grid-template-columns:1fr;padding:16px}.filter-actions{grid-column:auto}.summary-grid{gap:9px}.summary-card{padding:14px}.summary-card strong{font-size:23px}.panel-heading{padding:16px}.table-panel table,.table-panel tbody,.table-panel tr,.table-panel td{display:block;width:100%}.table-panel thead{display:none}.table-panel tbody tr{padding:10px 14px;border-top:1px solid #edf0f5}.table-panel td{display:flex;justify-content:space-between;gap:14px;padding:9px 0;border:0;text-align:right}.table-panel td:before{content:attr(data-label);color:#8793a5;font-size:10px;font-weight:800;text-align:left}.table-panel td[colspan]{display:block}.table-panel td[colspan]:before{display:none}}
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const refreshSummary = async () => {
                try {
                    const response = await fetch(@json(route('api.inventory.summary')), {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    if (!response.ok) return;
                    const data = await response.json();
                    const cards = document.querySelectorAll('#inventory-summary .summary-card strong');
                    if (cards.length === 4) {
                        cards[0].textContent = Number(data.total_transactions).toLocaleString();
                        cards[1].textContent = Number(data.stock_in_units).toLocaleString();
                        cards[2].textContent = Number(data.low_stock_count).toLocaleString();
                        cards[3].textContent = Number(data.out_of_stock_count).toLocaleString();
                    }
                } catch (error) {
                    console.warn('Inventory summary refresh failed.', error);
                }
            };
            window.setInterval(refreshSummary, 30000);
        });
    </script>
</x-app-layout>
