<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Supplier Performance Report
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Export Buttons -->
            <div class="flex flex-wrap justify-end gap-3 mb-6">
                <a href="{{ route('suppliers.performance.csv') }}"
                   class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                    Export CSV
                </a>

                <a href="{{ route('suppliers.performance.pdf') }}"
                   class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                    Export PDF
                </a>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

                <div class="bg-white rounded-lg shadow p-5">
                    <p class="text-sm text-gray-500">Total Suppliers</p>
                    <p class="text-3xl font-bold text-gray-800">
                        {{ $totalSuppliers }}
                    </p>
                </div>

                <div class="bg-white rounded-lg shadow p-5">
                    <p class="text-sm text-gray-500">Total Purchase Orders</p>
                    <p class="text-3xl font-bold text-gray-800">
                        {{ $totalOrders }}
                    </p>
                </div>

                <div class="bg-white rounded-lg shadow p-5">
                    <p class="text-sm text-gray-500">Received Orders</p>
                    <p class="text-3xl font-bold text-green-600">
                        {{ $totalReceivedOrders }}
                    </p>
                </div>

                <div class="bg-white rounded-lg shadow p-5">
                    <p class="text-sm text-gray-500">Open Orders</p>
                    <p class="text-3xl font-bold text-yellow-600">
                        {{ $totalPendingOrders }}
                    </p>
                </div>

            </div>

            <!-- Supplier Performance Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">

                <div class="p-5 border-b">
                    <h3 class="text-lg font-semibold text-gray-800">
                        Supplier Performance Details
                    </h3>
                    <p class="text-sm text-gray-500 mt-1">
                        Delivery performance based on received purchase orders.
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">

                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Supplier
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">
                                    Total Orders
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">
                                    Received
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">
                                    Open Orders
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">
                                    On-Time Delivery
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">
                                    Rating
                                </th>
                            </tr>
                        </thead>

                        <tbody class="bg-white divide-y divide-gray-200">

                            @forelse ($suppliers as $supplier)
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900">
                                            {{ $supplier->company_name }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $supplier->email }}
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 text-center">
                                        {{ $supplier->purchase_orders_count }}
                                    </td>

                                    <td class="px-6 py-4 text-center text-green-600">
                                        {{ $supplier->received_orders_count }}
                                    </td>

                                    <td class="px-6 py-4 text-center text-yellow-600">
                                        {{ $supplier->pending_orders_count }}
                                    </td>

                                    <td class="px-6 py-4 text-center">
                                        @if ($supplier->on_time_percentage !== null)
                                            {{ number_format($supplier->on_time_percentage, 1) }}%
                                        @else
                                            <span class="text-gray-400">N/A</span>
                                        @endif
                                    </td>

                                    <td class="px-6 py-4 text-center">
                                        @php
                                            $ratingClass = match ($supplier->report_rating) {
                                                'Excellent' => 'bg-green-100 text-green-800',
                                                'Good' => 'bg-blue-100 text-blue-800',
                                                'Average' => 'bg-yellow-100 text-yellow-800',
                                                'Needs Improvement' => 'bg-red-100 text-red-800',
                                                default => 'bg-gray-100 text-gray-600',
                                            };
                                        @endphp

                                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $ratingClass }}">
                                            {{ $supplier->report_rating }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                        No supplier records found.
                                    </td>
                                </tr>
                            @endforelse

                        </tbody>
                    </table>
                </div>

            </div>

            <div class="mt-6">
                <a href="{{ route('suppliers.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700">
                    Back to Suppliers
                </a>
            </div>

        </div>
    </div>
</x-app-layout>
