<x-app-layout>
    <div class="products-page">
        <div class="products-container">

            {{-- Page heading --}}
            <header class="products-heading">
                <div>
                    <span class="page-eyebrow">INVENTORY MANAGEMENT</span>
                    <h1>Products</h1>
                    <p>Manage your inventory, monitor stock, and keep product information organized.</p>
                </div>

                <a href="{{ route('products.create') }}" class="add-product-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2" stroke-linecap="round">
                        <path d="M12 5v14M5 12h14"/>
                    </svg>
                    <span>Add New Product</span>
                </a>
            </header>

            {{-- Success message --}}
            @if (session('success'))
                <div class="notice-success" role="status">
                    <span class="notice-icon">✓</span>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="notice-error" role="alert">{{ session('error') }}</div>
            @endif

            {{-- Search and summary --}}
            <section class="products-toolbar">
                <div class="toolbar-copy">
                    <h2>Product Inventory</h2>
                    <p>
                        @if(request('search'))
                            Showing search results for
                            <strong>{{ request('search') }}</strong>
                        @else
                            Review and manage your registered products.
                        @endif
                    </p>
                </div>

                <form method="GET"
                      action="{{ route('products.index') }}"
                      class="product-search">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="1.8" stroke-linecap="round">
                        <circle cx="11" cy="11" r="7"/>
                        <path d="m16 16 4 4"/>
                    </svg>

                    <input
                        type="search"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Search products..."
                        aria-label="Search products"
                    >

                    @if(request('search'))
                        <a href="{{ route('products.index') }}" class="clear-search">
                            Clear
                        </a>
                    @endif

                    <button type="submit" class="search-btn">Search</button>
                </form>
            </section>

            {{-- Product table --}}
            <section class="product-panel">
                <div class="panel-header">
                    <div>
                        <h2>All Products</h2>
                        <p>Your inventory records and available actions</p>
                    </div>

                    <span class="record-label">
                        {{ $products->total() }} {{ $products->total() === 1 ? 'record' : 'records' }}
                    </span>
                </div>

                <div class="table-scroll">
                    <table class="product-table">
                        <thead>
                            <tr>
                                <th>PRODUCT</th>
                                <th>DESCRIPTION</th>
                                <th>UNIT PRICE</th>
                                <th>QUANTITY</th>
                                <th>STOCK STATUS</th>
                                <th class="actions-heading">ACTIONS</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($products as $product)
                                <tr>
                                    <td data-label="Product">
                                        <div class="product-identity">
                                            <span class="product-avatar">
                                                {{ strtoupper(substr($product->name ?? 'P', 0, 1)) }}
                                            </span>
                                            <div class="product-name">
                                                <strong>{{ $product->name }}</strong>
                                                <small>Inventory item</small>
                                            </div>
                                        </div>
                                    </td>

                                    <td data-label="Description">
                                        <span class="description-text">
                                            {{ $product->description ?: 'No description provided' }}
                                        </span>
                                    </td>

                                    <td data-label="Unit Price">
                                        <span class="price-value">
                                            ${{ number_format((float) $product->price, 2) }}
                                        </span>
                                    </td>

                                    <td data-label="Quantity">
                                        <span class="quantity-value">
                                            {{ number_format((int) $product->quantity) }}
                                            <small>units</small>
                                        </span>
                                    </td>

                                    <td data-label="Stock Status">
                                        @if ((int) $product->quantity <= 0)
                                            <span class="stock-badge stock-out">
                                                <span></span> Out of stock
                                            </span>
                                        @elseif ((int) $product->quantity <= 5)
                                            <span class="stock-badge stock-low">
                                                <span></span> Low stock
                                            </span>
                                        @else
                                            <span class="stock-badge stock-available">
                                                <span></span> In stock
                                            </span>
                                        @endif
                                    </td>

                                    <td data-label="Actions">
                                        <div class="product-actions">
                                            <a href="{{ route('products.edit', $product) }}"
                                               class="action-edit">
                                                <svg viewBox="0 0 24 24" fill="none"
                                                     stroke="currentColor" stroke-width="1.8"
                                                     stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="m14 5 5 5M4 20l4.5-1 10.8-10.8a2.1 2.1 0 0 0-3-3L5.5 15.9 4 20Z"/>
                                                </svg>
                                                Edit
                                            </a>

                                            <form action="{{ route('products.destroy', $product) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('Are you sure you want to delete this product?');">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit" class="action-delete">
                                                    <svg viewBox="0 0 24 24" fill="none"
                                                         stroke="currentColor" stroke-width="1.8"
                                                         stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M3 6h18M8 6V4h8v2m3 0-1 14H6L5 6m4 4v6m6-6v6"/>
                                                    </svg>
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <div class="empty-products">
                                            <span class="empty-icon">
                                                <svg viewBox="0 0 24 24" fill="none"
                                                     stroke="currentColor" stroke-width="1.5">
                                                    <path d="m12 3 9 4.5v9L12 21l-9-4.5v-9z"/>
                                                    <path d="m3 7.5 9 4.5 9-4.5M12 12v9"/>
                                                </svg>
                                            </span>

                                            <h3>No products found</h3>
                                            <p>
                                                @if(request('search'))
                                                    Try a different search term or clear your search.
                                                @else
                                                    Add your first product to start managing your inventory.
                                                @endif
                                            </p>

                                            @if(request('search'))
                                                <a href="{{ route('products.index') }}" class="empty-action">
                                                    Clear Search
                                                </a>
                                            @else
                                                <a href="{{ route('products.create') }}" class="empty-action">
                                                    Add Your First Product
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if ($products->hasPages())
                    <div class="pagination-area">
                        <p>
                            Showing
                            <strong>{{ $products->firstItem() ?? 0 }}</strong>
                            to
                            <strong>{{ $products->lastItem() ?? 0 }}</strong>
                            of
                            <strong>{{ $products->total() }}</strong>
                            products
                        </p>

                        <div class="pagination-links">
                            {{ $products->appends(request()->query())->links() }}
                        </div>
                    </div>
                @endif
            </section>

            <footer class="products-footer">
                <span>StockPilot Inventory Management</span>
                <span>Keep your inventory accurate and organized.</span>
            </footer>

        </div>
    </div>

    <style>
        .products-page {
            min-height: calc(100vh - 76px);
            padding: 36px 28px 48px;
            background: #f5f7fb;
            color: #17243b;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system,
                         BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .products-container {
            width: 100%;
            max-width: 1440px;
            margin: 0 auto;
        }

        .products-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            margin-bottom: 28px;
        }

        .page-eyebrow {
            color: #3975eb;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 1.8px;
        }

        .products-heading h1 {
            margin: 7px 0 5px;
            color: #14213a;
            font-size: clamp(26px, 3vw, 34px);
            font-weight: 800;
            letter-spacing: -1.1px;
            line-height: 1.2;
        }

        .products-heading p,
        .toolbar-copy p,
        .panel-header p {
            margin: 0;
            color: #8290a5;
            font-size: 12px;
            line-height: 1.7;
        }

        .add-product-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            min-height: 44px;
            padding: 0 18px;
            border-radius: 11px;
            background: linear-gradient(135deg, #3979f6, #2456d9);
            box-shadow: 0 5px 12px #2456d927;
            color: white;
            font-size: 12px;
            font-weight: 750;
            text-decoration: none;
            white-space: nowrap;
            transition: transform .18s, box-shadow .18s;
        }

        .add-product-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 18px #2456d936;
        }

        .add-product-btn svg { width: 17px; height: 17px; }

        .notice-success {
            display: flex;
            align-items: center;
            gap: 11px;
            margin-bottom: 22px;
            padding: 14px 17px;
            border: 1px solid #b8ebd0;
            border-radius: 12px;
            background: #edfff5;
            color: #147a49;
            font-size: 13px;
        }

        .notice-error {
            margin-bottom: 22px;
            padding: 14px 17px;
            border: 1px solid #fecaca;
            border-radius: 12px;
            background: #fff1f2;
            color: #be3445;
            font-size: 13px;
        }

        .notice-icon {
            display: grid;
            place-items: center;
            width: 23px;
            height: 23px;
            border-radius: 50%;
            background: #c8f5dc;
            font-weight: 800;
        }

        .products-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 22px;
            margin-bottom: 20px;
        }

        .toolbar-copy h2,
        .panel-header h2 {
            margin: 0 0 5px;
            color: #202e46;
            font-size: 16px;
            font-weight: 800;
            letter-spacing: -.3px;
        }

        .product-search {
            display: flex;
            align-items: center;
            gap: 9px;
            width: min(100%, 430px);
            min-height: 44px;
            padding: 4px 5px 4px 13px;
            border: 1px solid #e3e9f2;
            border-radius: 12px;
            background: white;
            box-shadow: 0 2px 5px #1d355708;
        }

        .product-search > svg {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
            color: #94a0b3;
        }

        .product-search input {
            width: 100%;
            min-width: 0;
            border: 0;
            outline: 0;
            background: transparent;
            color: #23334d;
            font-size: 12px;
            box-shadow: none;
        }

        .product-search input:focus {
            border: 0;
            box-shadow: none;
            outline: none;
        }

        .product-search input::placeholder { color: #a0aabd; }

        .search-btn {
            min-height: 34px;
            padding: 0 14px;
            border: 0;
            border-radius: 8px;
            background: #edf3ff;
            color: #285bd0;
            font-size: 11px;
            font-weight: 800;
            cursor: pointer;
        }

        .search-btn:hover { background: #dfeaff; }

        .clear-search {
            color: #75839a;
            font-size: 11px;
            text-decoration: none;
        }

        .product-panel {
            overflow: hidden;
            border: 1px solid #e5eaf2;
            border-radius: 16px;
            background: white;
            box-shadow: 0 4px 18px #24395b08;
        }

        .panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 22px 24px;
            border-bottom: 1px solid #edf0f5;
        }

        .record-label {
            padding: 7px 11px;
            border: 1px solid #e3ebfa;
            border-radius: 8px;
            background: #f5f8ff;
            color: #4969a4;
            font-size: 10px;
            font-weight: 800;
            white-space: nowrap;
        }

        .table-scroll { width: 100%; overflow-x: auto; }

        .product-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        .product-table thead { background: #f9fafe; }

        .product-table th {
            padding: 15px 20px;
            border-bottom: 1px solid #edf0f5;
            color: #8793a7;
            font-size: 9px;
            font-weight: 800;
            letter-spacing: .9px;
            white-space: nowrap;
        }

        .product-table td {
            padding: 17px 20px;
            border-bottom: 1px solid #f0f3f8;
            color: #34445d;
            font-size: 12px;
            vertical-align: middle;
        }

        .product-table tbody tr:last-child td { border-bottom: 0; }

        .product-table tbody tr { transition: background .15s; }
        .product-table tbody tr:hover { background: #f9fbff; }

        .product-identity {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 145px;
        }

        .product-avatar {
            display: grid;
            place-items: center;
            width: 40px;
            height: 40px;
            flex-shrink: 0;
            border: 1px solid #dfe9ff;
            border-radius: 12px;
            background: #edf3ff;
            color: #3269df;
            font-size: 15px;
            font-weight: 800;
        }

        .product-name { display: grid; gap: 4px; }

        .product-name strong {
            color: #263650;
            font-size: 12px;
            font-weight: 800;
            overflow-wrap: anywhere;
        }

        .product-name small {
            color: #9aa5b6;
            font-size: 10px;
        }

        .description-text {
            display: block;
            max-width: 230px;
            color: #78869a;
            line-height: 1.6;
            overflow-wrap: anywhere;
        }

        .price-value {
            color: #1e344f;
            font-weight: 800;
            white-space: nowrap;
        }

        .quantity-value {
            display: inline-flex;
            align-items: baseline;
            gap: 5px;
            color: #263650;
            font-weight: 800;
            white-space: nowrap;
        }

        .quantity-value small {
            color: #96a1b2;
            font-size: 10px;
            font-weight: 500;
        }

        .stock-badge {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 7px 10px;
            border-radius: 8px;
            font-size: 10px;
            font-weight: 800;
            white-space: nowrap;
        }

        .stock-badge > span {
            width: 6px;
            height: 6px;
            border-radius: 50%;
        }

        .stock-available { background: #eafaf1; color: #218052; }
        .stock-available > span { background: #29b873; }

        .stock-low { background: #fff6e7; color: #ad6b0d; }
        .stock-low > span { background: #efa72f; }

        .stock-out { background: #fff0f1; color: #c83f4d; }
        .stock-out > span { background: #e35360; }

        .actions-heading { text-align: right; }

        .product-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 7px;
            white-space: nowrap;
        }

        .product-actions form { margin: 0; }

        .action-edit,
        .action-delete {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            min-height: 32px;
            padding: 0 9px;
            border: 1px solid transparent;
            border-radius: 8px;
            background: transparent;
            font-size: 11px;
            font-weight: 750;
            text-decoration: none;
            cursor: pointer;
            transition: background .15s, border-color .15s;
        }

        .action-edit { color: #3269df; }
        .action-edit:hover { background: #edf3ff; border-color: #dce7ff; }

        .action-delete { color: #d34c59; }
        .action-delete:hover { background: #fff0f1; border-color: #ffdcdf; }

        .action-edit svg, .action-delete svg { width: 14px; height: 14px; }

        .empty-products {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 60px 20px;
            text-align: center;
        }

        .empty-icon {
            display: grid;
            place-items: center;
            width: 60px;
            height: 60px;
            margin-bottom: 15px;
            border-radius: 18px;
            background: #edf3ff;
            color: #3975eb;
        }

        .empty-icon svg { width: 30px; height: 30px; }

        .empty-products h3 {
            margin: 0 0 7px;
            color: #24334c;
            font-size: 15px;
            font-weight: 800;
        }

        .empty-products p {
            max-width: 380px;
            margin: 0 0 18px;
            color: #8a96a9;
            font-size: 12px;
            line-height: 1.7;
        }

        .empty-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 15px;
            border-radius: 9px;
            background: #edf3ff;
            color: #285bd0;
            font-size: 11px;
            font-weight: 800;
            text-decoration: none;
        }

        .pagination-area {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 15px;
            padding: 18px 24px;
            border-top: 1px solid #edf0f5;
        }

        .pagination-area > p {
            margin: 0;
            color: #8995a8;
            font-size: 11px;
        }

        .pagination-area > p strong { color: #41516a; }

        .pagination-links { max-width: 100%; overflow-x: auto; }

        .products-footer {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 20px 2px 0;
            color: #9aa5b5;
            font-size: 10px;
        }

        .products-footer span:first-child {
            color: #75839a;
            font-weight: 700;
        }

        @media (max-width: 1000px) {
            .products-page { padding: 28px 18px 40px; }
            .products-toolbar { align-items: flex-start; flex-direction: column; }
            .product-search { width: 100%; max-width: none; }
            .product-table th, .product-table td { padding-right: 14px; padding-left: 14px; }
        }

        @media (max-width: 640px) {
            .products-page { padding: 24px 12px 32px; }

            .products-heading {
                align-items: flex-start;
                flex-direction: column;
                gap: 16px;
            }

            .add-product-btn { width: 100%; }

            .products-heading h1 { font-size: 28px; }

            .panel-header { padding: 17px 15px; }

            .product-table,
            .product-table tbody,
            .product-table tr,
            .product-table td {
                display: block;
                width: 100%;
            }

            .product-table thead { display: none; }

            .product-table tbody tr {
                padding: 12px 15px;
                border-bottom: 1px solid #e9eef5;
            }

            .product-table tbody tr:last-child { border-bottom: 0; }

            .product-table td {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 16px;
                padding: 10px 0;
                border: 0;
                text-align: right;
            }

            .product-table td::before {
                content: attr(data-label);
                flex-shrink: 0;
                padding-top: 2px;
                color: #8a96a9;
                font-size: 10px;
                font-weight: 800;
                text-align: left;
            }

            .product-table td:first-child {
                padding-top: 12px;
                padding-bottom: 13px;
                border-bottom: 1px dashed #e7edf5;
            }

            .product-table td:first-child::before { display: none; }

            .product-identity { text-align: left; }
            .description-text { max-width: 65%; }
            .product-actions { flex-wrap: wrap; }

            .empty-products { padding: 45px 15px; }

            .pagination-area {
                align-items: flex-start;
                flex-direction: column;
                padding: 16px;
            }

            .products-footer { flex-direction: column; gap: 5px; }
        }

        @media (prefers-reduced-motion: reduce) {
            .products-page *, .products-page *::before, .products-page *::after {
                transition: none !important;
            }
        }
    </style>
</x-app-layout>
