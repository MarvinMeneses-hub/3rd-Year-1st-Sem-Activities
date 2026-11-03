<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Product Details') }}
            </h2>
            <a href="{{ route('products.index') }}"
               class="inline-flex items-center justify-center px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                Back to Products
            </a>
        </div>
    </x-slot>

    @php
        $unitPrice = $product->unit_price ?? $product->price ?? 0;
        $stock = (int) ($product->quantity ?? 0);
        $reorderLevel = (int) ($product->reorder_level ?? 10);
        $stockValue = $stock * (float) $unitPrice;
    @endphp

    <div class="py-8 sm:py-12">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="rounded-lg bg-green-100 px-4 py-3 text-green-800 dark:bg-green-900/40 dark:text-green-200">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-xl p-6 sm:p-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Product</p>
                        <h1 class="mt-1 text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">
                            {{ $product->name }}
                        </h1>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            SKU: {{ $product->sku ?: 'Not assigned' }}
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('products.edit', $product) }}"
                           class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                            Edit Product
                        </a>
                        <form action="{{ route('products.destroy', $product) }}" method="POST"
                              onsubmit="return confirm('Are you sure you want to delete this product? This action cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="inline-flex items-center justify-center rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Unit Price</p>
                        <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
                            ${{ number_format((float) $unitPrice, 2) }}
                        </p>
                    </div>
                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Quantity in Stock</p>
                        <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stock) }}</p>
                    </div>
                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Reorder Level</p>
                        <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($reorderLevel) }}</p>
                    </div>
                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Stock Value</p>
                        <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
                            ${{ number_format($stockValue, 2) }}
                        </p>
                    </div>
                </div>

                <div class="mt-6">
                    <span class="inline-flex rounded-full px-3 py-1 text-sm font-semibold
                        {{ $stock <= 0
                            ? 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200'
                            : ($stock <= $reorderLevel
                                ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200'
                                : 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200') }}">
                        {{ $stock <= 0 ? 'Out of Stock' : ($stock <= $reorderLevel ? 'Low Stock' : 'In Stock') }}
                    </span>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-xl p-6 sm:p-8">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Product Information</h3>
                <dl class="mt-5 grid grid-cols-1 gap-x-8 gap-y-5 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Category</dt>
                        <dd class="mt-1 text-gray-900 dark:text-gray-100">{{ $product->category ?: 'Not specified' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Supplier</dt>
                        <dd class="mt-1 text-gray-900 dark:text-gray-100">{{ $product->supplier ?: 'Not specified' }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Description</dt>
                        <dd class="mt-1 whitespace-pre-line text-gray-900 dark:text-gray-100">{{ $product->description ?: 'No description provided.' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Created</dt>
                        <dd class="mt-1 text-gray-900 dark:text-gray-100">{{ $product->created_at?->format('M d, Y h:i A') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Last Updated</dt>
                        <dd class="mt-1 text-gray-900 dark:text-gray-100">{{ $product->updated_at?->format('M d, Y h:i A') ?? '—' }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</x-app-layout>
