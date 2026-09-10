<x-layouts.app title="Record purchase">
    <h1 class="text-2xl font-semibold mb-2">Purchase spare parts</h1>
    <p class="text-sm text-slate-500 mb-4">Choose products that already exist. This only increases quantity — it does not create a new SKU. Scan or photograph the supplier invoice. Print from the browser if needed.</p>
    <form method="POST" action="{{ route('purchases.store') }}" enctype="multipart/form-data" class="bg-white p-6 rounded-lg space-y-4">
        @csrf
        @if (auth()->user()->isAdmin())
            <select name="branch_id" required class="w-full border rounded px-3 py-2">
                <option value="">Branch</option>
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}" @selected(old('branch_id') == $branch->id)>{{ $branch->name }}</option>
                @endforeach
            </select>
        @endif
        <div class="grid md:grid-cols-2 gap-3">
            <input name="supplier_name" value="{{ old('supplier_name') }}" placeholder="Supplier" class="border rounded px-3 py-2">
            <input name="invoice_number" value="{{ old('invoice_number') }}" placeholder="Supplier invoice no." class="border rounded px-3 py-2">
            <input name="purchased_on" type="date" required value="{{ old('purchased_on', now()->toDateString()) }}" class="border rounded px-3 py-2">
            <input name="default_profit_percent" type="number" step="0.1" min="0" value="{{ old('default_profit_percent', 0) }}" placeholder="Default profit % (new items)" class="border rounded px-3 py-2">
        </div>
        <textarea name="notes" placeholder="Notes" class="w-full border rounded px-3 py-2">{{ old('notes') }}</textarea>
        <div>
            <label class="block text-sm font-medium mb-1">Scan / photo of purchase invoice</label>
            <input type="file" name="invoice_scan" accept="image/*,application/pdf" capture="environment" class="block">
        </div>
        <table class="w-full text-sm">
            <thead><tr class="text-left"><th>Product</th><th>Qty</th><th>Unit cost AED</th></tr></thead>
            <tbody>
            @for ($i = 0; $i < 8; $i++)
                <tr>
                    <td>
                        <select name="product_id[]" class="border rounded px-2 py-1 w-full">
                            <option value="">—</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}" @selected(old('product_id.'.$i) == $product->id)>{{ $product->sku }} · {{ $product->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td><input name="qty[]" type="number" min="0" value="{{ old('qty.'.$i) }}" class="border rounded w-24 px-2 py-1"></td>
                    <td><input name="unit_cost[]" type="number" step="0.01" min="0" value="{{ old('unit_cost.'.$i) }}" class="border rounded w-32 px-2 py-1"></td>
                </tr>
            @endfor
            </tbody>
        </table>
        <button class="bg-slate-900 text-white px-4 py-2 rounded">Save purchase</button>
    </form>
</x-layouts.app>
