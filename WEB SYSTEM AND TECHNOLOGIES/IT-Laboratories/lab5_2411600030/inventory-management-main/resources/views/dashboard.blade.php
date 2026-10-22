
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-600">
                    Inventory Management
                </p>
                <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">
                    Dashboard Overview
                </h2>
                <p class="mt-1 text-sm text-slate-500">
                    Monitor your stock, products, and inventory value.
                </p>
            </div>

            <a href="{{ route('products.index') }}"
               class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 4v16m8-8H4"/>
                </svg>
                Manage Products
            </a>
        </div>
    </x-slot>

    <div class="min-h-screen bg-slate-50 py-8 sm:py-10">
        <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">

            {{-- Welcome banner --}}
            <section class="relative overflow-hidden rounded-3xl bg-slate-900 px-6 py-8 shadow-xl sm:px-9 sm:py-10">
                <div class="absolute -right-12 -top-20 h-64 w-64 rounded-full bg-blue-500 opacity-20 blur-3xl"></div>
                <div class="absolute -bottom-24 right-1/3 h-48 w-48 rounded-full bg-indigo-500 opacity-20 blur-3xl"></div>

                <div class="relative flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-xs font-semibold text-blue-200">
                            <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                            INVENTORY AT A GLANCE
                        </span>

                        <h3 class="mt-5 text-2xl font-bold tracking-tight text-white sm:text-3xl">
                            Welcome back, {{ Auth::user()->name }}!
                        </h3>

                        <p class="mt-3 max-w-xl text-sm leading-6 text-slate-300 sm:text-base">
                            Here's your inventory snapshot. Review your stock levels and keep your products organized.
                        </p>
                    </div>

                    <div class="hidden shrink-0 rounded-2xl border border-white/10 bg-white/10 p-5 sm:block">
                        <svg class="h-14 w-14 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.4"
                                  d="M3 7.5 12 3l9 4.5v9L12 21l-9-4.5v-9Z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.4"
                                  d="M3.3 7.7 12 12l8.7-4.3M12 21v-9M7.5 5.2l9 4.6"/>
                        </svg>
                    </div>
                </div>
            </section>

            {{-- Summary cards --}}
            <section>
                <div class="mb-4">
                    <h3 class="text-lg font-bold text-slate-900">Inventory Summary</h3>
                    <p class="mt-1 text-sm text-slate-500">Key figures from your current inventory.</p>
                </div>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">

                    {{-- Total products --}}
                    <div class="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition duration-200 hover:-translate-y-1 hover:shadow-lg">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-medium text-slate-500">Total Products</p>
                                <p class="mt-3 text-4xl font-bold tracking-tight text-slate-900">
                                    {{ $totalProducts }}
                                </p>
                                <p class="mt-2 text-xs text-slate-500">Products in your inventory</p>
                            </div>
                            <div class="rounded-2xl bg-blue-50 p-3 text-blue-600 transition group-hover:bg-blue-600 group-hover:text-white">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                          d="M20 7 12 3 4 7m16 0-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    {{-- Total stock --}}
                    <div class="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition duration-200 hover:-translate-y-1 hover:shadow-lg">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-medium text-slate-500">Total Stock Quantity</p>
                                <p class="mt-3 text-4xl font-bold tracking-tight text-indigo-600">
                                    {{ $totalQuantity }}
                                </p>
                                <p class="mt-2 text-xs text-slate-500">Combined units in stock</p>
                            </div>
                            <div class="rounded-2xl bg-indigo-50 p-3 text-indigo-600 transition group-hover:bg-indigo-600 group-hover:text-white">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                          d="M4 19V5m0 14h16M8 15v-4m4 4V7m4 8v-6"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    {{-- Inventory value --}}
                    <div class="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition duration-200 hover:-translate-y-1 hover:shadow-lg sm:col-span-2 xl:col-span-1">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-medium text-slate-500">Total Inventory Value</p>
                                <p class="mt-3 break-words text-3xl font-bold tracking-tight text-emerald-600">
                                    ${{ number_format($totalValue, 2) }}
                                </p>
                                <p class="mt-2 text-xs text-slate-500">Based on your inventory records</p>
                            </div>
                            <div class="rounded-2xl bg-emerald-50 p-3 text-emerald-600 transition group-hover:bg-emerald-600 group-hover:text-white">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                          d="M12 8c-2.2 0-4 .9-4 2s1.8 2 4 2 4 .9 4 2-1.8 2-4 2m0-10v12m7-6a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                </div>
            </section>

            {{-- Low stock section --}}
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-col gap-4 border-b border-slate-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-3">
                        <div class="rounded-xl bg-amber-50 p-2.5 text-amber-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                      d="M12 9v4m0 4h.01M10.3 4.8 2.9 18a1.5 1.5 0 0 0 1.3 2.2h15.6a1.5 1.5 0 0 0 1.3-2.2L13.7 4.8a2 2 0 0 0-3.4 0Z"/>
                            </svg>
                        </div>

                        <div>
                            <h3 class="font-bold text-slate-900">Low Stock Alerts</h3>
                            <p class="mt-1 text-sm text-slate-500">Products with five units or fewer</p>
                        </div>
                    </div>

                    <a href="{{ route('products.index') }}"
                       class="inline-flex items-center gap-2 text-sm font-semibold text-blue-600 transition hover:text-blue-800">
                        View all products
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M5 12h14m-7-7 7 7-7 7"/>
                        </svg>
                    </a>
                </div>

                <div class="p-6">
                    @forelse($lowStockProducts as $item)
                        <div class="flex flex-col gap-3 border-b border-slate-100 py-4 first:pt-0 last:border-0 last:pb-0 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex items-center gap-3">
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-600">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                              d="M20 7 12 3 4 7m16 0-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                </div>

                                <div>
                                    <p class="font-semibold text-slate-800">{{ $item->name }}</p>
                                    <p class="mt-1 text-xs text-slate-500">Stock level needs attention</p>
                                </div>
                            </div>

                            <span class="inline-flex w-fit items-center gap-2 rounded-full bg-amber-50 px-3 py-1.5 text-xs font-bold text-amber-700">
                                <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                                {{ $item->quantity }} left
                            </span>
                        </div>
                    @empty
                        <div class="py-8 text-center">
                            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
                                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="m5 12 4 4L19 6"/>
                                </svg>
                            </div>
                            <h4 class="mt-4 font-semibold text-slate-900">Inventory looks good!</h4>
                            <p class="mt-1 text-sm text-slate-500">
                                All products have sufficient stock levels.
                            </p>
                        </div>
                    @endforelse
                </div>
            </section>

            {{-- Quick actions --}}
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-7">
                <h3 class="text-lg font-bold text-slate-900">Quick Actions</h3>
                <p class="mt-1 text-sm text-slate-500">Get to the tools you use most.</p>

                <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <a href="{{ route('products.index') }}"
                       class="flex items-center justify-between rounded-xl border border-slate-200 p-4 transition hover:border-blue-300 hover:bg-blue-50/50">
                        <div class="flex items-center gap-3">
                            <div class="rounded-xl bg-blue-50 p-3 text-blue-600">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                          d="M4 7h16M4 12h16M4 17h16"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold text-slate-900">View Products</p>
                                <p class="mt-1 text-xs text-slate-500">Review your inventory list</p>
                            </div>
                        </div>
                        <span class="text-slate-400">→</span>
                    </a>

                    <a href="{{ route('products.create') }}"
                       class="flex items-center justify-between rounded-xl border border-slate-200 p-4 transition hover:border-blue-300 hover:bg-blue-50/50">
                        <div class="flex items-center gap-3">
                            <div class="rounded-xl bg-emerald-50 p-3 text-emerald-600">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                          d="M12 4v16m8-8H4"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold text-slate-900">Add a Product</p>
                                <p class="mt-1 text-xs text-slate-500">Register a new inventory item</p>
                            </div>
                        </div>
                        <span class="text-slate-400">→</span>
                    </a>
                </div>
            </section>

            <p class="pb-2 text-center text-xs text-slate-400">
                Inventory Management System · Your inventory, organized.
            </p>

        </div>
    </div>
</x-app-layout>
