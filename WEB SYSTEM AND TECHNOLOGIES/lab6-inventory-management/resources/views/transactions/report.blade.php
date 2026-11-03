<x-app-layout>
    <div class="report-page">
        <div class="report-container">
            <a href="{{ route('transactions.index') }}" class="back-link">← Back to Transactions</a>
            <header><span>INVENTORY ANALYTICS</span><h1>Transaction Reports</h1><p>Review stock movement totals for your selected reporting period.</p></header>
            <section class="filter-card">
                <form method="GET" action="{{ route('transactions.report') }}">
                    <label>From <input type="date" name="date_from" value="{{ request('date_from') }}"></label>
                    <label>To <input type="date" name="date_to" value="{{ request('date_to') }}"></label>
                    <button type="submit">Generate Report</button>
                    <a href="{{ route('transactions.report') }}">Clear</a>
                </form>
            </section>
            <section class="report-grid">
                <article><small>TRANSACTIONS</small><strong>{{ number_format($summary['transactions']) }}</strong></article>
                <article><small>STOCK IN</small><strong class="positive">{{ number_format($summary['stock_in']) }}</strong></article>
                <article><small>STOCK OUT</small><strong class="negative">{{ number_format($summary['stock_out']) }}</strong></article>
                <article><small>NET ADJUSTMENTS</small><strong>{{ number_format($summary['adjustments']) }}</strong></article>
            </section>
            <section class="report-table">
                <div class="table-heading"><h2>Included Transactions</h2><p>Use the date filters to narrow this report.</p></div>
                <div class="table-scroll"><table><thead><tr><th>DATE</th><th>PRODUCT</th><th>TYPE</th><th>QUANTITY</th><th>USER</th></tr></thead><tbody>
                    @forelse ($transactions as $transaction)
                        @php
                            $delta = match ($transaction->type) {
                                'stock_in' => abs($transaction->quantity),
                                'stock_out' => -abs($transaction->quantity),
                                default => $transaction->quantity,
                            };
                        @endphp
                        <tr><td>{{ $transaction->created_at->format('M d, Y h:i A') }}</td><td>{{ $transaction->product?->name ?? 'Deleted product' }}</td><td>{{ ucfirst(str_replace('_', ' ', $transaction->type)) }}</td><td class="{{ $delta >= 0 ? 'positive' : 'negative' }}">{{ $delta > 0 ? '+' : '' }}{{ number_format($delta) }}</td><td>{{ $transaction->user?->name ?? 'Unknown' }}</td></tr>
                    @empty
                        <tr><td colspan="5" class="empty">No transactions found for this period.</td></tr>
                    @endforelse
                </tbody></table></div>
                @if ($transactions->hasPages())<div class="pagination">{{ $transactions->links() }}</div>@endif
            </section>
        </div>
    </div>
    <style>
        .report-page{min-height:calc(100vh - 76px);padding:35px 22px;background:#f5f7fb;color:#17243b;font-family:Inter,ui-sans-serif,system-ui,sans-serif}.report-container{max-width:1200px;margin:auto}.back-link{color:#3975eb;font-size:12px;font-weight:750;text-decoration:none}.report-container header{margin:22px 0}.report-container header span{color:#3975eb;font-size:10px;font-weight:800;letter-spacing:1.5px}.report-container h1{margin:7px 0;font-size:30px;font-weight:800}.report-container header p,.table-heading p{margin:0;color:#8290a5;font-size:12px}.filter-card,.report-table{margin-bottom:18px;border:1px solid #e5eaf2;border-radius:13px;background:#fff;overflow:hidden}.filter-card form{display:flex;align-items:end;gap:12px;flex-wrap:wrap;padding:18px}.filter-card label{display:grid;gap:6px;color:#53647c;font-size:11px;font-weight:700}.filter-card input{min-height:38px;padding:0 10px;border:1px solid #dfe6ef;border-radius:8px}.filter-card button{min-height:38px;padding:0 14px;border:0;border-radius:8px;background:#326bdc;color:#fff;font-size:11px;font-weight:800}.filter-card a{color:#66758b;font-size:11px;text-decoration:none;padding:10px}.report-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:13px;margin-bottom:18px}.report-grid article{display:grid;gap:8px;padding:19px;border:1px solid #e5eaf2;border-radius:12px;background:#fff}.report-grid small{color:#8793a7;font-size:9px;font-weight:800;letter-spacing:.8px}.report-grid strong{font-size:26px}.positive{color:#168452}.negative{color:#cb4653}.table-heading{padding:19px 21px;border-bottom:1px solid #edf0f5}.table-heading h2{margin:0 0 5px;font-size:15px;font-weight:800}.table-scroll{overflow-x:auto}table{width:100%;border-collapse:collapse;text-align:left}th{padding:13px 17px;background:#f9fafe;color:#8793a7;font-size:9px;letter-spacing:.8px}td{padding:14px 17px;border-top:1px solid #f0f3f8;color:#526178;font-size:11px}.empty{text-align:center;padding:35px}.pagination{padding:15px;border-top:1px solid #edf0f5}@media(max-width:700px){.report-grid{grid-template-columns:repeat(2,1fr)}.report-page{padding:24px 12px}.filter-card form{align-items:stretch;flex-direction:column}.filter-card label input{width:100%}}
    </style>
</x-app-layout>
