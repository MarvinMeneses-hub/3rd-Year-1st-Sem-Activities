
<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-blue-600">Inventory workspace</p>
            <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-900">Edit Product</h1>
            <p class="mt-1 text-sm text-slate-500">Update the details of an existing inventory item.</p>
        </div>
    </x-slot>

    <div class="min-h-screen bg-slate-50 px-4 py-8 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl">
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 bg-slate-50/70 px-6 py-5 sm:px-8">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-100 text-amber-700">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m16 4 4 4M4 20l4-.8L19.5 7.7a2.1 2.1 0 0 0-3-3L5 16.2 4 20Z"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <h2 class="font-extrabold text-slate-900">Product information</h2>
                            <p class="mt-1 truncate text-sm text-slate-500">{{ $product->name }} · ID {{ $product->id }}</p>
                        </div>
                    </div>
                </div>

                <form action="{{ route('products.update', $product) }}" method="POST" class="space-y-6 p-6 sm:p-8">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="name" class="mb-2 block text-sm font-bold text-slate-700">Product name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name', $product->name) }}" required
                               class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10">
                        @error('name') <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="description" class="mb-2 block text-sm font-bold text-slate-700">Description</label>
                        <textarea name="description" id="description" rows="4"
                                  class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10">{{ old('description', $product->description) }}</textarea>
                        @error('description') <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label for="price" class="mb-2 block text-sm font-bold text-slate-700">Unit price ($) <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-semibold text-slate-400">$</span>
                                <input type="number" step="0.01" name="price" id="price" value="{{ old('price', $product->price) }}" required
                                       class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-9 pr-4 text-sm text-slate-800 focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10">
                            </div>
                            @error('price') <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="quantity" class="mb-2 block text-sm font-bold text-slate-700">Quantity <span class="text-red-500">*</span></label>
                            <input type="number" name="quantity" id="quantity" value="{{ old('quantity', $product->quantity) }}" required
                                   class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10">
                            @error('quantity') <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:justify-end">
                        <a href="{{ route('products.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-5 py-3 text-sm font-bold text-slate-600 transition hover:bg-slate-50">
                            Cancel
                        </a>
                        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m5 12 4 4L19 6"/>
                            </svg>
                            Update Product
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
