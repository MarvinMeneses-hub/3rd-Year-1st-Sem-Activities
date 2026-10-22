
<x-app-layout>
    <div class="min-h-screen bg-slate-50 px-4 py-8 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl space-y-7">

            <section class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-[0.18em] text-blue-600">
                        <span class="h-2 w-2 rounded-full bg-blue-600"></span>
                        Inventory workspace
                    </div>
                    <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-slate-900">Products</h1>
                    <p class="mt-2 text-sm leading-6 text-slate-500">Manage your items, review quantities, and keep inventory up to date.</p>
                </div>

                <a href="{{ route('products.create') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-blue-600/20 transition hover:-translate-y-0.5 hover:bg-blue-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m7-7H5"/>
                    </svg>
                    Add New Product
                </a>
            </section>

            @if (session('success'))
                <div class="flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800" role="status">
                    <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m5 12 4 4L19 6"/>
                    </svg>
                    <p class="font-medium">{{ session('success') }}</p>
                </div>
            @endif

            @if (session('error'))
                <div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm font-medium text-red-700" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 p-5 sm:p-6">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <h2 class="text-lg font-extrabold text-slate-900">Product inventory</h2>
                            <p class="mt-1 text-sm text-slate-500">Search and manage your existing products.</p>
                        </div>

                        <form method="GET" action="{{ route('products.index') }}" class="flex w-full flex-col gap-2 sm:flex-row lg:w-auto">
                            <label for="product-search" class="sr-only">Search products</label>
                            <div class="relative flex-1 sm:min-w-[260px]">
                                <svg class="pointer-events-none absolute left-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <circle cx="11" cy="11" r="7" stroke-width="1.8"/>
                                    <path stroke-linecap="round" stroke-width="1.8" d="m16 16 4 4"/>
                                </svg>
                                <input id="product-search" type="text" name="search" value="{{ request('search') }}"
                                       placeholder="Search products..."
                                       class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-11 pr-4 text-sm text-slate-800 placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-blue-500/10">
                            </div>
                            <button type="submit" class="rounded-xl bg-slate-900 px-5 py-3 text-sm font-bold text-white transition hover:bg-slate-700">
                                Search
                            </button>
                            @if(request('search'))
                                <a href="{{ route('products.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                                    Clear
                                </a>
                            @endif
                        </form>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[760px] text-left">
                        <thead class="bg-slate-50">
                            <tr class="border-b border-slate-100">
                                <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Product</th>
                                <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Description</th>
                                <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Unit price</th>
                                <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Stock status</th>
                                <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider text-slate-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($products as $product)
                                <tr class="transition hover:bg-blue-50/30">
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-700">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7 12 3 4 7m16 0-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="font-bold text-slate-900">{{ $product->name }}</p>
                                                <p class="mt-1 text-xs text-slate-400">Product ID: {{ $product->id }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="max-w-xs px-6 py-5 text-sm leading-5 text-slate-500">
                                        {{ $product->description ?: 'No description provided' }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-5 text-sm font-bold text-slate-800">
                                        ${{ number_format($product->price, 2) }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-5">
                                        @if($product->quantity <= 0)
                                            <span class="inline-flex items-center gap-2 rounded-full bg-red-50 px-3 py-1.5 text-xs font-bold text-red-700">
                                                <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                                Out of stock
                                            </span>
                                        @elseif($product->quantity <= 5)
                                            <span class="inline-flex items-center gap-2 rounded-full bg-amber-50 px-3 py-1.5 text-xs font-bold text-amber-700">
                                                <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                                {{ $product->quantity }} left
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700">
                                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                                {{ $product->quantity }} in stock
                                            </span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-5">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('products.edit', $product) }}"
                                               class="rounded-lg border border-blue-100 bg-blue-50 px-3 py-2 text-xs font-bold text-blue-700 transition hover:border-blue-200 hover:bg-blue-100">
                                                Edit
                                            </a>
                                            <form action="{{ route('products.destroy', $product) }}" method="POST"
                                                  onsubmit="return confirm('Are you sure you want to delete this product?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-lg border border-red-100 bg-red-50 px-3 py-2 text-xs font-bold text-red-700 transition hover:border-red-200 hover:bg-red-100">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-16 text-center">
                                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                                            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7 12 3 4 7m16 0-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                            </svg>
                                        </div>
                                        <h3 class="mt-4 font-bold text-slate-900">No products found</h3>
                                        <p class="mt-1 text-sm text-slate-500">Try a different search or add a new product.</p>
                                        @if(request('search'))
                                            <a href="{{ route('products.index') }}" class="mt-4 inline-flex text-sm font-bold text-blue-600 hover:text-blue-800">Clear search</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-slate-100 px-5 py-4 sm:px-6">
                    {{ $products->links() }}
                </div>
            </section>

            <p class="text-center text-xs text-slate-400">Keep your inventory accurate by reviewing product quantities regularly.</p>
        </div>
    </div>
</x-app-layout>
