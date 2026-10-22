
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Account Management | Banking System</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Segoe UI", Arial, sans-serif;
            background: #f4f7fb;
            color: #172033;
        }
        header {
            background: #142b4a;
            color: white;
            padding: 22px 5%;
        }
        header h1 { margin: 0; font-size: 1.5rem; }
        header p { color: #cbd8e8; margin-bottom: 0; }
        main { width: min(1100px, 92%); margin: 32px auto; }
        .top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 24px;
        }
        h2 { margin: 0 0 6px; }
        .muted { color: #65728a; }
        .button {
            display: inline-block;
            padding: 11px 16px;
            background: #1d4ed8;
            color: white;
            text-decoration: none;
            border-radius: 9px;
            font-weight: 600;
        }
        .button.secondary { background: #e6edf7; color: #142b4a; }
        .panel {
            background: white;
            border: 1px solid #e4eaf2;
            border-radius: 14px;
            padding: 22px;
            box-shadow: 0 8px 24px rgba(20,43,74,.05);
        }
        .success {
            background: #dcfce7;
            color: #166534;
            padding: 12px 14px;
            border-radius: 9px;
            margin-bottom: 18px;
        }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td {
            padding: 14px 12px;
            text-align: left;
            border-bottom: 1px solid #e8edf4;
            white-space: nowrap;
        }
        th { color: #65728a; font-size: .85rem; }
        .balance { font-weight: 700; }
        .empty { text-align: center; padding: 35px 12px; color: #65728a; }
        .pagination { margin-top: 20px; }
        .pagination nav > div { display: flex; gap: 8px; flex-wrap: wrap; }
        .pagination a, .pagination span {
            padding: 8px 12px;
            border: 1px solid #dbe3ee;
            border-radius: 7px;
            text-decoration: none;
            color: #142b4a;
        }
        @media (max-width: 600px) {
            main { margin-top: 22px; }
            .panel { padding: 12px; }
            th, td { padding: 11px 9px; }
        }
    </style>
</head>
<body>
<header>
    <h1>Banking System</h1>
    <p>Account Management</p>
</header>

<main>
    <div class="top">
        <div>
            <h2>Bank Accounts</h2>
            <div class="muted">View account holders and their balances.</div>
        </div>
        <div>
            <a class="button secondary" href="{{ route('dashboard') }}">Dashboard</a>
            <a class="button" href="{{ route('accounts.create') }}">+ New Account</a>
        </div>
    </div>

    @if (session('success'))
        <div class="success">{{ session('success') }}</div>
    @endif

    <section class="panel">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Account Number</th>
                        <th>Account Holder</th>
                        <th>Email</th>
                        <th>Balance</th>
                        <th>Minimum Balance</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($accounts as $account)
                        <tr>
                            <td>{{ $account->account_number }}</td>
                            <td>{{ $account->user->name ?? 'Unknown' }}</td>
                            <td>{{ $account->user->email ?? '—' }}</td>
                            <td class="balance">
                                ₱{{ number_format((float) $account->balance, 2) }}
                            </td>
                            <td>
                                ₱{{ number_format((float) $account->minimum_balance, 2) }}
                            </td>
                            <td>{{ $account->created_at?->format('M d, Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty">
                                No accounts yet. Create your first account to get started.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination">{{ $accounts->links() }}</div>
    </section>
</main>
</body>
</html>
