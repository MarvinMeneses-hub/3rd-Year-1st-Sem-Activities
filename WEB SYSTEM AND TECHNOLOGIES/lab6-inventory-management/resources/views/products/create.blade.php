<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Add New Product') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">

                <form action="{{ route('products.store') }}" method="POST">
                    @csrf

                    <!-- Product Name -->
                    <div class="mb-4">
                        <label class="block text-gray-700 dark:text-gray-300 font-medium mb-2"
                               for="name">Product Name</label>

                        <input type="text" name="name" id="name"
                               value="{{ old('name') }}" required
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white dark:border-gray-600">

                        @error('name')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- SKU -->
                    <div class="mb-4">
                        <label class="block text-gray-700 dark:text-gray-300 font-medium mb-2"
                               for="sku">SKU (Stock Keeping Unit)</label>

                        <input type="text" name="sku" id="sku"
                               value="{{ old('sku') }}"
                               placeholder="Example: TOOL-001"
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white dark:border-gray-600">

                        @error('sku')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Category and Supplier -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-700 dark:text-gray-300 font-medium mb-2"
                                   for="category">Category</label>

                            <select name="category" id="category"
                                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white dark:border-gray-600">
                                <option value="">Select a category</option>

                                @foreach ([
                                    'Lumber',
                                    'Hardware',
                                    'Tools',
                                    'Electrical',
                                    'Plumbing',
                                    'Paint'
                                ] as $category)
                                    <option value="{{ $category }}"
                                        {{ old('category') === $category ? 'selected' : '' }}>
                                        {{ $category }}
                                    </option>
                                @endforeach
                            </select>

                            @error('category')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-gray-700 dark:text-gray-300 font-medium mb-2"
                                   for="supplier">Supplier</label>

                            <input type="text" name="supplier" id="supplier"
                                   value="{{ old('supplier') }}"
                                   placeholder="Supplier name"
                                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white dark:border-gray-600">

                            @error('supplier')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mb-4">
                        <label class="block text-gray-700 dark:text-gray-300 font-medium mb-2"
                               for="description">Description</label>

                        <textarea name="description" id="description" rows="3"
                                  class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white dark:border-gray-600">{{ old('description') }}</textarea>

                        @error('description')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Unit Price and Quantity -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-700 dark:text-gray-300 font-medium mb-2"
                                   for="price">Unit Price ($)</label>

                            <input type="number" step="0.01" min="0"
                                   name="price" id="price"
                                   value="{{ old('price') }}" required
                                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white dark:border-gray-600">

                            @error('price')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-gray-700 dark:text-gray-300 font-medium mb-2"
                                   for="quantity">Quantity in Stock</label>

                            <input type="number" min="0" step="1"
                                   name="quantity" id="quantity"
                                   value="{{ old('quantity', 0) }}" required
                                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white dark:border-gray-600">

                            @error('quantity')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Reorder Level -->
                    <div class="mb-6">
                        <label class="block text-gray-700 dark:text-gray-300 font-medium mb-2"
                               for="reorder_level">Low Stock Alert Level</label>

                        <input type="number" min="0" step="1"
                               name="reorder_level" id="reorder_level"
                               value="{{ old('reorder_level', 10) }}" required
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white dark:border-gray-600">

                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            The product will be considered low stock when its quantity
                            is at or below this number.
                        </p>

                        @error('reorder_level')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('products.index') }}"
                           class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-md text-sm">
                            Cancel
                        </a>

                        <button type="submit"
                                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md text-sm">
                            Save Product
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>