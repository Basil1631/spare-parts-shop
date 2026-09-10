<x-layouts.app title="Products">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <h1 class="text-2xl font-semibold">Products</h1>
        @if (auth()->user()->canManageCatalog())
            <a href="{{ route('products.create') }}" class="bg-slate-900 text-white px-4 py-2 rounded-xl">Add product</a>
        @endif
    </div>
    <form class="mb-4 flex gap-2">
        <input name="q" value="{{ request('q') }}" placeholder="Search name or SKU" class="border rounded-md px-3 py-2 w-72 bg-white">
        <button class="border bg-white px-3 rounded-md">Search</button>
    </form>
    <div class="bg-white rounded-lg shadow-sm overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left">
                <tr>
                    <th class="px-3 py-2">Name</th>
                    <th>SKU</th>
                    <th>MRP (AED)</th>
                    <th>Min qty</th>
                    <th>On hand</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @foreach ($products as $product)
                <tr class="border-t {{ $product->isLowStock() ? 'bg-red-50' : '' }}">
                    <td class="px-3 py-2">{{ $product->name }}</td>
                    <td>{{ $product->sku }}</td>
                    <td>{{ \App\Support\Money::fromFils($product->price_fils) }}</td>
                    <td>{{ $product->min_qty }}</td>
                    <td class="{{ $product->isLowStock() ? 'text-red-700 font-semibold' : '' }}">{{ $product->qty_on_hand }}</td>
                    <td class="px-3">
                        @if (auth()->user()->canManageCatalog())
                            <a class="text-teal-700" href="{{ route('products.edit', $product) }}">Edit</a>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $products->links() }}</div>
</x-layouts.app>
