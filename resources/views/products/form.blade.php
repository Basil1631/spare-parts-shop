@php
    $p = $product ?? null;
@endphp
<x-layouts.app :title="$p ? 'Edit product' : 'Add product'">
    <h1 class="text-2xl font-semibold mb-4">{{ $p ? 'Edit product' : 'Add product' }}</h1>
    <form method="POST" action="{{ $p ? route('products.update', $p) : route('products.store') }}" class="bg-white rounded-lg shadow-sm p-6 max-w-xl space-y-4">
        @csrf
        @if ($p) @method('PUT') @endif
        <div>
            <label class="block text-sm font-medium mb-1">Product name</label>
            <input name="name" value="{{ old('name', $p?->name ?? '') }}" required class="w-full border rounded-md px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">SKU code</label>
            <input name="sku" value="{{ old('sku', $p?->sku ?? '') }}" required class="w-full border rounded-md px-3 py-2">
            <p class="text-xs text-slate-500 mt-1">Spaces and dashes are ignored when checking duplicates.</p>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">MRP / selling price (AED, exclusive of VAT)</label>
            <input name="price" type="number" step="0.01" min="0" value="{{ old('price', $p ? number_format($p->price_fils/100, 2, '.', '') : '') }}" required class="w-full border rounded-md px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">VAT % (blank = shop default)</label>
            <input name="vat_rate" type="number" step="0.01" min="0" value="{{ old('vat_rate', $p?->vat_rate ?? '') }}" class="w-full border rounded-md px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Minimum stock quantity</label>
            <input name="min_qty" type="number" min="0" value="{{ old('min_qty', $p?->min_qty ?? 0) }}" required class="w-full border rounded-md px-3 py-2">
        </div>
        @if ($p)
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="active" value="1" @checked(old('active', $p->active))>
                Active
            </label>
        @endif
        <button class="bg-slate-900 text-white px-4 py-2 rounded-md">Save</button>
    </form>
</x-layouts.app>
