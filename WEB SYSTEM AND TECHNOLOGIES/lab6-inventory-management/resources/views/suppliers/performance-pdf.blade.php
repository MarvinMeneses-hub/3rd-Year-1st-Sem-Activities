<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Supplier Performance Report</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #333;
        }

        h1 {
            text-align: center;
            margin-bottom: 5px;
        }

        .generated {
            text-align: center;
            color: #666;
            margin-bottom: 20px;
        }

        .summary {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }

        .summary td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }

        .summary strong {
            display: block;
            font-size: 16px;
            margin-top: 5px;
        }

        table.report {
            width: 100%;
            border-collapse: collapse;
        }

        table.report th,
        table.report td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        table.report th {
            background-color: #eeeeee;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            color: #777;
        }
    </style>
</head>
<body>

    <h1>Supplier Performance Report</h1>

    <p class="generated">
        Generated: {{ now()->format('F d, Y h:i A') }}
    </p>

    <table class="summary">
        <tr>
            <td>
                Total Suppliers
                <strong>{{ $totalSuppliers }}</strong>
            </td>
            <td>
                Total Purchase Orders
                <strong>{{ $totalOrders }}</strong>
            </td>
            <td>
                Received Orders
                <strong>{{ $totalReceivedOrders }}</strong>
            </td>
            <td>
                Open Orders
                <strong>{{ $totalPendingOrders }}</strong>
            </td>
        </tr>
    </table>

    <table class="report">
        <thead>
            <tr>
                <th>Supplier</th>
                <th>Email</th>
                <th>Total Orders</th>
                <th>Received</th>
                <th>Open Orders</th>
                <th>On-Time Deliveries</th>
                <th>On-Time %</th>
                <th>Rating</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($suppliers as $supplier)
                <tr>
                    <td>{{ $supplier->company_name }}</td>
                    <td>{{ $supplier->email }}</td>
                    <td>{{ $supplier->purchase_orders_count }}</td>
                    <td>{{ $supplier->received_orders_count }}</td>
                    <td>{{ $supplier->pending_orders_count }}</td>
                    <td>{{ $supplier->on_time_orders_count }}</td>
                    <td>
                        {{ $supplier->on_time_percentage !== null
                            ? number_format($supplier->on_time_percentage, 1) . '%'
                            : 'N/A' }}
                    </td>
                    <td>{{ $supplier->report_rating }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">No supplier records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <p class="footer">
        Inventory Management System - Supplier Performance Report
    </p>

</body>
</html>
