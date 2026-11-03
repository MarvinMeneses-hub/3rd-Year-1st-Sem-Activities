<x-app-layout>
    <div class="detail-page">
        <div class="detail-container">
            <a href="{{ route('transactions.index') }}" class="back-link">← Back to Transaction History</a>
            <header><span>TRANSACTION #{{ $transaction->id }}</span><h1>Transaction Details</h1><p>Review the details of this inventory movement.</p></header>
            <section class="detail-card">
                <div class="detail-top">
                    <div><small>TRANSACTION TYPE</small><h2>{{ str_replace('_', ' ', ucfirst($transaction->type)) }}</h2></div>
                    <span class="type-badge {{ $transaction->type }}">{{ ucfirst(str_replace('_', ' ', $transaction->type)) }}</span>
                </div>
                <div class="detail-grid">
                    <div><small>PRODUCT</small><strong>{{ $transaction->product?->name ?? 'Deleted product' }}</strong></div>
                    <div><small>QUANTITY CHANGE</small>
                        @php
                            $delta = match ($transaction->type) {
                                'stock_in' => abs($transaction->quantity),
                                'stock_out' => -abs($transaction->quantity),
                                default => $transaction->quantity,
                            };
                        @endphp
                        <strong class="{{ $delta >= 0 ? 'positive' : 'negative' }}">{{ $delta > 0 ? '+' : '' }}{{ number_format($delta) }} units</strong>
                    </div>
                    <div><small>REFERENCE NUMBER</small><strong>{{ $transaction->reference_number ?: '—' }}</strong></div>
                    <div><small>RECORDED BY</small><strong>{{ $transaction->user?->name ?? 'Unknown user' }}</strong></div>
                    <div><small>DATE RECORDED</small><strong>{{ $transaction->created_at->format('F d, Y') }}</strong></div>
                    <div><small>TIME RECORDED</small><strong>{{ $transaction->created_at->format('h:i A') }}</strong></div>
                </div>
                <div class="notes"><small>NOTES</small><p>{{ $transaction->notes ?: 'No additional notes were provided.' }}</p></div>
                <div class="detail-actions"><a href="{{ route('transactions.index') }}" class="back-button">Return to History</a><a href="{{ route('transactions.report') }}" class="report-button">View Reports</a></div>
            </section>
            <p class="audit-note">Transaction records are historical audit entries and cannot be edited or deleted.</p>
        </div>
    </div>
    <style>
        .detail-page{min-height:calc(100vh - 76px);padding:35px 20px;background:#f5f7fb;color:#17243b;font-family:Inter,ui-sans-serif,system-ui,sans-serif}.detail-container{max-width:900px;margin:auto}.back-link{color:#3975eb;font-size:12px;font-weight:750;text-decoration:none}.detail-container header{margin:22px 0}.detail-container header span,.detail-grid small,.detail-top small,.notes small{color:#8793a7;font-size:10px;font-weight:800;letter-spacing:1px}.detail-container h1{margin:6px 0;font-size:30px;font-weight:800}.detail-container header p{margin:0;color:#8290a5;font-size:12px}.detail-card{padding:26px;border:1px solid #e5eaf2;border-radius:15px;background:#fff;box-shadow:0 4px 18px #24395b08}.detail-top{display:flex;justify-content:space-between;align-items:center;padding-bottom:22px;border-bottom:1px solid #edf0f5}.detail-top h2{margin:7px 0 0;font-size:20px;font-weight:800;text-transform:capitalize}.type-badge{padding:7px 11px;border-radius:20px;font-size:10px;font-weight:800;text-transform:capitalize}.type-badge.stock_in{background:#eafaf1;color:#16834a}.type-badge.stock_out{background:#fff0f0;color:#c43d45}.type-badge.adjustment{background:#fff7e8;color:#a66a0b}.detail-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:25px;padding:25px 0}.detail-grid div{display:grid;gap:8px}.detail-grid strong{color:#34445d;font-size:13px}.positive{color:#168452!important}.negative{color:#cb4653!important}.notes{padding:18px;border-radius:10px;background:#f8fafd}.notes p{margin:8px 0 0;color:#526178;font-size:12px;line-height:1.7;white-space:pre-wrap}.detail-actions{display:flex;justify-content:flex-end;gap:9px;margin-top:23px}.back-button,.report-button{display:inline-flex;align-items:center;min-height:40px;padding:0 14px;border-radius:9px;font-size:11px;font-weight:750;text-decoration:none}.back-button{border:1px solid #dfe6ef;color:#52647d}.report-button{background:linear-gradient(135deg,#3979f6,#2456d9);color:#fff}.audit-note{margin-top:14px;color:#929daf;font-size:10px;text-align:center}@media(max-width:600px){.detail-page{padding:24px 13px}.detail-card{padding:18px}.detail-grid{grid-template-columns:1fr;gap:18px}.detail-actions{flex-direction:column}.detail-actions a{justify-content:center}}
    </style>
</x-app-layout>
