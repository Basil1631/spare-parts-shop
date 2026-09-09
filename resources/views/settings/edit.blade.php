<x-layouts.app title="Settings">
    <h1 class="text-2xl font-semibold mb-4">Shop settings</h1>
    <form method="POST" action="{{ route('settings.update') }}" class="bg-white rounded-lg shadow-sm p-6 max-w-xl space-y-4">
        @csrf
        @method('PUT')
        <div>
            <label class="block text-sm font-medium mb-1">Shop name</label>
            <input name="shop_name" required value="{{ old('shop_name', $settings->shop_name) }}" class="w-full border rounded-md px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Address</label>
            <textarea name="address" class="w-full border rounded-md px-3 py-2">{{ old('address', $settings->address) }}</textarea>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">TRN</label>
            <input name="trn" value="{{ old('trn', $settings->trn) }}" class="w-full border rounded-md px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Phone</label>
            <input name="phone" value="{{ old('phone', $settings->phone) }}" class="w-full border rounded-md px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Default VAT %</label>
            <input name="vat_percent" type="number" step="0.01" required value="{{ old('vat_percent', $settings->vat_percent) }}" class="w-full border rounded-md px-3 py-2">
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-sm font-medium mb-1">Invoice prefix</label>
                <input name="invoice_prefix" required value="{{ old('invoice_prefix', $settings->invoice_prefix) }}" class="w-full border rounded-md px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Credit note prefix</label>
                <input name="credit_note_prefix" required value="{{ old('credit_note_prefix', $settings->credit_note_prefix) }}" class="w-full border rounded-md px-3 py-2">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Installment collection day of month</label>
            <input name="installment_day" type="number" min="1" max="28" required value="{{ old('installment_day', $settings->installment_day) }}" class="w-full border rounded-md px-3 py-2">
            <p class="text-xs text-slate-500 mt-1">Default 3 — dues fall on the 3rd of each month.</p>
        </div>
        <button class="bg-slate-900 text-white px-4 py-2 rounded-md">Save</button>
    </form>
</x-layouts.app>
