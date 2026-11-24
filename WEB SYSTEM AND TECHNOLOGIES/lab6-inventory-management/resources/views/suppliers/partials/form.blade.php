<div class="supplier-form-grid">
    <div class="supplier-field">
        <label for="company_name">Company Name *</label>
        <input id="company_name" name="company_name" type="text"
            value="{{ old('company_name', $supplier->company_name ?? '') }}" required>
        @error('company_name')
            <span class="field-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="supplier-field">
        <label for="contact_person">Contact Person *</label>
        <input id="contact_person" name="contact_person" type="text"
            value="{{ old('contact_person', $supplier->contact_person ?? '') }}" required>
        @error('contact_person')
            <span class="field-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="supplier-field">
        <label for="email">Email Address *</label>
        <input id="email" name="email" type="email"
            value="{{ old('email', $supplier->email ?? '') }}" required>
        @error('email')
            <span class="field-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="supplier-field">
        <label for="phone">Phone Number *</label>
        <input id="phone" name="phone" type="text"
            value="{{ old('phone', $supplier->phone ?? '') }}" required>
        @error('phone')
            <span class="field-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="supplier-field full">
        <label for="physical_address">Physical Address *</label>
        <textarea id="physical_address" name="physical_address" required>{{ old('physical_address', $supplier->physical_address ?? '') }}</textarea>
        @error('physical_address')
            <span class="field-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="supplier-field">
        <label for="payment_terms">Payment Terms</label>
        <input id="payment_terms" name="payment_terms" type="text"
            value="{{ old('payment_terms', $supplier->payment_terms ?? '') }}"
            placeholder="e.g. Net 30 days">
        @error('payment_terms')
            <span class="field-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="supplier-field">
        <label for="lead_time_days">Lead Time (Days) *</label>
        <input id="lead_time_days" name="lead_time_days" type="number" min="0"
            value="{{ old('lead_time_days', $supplier->lead_time_days ?? 7) }}" required>
        @error('lead_time_days')
            <span class="field-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="supplier-field">
        <label for="minimum_order_amount">Minimum Order Amount *</label>
        <input id="minimum_order_amount" name="minimum_order_amount" type="number"
            min="0" step="0.01"
            value="{{ old('minimum_order_amount', $supplier->minimum_order_amount ?? '0.00') }}"
            required>
        @error('minimum_order_amount')
            <span class="field-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="supplier-field">
        <label>Supplier Status</label>
        <input type="hidden" name="is_active" value="0">
        <label class="supplier-checkbox">
            <input type="checkbox" name="is_active" value="1"
                @checked((bool) old('is_active', isset($supplier) ? $supplier->is_active : true))>
            Active supplier
        </label>
        @error('is_active')
            <span class="field-error">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="supplier-form-actions">
    <a href="{{ route('suppliers.index') }}" class="supplier-secondary-btn">
        Cancel
    </a>

    <button type="submit" class="supplier-primary-btn">
        {{ $submitLabel }}
    </button>
</div>
