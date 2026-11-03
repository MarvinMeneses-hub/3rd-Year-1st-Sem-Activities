
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-600">
                    INVENTORY MANAGEMENT SYSTEM
                </p>
                <h2 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-900">
                    Dashboard Overview
                </h2>
                <p class="mt-1 text-sm text-slate-500">
                    Manage your products, monitor stock, and track inventory value.
                </p>
            </div>

            <a href="{{ route('products.create') }}"
               class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-blue-600/20 transition hover:-translate-y-0.5 hover:bg-blue-700">
                <span class="text-lg">+</span>
                Add New Product
            </a>
        </div>
    </x-slot>

    <div class="min-h-screen bg-slate-50 py-8">
        <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">

            {{-- Welcome banner --}}
            <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-950 via-slate-900 to-blue-950 p-7 shadow-xl sm:p-10">
                <div class="absolute -right-16 -top-20 h-64 w-64 rounded-full bg-blue-500/20 blur-3xl"></div>
                <div class="absolute -bottom-24 right-1/3 h-48 w-48 rounded-full bg-indigo-500/20 blur-3xl"></div>

                <div class="relative flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
                    <div class="max-w-2xl">
                        <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-xs font-bold tracking-wider text-blue-200">
                            <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                            YOUR INVENTORY AT A GLANCE
                        </span>

                        <h3 class="mt-5 text-3xl font-extrabold tracking-tight text-white sm:text-4xl">
                            Welcome back, {{ Auth::user()->name }}!
                        </h3>

                        <p class="mt-3 max-w-xl text-sm leading-6 text-slate-300 sm:text-base">
                            Here's your inventory overview. Keep track of available products,
                            watch stock levels, and maintain an organized inventory.
                        </p>

                        <div class="mt-6 flex flex-wrap gap-3">
                            <a href="{{ route('products.index') }}"
                               class="rounded-xl bg-white px-5 py-3 text-sm font-bold text-slate-900 transition hover:bg-blue-50">
                                View Inventory →
                            </a>

                            <a href="{{ route('products.create') }}"
                               class="rounded-xl border border-white/20 bg-white/10 px-5 py-3 text-sm font-bold text-white transition hover:bg-white/20">
                                + Create Product
                            </a>
                        </div>
                    </div>

                    <div class="hidden h-32 w-32 shrink-0 items-center justify-center rounded-3xl border border-white/15 bg-white/10 text-6xl shadow-inner sm:flex">
                        📦
                    </div>
                </div>
            </section>

            {{-- Summary heading --}}
            <section>
                <div class="mb-5">
                    <h3 class="text-xl font-extrabold text-slate-900">
                        Inventory Summary
                    </h3>
                    <p class="mt-1 text-sm text-slate-500">
                        A quick overview of your current stock.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">

                    {{-- Total products --}}
                    <div class="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-semibold text-slate-500">Total Products</p>
                                <p class="mt-3 text-4xl font-extrabold tracking-tight text-slate-900">
                                    {{ number_format($totalProducts) }}
                                </p>
                                <p class="mt-2 text-xs text-slate-500">Registered product types</p>
                            </div>
                            <div class="rounded-2xl bg-blue-50 p-3 text-2xl transition group-hover:bg-blue-600">
                                📦
                            </div>
                        </div>
                        <div class="mt-5 h-1.5 overflow-hidden rounded-full bg-blue-50">
                            <div class="h-full w-full rounded-full bg-blue-500"></div>
                        </div>
                    </div>

                    {{-- Total stock --}}
                    <div class="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-semibold text-slate-500">Total Stock</p>
                                <p class="mt-3 text-4xl font-extrabold tracking-tight text-indigo-600">
                                    {{ number_format($totalQuantity) }}
                                </p>
                                <p class="mt-2 text-xs text-slate-500">Combined units available</p>
                            </div>
                            <div class="rounded-2xl bg-indigo-50 p-3 text-2xl">
                                📊
                            </div>
                        </div>
                        <div class="mt-5 h-1.5 overflow-hidden rounded-full bg-indigo-50">
                            <div class="h-full w-full rounded-full bg-indigo-500"></div>
                        </div>
                    </div>

                    {{-- Inventory value --}}
                    <div class="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-slate-500">Inventory Value</p>
                                <p class="mt-3 break-words text-2xl font-extrabold tracking-tight text-emerald-600 sm:text-3xl">
                                    ₱{{ number_format((float) $totalValue, 2) }}
                                </p>
                                <p class="mt-2 text-xs text-slate-500">Price × quantity</p>
                            </div>
                            <div class="rounded-2xl bg-emerald-50 p-3 text-2xl">
                                💰
                            </div>
                        </div>
                        <div class="mt-5 h-1.5 overflow-hidden rounded-full bg-emerald-50">
                            <div class="h-full w-full rounded-full bg-emerald-500"></div>
                        </div>
                    </div>

                    {{-- Out of stock --}}
                    <div class="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-semibold text-slate-500">Out of Stock</p>
                                <p class="mt-3 text-4xl font-extrabold tracking-tight text-rose-600">
                                    {{ number_format($outOfStockCount) }}
                                </p>
                                <p class="mt-2 text-xs text-slate-500">Products needing restock</p>
                            </div>
                            <div class="rounded-2xl bg-rose-50 p-3 text-2xl">
                                ⚠️
                            </div>
                        </div>
                        <div class="mt-5 h-1.5 overflow-hidden rounded-full bg-rose-50">
                            <div class="h-full w-full rounded-full bg-rose-500"></div>
                        </div>
                    </div>

                </div>
            </section>

            {{-- Stock alerts --}}
            <section class="grid grid-cols-1 gap-6 xl:grid-cols-2">

                {{-- Low stock --}}
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-6 py-5">
                        <div class="flex items-center gap-3">
                            <div class="rounded-xl bg-amber-50 p-3 text-xl">🟠</div>
                            <div>
                                <h3 class="font-extrabold text-slate-900">Low Stock Alerts</h3>
                                <p class="mt-1 text-xs text-slate-500">Products with 1–5 units remaining</p>
                            </div>
                        </div>
                        <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700">
                            {{ $lowStockProducts->count() }} items
                        </span>
                    </div>

                    <div class="p-6">
                        @forelse($lowStockProducts as $item)
                            <div class="flex items-center justify-between gap-4 border-b border-slate-100 py-4 first:pt-0 last:border-0 last:pb-0">
                                <div class="min-w-0">
                                    <p class="truncate font-bold text-slate-800">{{ $item->name }}</p>
                                    <p class="mt-1 text-xs text-slate-500">Restock recommended</p>
                                </div>
                                <span class="shrink-0 rounded-full bg-amber-50 px-3 py-1.5 text-xs font-extrabold text-amber-700">
                                    {{ $item->quantity }} left
                                </span>
                            </div>
                        @empty
                            <div class="py-8 text-center">
                                <div class="text-4xl">✅</div>
                                <p class="mt-3 font-bold text-slate-800">No low-stock products</p>
                                <p class="mt-1 text-sm text-slate-500">Products with 1–5 units will appear here.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Out of stock list --}}
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-6 py-5">
                        <div class="flex items-center gap-3">
                            <div class="rounded-xl bg-rose-50 p-3 text-xl">🔴</div>
                            <div>
                                <h3 class="font-extrabold text-slate-900">Out-of-Stock Products</h3>
                                <p class="mt-1 text-xs text-slate-500">Items with zero or fewer units</p>
                            </div>
                        </div>
                        <span class="rounded-full bg-rose-50 px-3 py-1 text-xs font-bold text-rose-700">
                            {{ $outOfStockCount }} items
                        </span>
                    </div>

                    <div class="p-6">
                        @forelse($outOfStockProducts->take(5) as $item)
                            <div class="flex items-center justify-between gap-4 border-b border-slate-100 py-4 first:pt-0 last:border-0 last:pb-0">
                                <div class="min-w-0">
                                    <p class="truncate font-bold text-slate-800">{{ $item->name }}</p>
                                    <p class="mt-1 text-xs text-slate-500">Immediate restocking may be needed</p>
                                </div>
                                <span class="shrink-0 rounded-full bg-rose-50 px-3 py-1.5 text-xs font-extrabold text-rose-700">
                                    {{ $item->quantity }} left
                                </span>
                            </div>
                        @empty
                            <div class="py-8 text-center">
                                <div class="text-4xl">🎉</div>
                                <p class="mt-3 font-bold text-slate-800">No out-of-stock products</p>
                                <p class="mt-1 text-sm text-slate-500">All listed products have available stock.</p>
                            </div>
                        @endforelse

                        @if($outOfStockCount > 5)
                            <p class="mt-4 text-center text-xs text-slate-500">
                                Showing 5 of {{ $outOfStockCount }} out-of-stock products.
                            </p>
                        @endif
                    </div>
                </div>

            </section>

            {{-- Quick actions --}}
            <section>
                <div class="mb-5">
                    <h3 class="text-xl font-extrabold text-slate-900">Quick Actions</h3>
                    <p class="mt-1 text-sm text-slate-500">Access your inventory tools quickly.</p>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">

                    <a href="{{ route('products.index') }}"
                       class="group flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:border-blue-300 hover:shadow-lg">
                        <div class="rounded-xl bg-blue-50 p-4 text-2xl transition group-hover:bg-blue-600">
                            📋
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="font-bold text-slate-900">View Products</p>
                            <p class="mt-1 text-sm text-slate-500">Search and manage inventory</p>
                        </div>
                        <span class="text-xl text-slate-400 transition group-hover:translate-x-1 group-hover:text-blue-600">→</span>
                    </a>

                    <a href="{{ route('products.create') }}"
                       class="group flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:border-emerald-300 hover:shadow-lg">
                        <div class="rounded-xl bg-emerald-50 p-4 text-2xl transition group-hover:bg-emerald-100">
                            ➕
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="font-bold text-slate-900">Add a Product</p>
                            <p class="mt-1 text-sm text-slate-500">Register a new inventory item</p>
                        </div>
                        <span class="text-xl text-slate-400 transition group-hover:translate-x-1 group-hover:text-emerald-600">→</span>
                    </a>

                    <a href="{{ route('profile.edit') }}"
                       class="group flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:border-indigo-300 hover:shadow-lg">
                        <div class="rounded-xl bg-indigo-50 p-4 text-2xl transition group-hover:bg-indigo-100">
                            👤
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="font-bold text-slate-900">My Profile</p>
                            <p class="mt-1 text-sm text-slate-500">Manage your account settings</p>
                        </div>
                        <span class="text-xl text-slate-400 transition group-hover:translate-x-1 group-hover:text-indigo-600">→</span>
                    </a>

                </div>
            </section>

            <footer class="pb-2 text-center text-xs text-slate-400">
                Inventory Management System · Organized stock, smarter decisions.
            </footer>

        </div>
    </div>
</x-app-layout>
