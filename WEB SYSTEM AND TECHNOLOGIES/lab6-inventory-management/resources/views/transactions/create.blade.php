<x-app-layout>
    <div class="transaction-form-page">
        <div class="form-container">
            <a href="{{ route('transactions.index') }}" class="back-link">← Back to Transactions</a>

            <header>
                <span>INVENTORY MANAGEMENT</span>
                <h1>Record Inventory Transaction</h1>
                <p>Record stock received, stock released, or a manual stock adjustment.</p>
            </header>

            @if ($errors->any())
                <div class="form-errors">
                    <strong>Please correct the following:</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <section class="form-card">
                <form
                    id="transaction-form"
                    method="POST"
                    action="{{ route('transactions.store') }}"
                >
                    @csrf

                    <input
                        type="hidden"
                        name="submission_token"
                        value="{{ old('submission_token', $submissionToken) }}"
                    >

                    <div class="field">
                        <label for="product_id">Product <b>*</b></label>
                        <select id="product_id" name="product_id" required>
                            <option value="">Select a product</option>
                            @foreach ($products as $product)
                                <option
                                    value="{{ $product->id }}"
                                    data-stock="{{ $product->quantity }}"
                                    @selected(old('product_id') == $product->id)
                                >
                                    {{ $product->name }} — Current stock: {{ $product->quantity }}
                                </option>
                            @endforeach
                        </select>

                        <small id="current-stock">Select a product to view its current stock.</small>

                        @error('product_id')
                            <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="type">Transaction Type <b>*</b></label>
                        <select id="type" name="type" required>
                            <option value="">Select transaction type</option>
                            <option value="stock_in" @selected(old('type') === 'stock_in')>
                                Stock In — Add inventory
                            </option>
                            <option value="stock_out" @selected(old('type') === 'stock_out')>
                                Stock Out — Release inventory
                            </option>
                            <option value="adjustment" @selected(old('type') === 'adjustment')>
                                Adjustment — Correct inventory quantity
                            </option>
                        </select>

                        @error('type')
                            <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="quantity">Quantity <b>*</b></label>
                        <input
                            type="number"
                            id="quantity"
                            name="quantity"
                            value="{{ old('quantity') }}"
                            step="1"
                            required
                            placeholder="Enter quantity"
                        >

                        <small id="quantity-help">
                            For stock-in and stock-out, enter a positive whole number.
                            For adjustment, use a positive number to add stock or a negative
                            number to remove stock.
                        </small>

                        @error('quantity')
                            <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="reference_number">Reference Number</label>
                        <input
                            type="text"
                            id="reference_number"
                            name="reference_number"
                            value="{{ old('reference_number') }}"
                            maxlength="255"
                            placeholder="e.g. PO-2026-001"
                        >

                        @error('reference_number')
                            <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="notes">Notes</label>
                        <textarea
                            id="notes"
                            name="notes"
                            rows="4"
                            maxlength="5000"
                            placeholder="Optional transaction details..."
                        >{{ old('notes') }}</textarea>

                        @error('notes')
                            <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-note">
                        <strong>Important:</strong>
                        The system automatically updates the product's stock after a
                        transaction is saved. Stock cannot become negative.
                    </div>

                    <div class="form-actions">
                        <a href="{{ route('transactions.index') }}" class="cancel-btn">
                            Cancel
                        </a>

                        <button
                            type="submit"
                            id="save-transaction-button"
                            class="save-btn"
                        >
                            Save Transaction
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </div>

    <style>
        .transaction-form-page {
            min-height: calc(100vh - 76px);
            padding: 35px 20px;
            background: #f5f7fb;
            font-family: Inter, ui-sans-serif, system-ui, sans-serif;
            color: #17243b;
        }

        .form-container {
            max-width: 760px;
            margin: auto;
        }

        .back-link {
            color: #3975eb;
            font-size: 12px;
            font-weight: 750;
            text-decoration: none;
        }

        .form-container header {
            margin: 22px 0;
        }

        .form-container header span {
            color: #3975eb;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 1.5px;
        }

        .form-container h1 {
            margin: 7px 0;
            color: #17243b;
            font-size: 30px;
            font-weight: 800;
        }

        .form-container header p {
            margin: 0;
            color: #8290a5;
            font-size: 12px;
        }

        .form-card {
            padding: 27px;
            border: 1px solid #e5eaf2;
            border-radius: 15px;
            background: #fff;
            box-shadow: 0 4px 18px #24395b08;
        }

        .field {
            display: grid;
            gap: 8px;
            margin-bottom: 19px;
        }

        .field label {
            color: #34445d;
            font-size: 12px;
            font-weight: 800;
        }

        .field label b {
            color: #dc4654;
        }

        .field input,
        .field select,
        .field textarea {
            width: 100%;
            padding: 11px 12px;
            border: 1px solid #dfe6ef;
            border-radius: 9px;
            background: #fff;
            color: #34445d;
            font-size: 13px;
            outline: none;
        }

        .field input:focus,
        .field select:focus,
        .field textarea:focus {
            border-color: #6a99eb;
            box-shadow: 0 0 0 3px #3979f61a;
        }

        .field small {
            color: #8793a5;
            font-size: 10px;
            line-height: 1.6;
        }

        .field-error {
            color: #c43d45;
            font-size: 11px;
        }

        .form-errors {
            margin-bottom: 16px;
            padding: 13px;
            border: 1px solid #fecaca;
            border-radius: 9px;
            background: #fff1f2;
            color: #be3445;
            font-size: 12px;
        }

        .form-errors ul {
            margin: 6px 0 0;
            padding-left: 18px;
        }

        .form-note {
            margin: 7px 0 22px;
            padding: 13px;
            border-radius: 9px;
            background: #f2f7ff;
            color: #526b91;
            font-size: 11px;
            line-height: 1.6;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 9px;
        }

        .cancel-btn,
        .save-btn {
            min-height: 42px;
            padding: 0 17px;
            border: 0;
            border-radius: 9px;
            font-size: 12px;
            font-weight: 750;
            text-decoration: none;
            cursor: pointer;
        }

        .cancel-btn {
            display: inline-flex;
            align-items: center;
            border: 1px solid #dfe6ef;
            color: #52647d;
            background: #fff;
        }

        .save-btn {
            background: linear-gradient(135deg, #3979f6, #2456d9);
            color: #fff;
            transition: opacity 0.2s ease;
        }

        .save-btn:disabled {
            opacity: 0.65;
            cursor: not-allowed;
        }

        @media (max-width: 600px) {
            .transaction-form-page {
                padding: 24px 13px;
            }

            .form-card {
                padding: 18px;
            }

            .form-container h1 {
                font-size: 25px;
            }
        }
    </style>

    <script>
        const productSelect = document.getElementById('product_id');
        const typeSelect = document.getElementById('type');
        const quantityInput = document.getElementById('quantity');
        const stockLabel = document.getElementById('current-stock');
        const quantityHelp = document.getElementById('quantity-help');

        function updateTransactionHints() {
            const option = productSelect.options[productSelect.selectedIndex];

            stockLabel.textContent = option && option.dataset.stock !== undefined
                ? `Current stock: ${option.dataset.stock} units`
                : 'Select a product to view its current stock.';

            if (typeSelect.value === 'adjustment') {
                quantityInput.removeAttribute('min');
                quantityHelp.textContent =
                    'Enter a positive number to add stock or a negative number to remove stock.';
            } else {
                quantityInput.min = '1';
                quantityHelp.textContent =
                    'Enter a positive whole number. Stock-out cannot exceed the available quantity.';
            }
        }

        productSelect.addEventListener('change', updateTransactionHints);
        typeSelect.addEventListener('change', updateTransactionHints);

        updateTransactionHints();

        /*
         * Prevent repeated clicks on the Save Transaction button.
         * The backend submission token provides the second layer of protection.
         */
        const transactionForm = document.getElementById('transaction-form');
        const saveButton = document.getElementById('save-transaction-button');

        transactionForm.addEventListener('submit', function () {
            if (saveButton.disabled) {
                return;
            }

            saveButton.disabled = true;
            saveButton.textContent = 'Saving...';
        });
    </script>
</x-app-layout>