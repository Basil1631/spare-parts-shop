<x-layouts.app title="Stock">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <h1 class="text-2xl font-semibold">Stock</h1>
        <div class="flex gap-2">
            <a href="{{ route('stock.index') }}" class="px-3 py-2 rounded-md {{ ! $lowOnly ? 'bg-slate-900 text-white' : 'bg-white border' }}">All products</a>
            <a href="{{ route('stock.index', ['filter' => 'low']) }}" class="px-3 py-2 rounded-md {{ $lowOnly ? 'bg-red-600 text-white' : 'bg-white border' }}">Low stock</a>
            @if ($lowOnly)
                <a href="{{ route('stock.export') }}" class="px-3 py-2 rounded-md bg-emerald-700 text-white">Download CSV</a>
            @endif
        </div>
    </div>
    <form class="mb-4 flex gap-2">
        <input type="hidden" name="filter" value="{{ $lowOnly ? 'low' : '' }}">
        <input name="q" value="{{ request('q') }}" placeholder="Search name or SKU" class="border rounded-md px-3 py-2 w-72 bg-white">
        <button class="border bg-white px-3 rounded-md">Search</button>
    </form>
    @if ($products->isEmpty())
        <p class="text-slate-600">Add products first, then receive stock when goods arrive.</p>
    @endif
    <div class="space-y-3">
        @foreach ($products as $product)
            <div class="bg-white rounded-lg shadow-sm p-4 {{ $product->isLowStock() ? 'ring-2 ring-red-500' : '' }}">
                <div class="flex flex-wrap justify-between gap-3">
                    <div>
                        <div class="font-semibold {{ $product->isLowStock() ? 'text-red-700' : '' }}">
                            {{ $product->name }}
                            @if ($product->isLowStock())
                                <span class="ml-2 text-xs bg-red-600 text-white px-2 py-0.5 rounded">LOW</span>
                            @endif
                        </div>
                        <div class="text-sm text-slate-500">SKU {{ $product->sku }} · Min {{ $product->min_qty }} · On hand <span class="font-semibold">{{ $product->qty_on_hand }}</span></div>
                    </div>
                    <div class="flex flex-wrap gap-4">
                        <form method="POST" action="{{ route('stock.receive', $product) }}" class="flex gap-2 items-end">
                            @csrf
                            <div>
                                <label class="block text-xs text-slate-500">New stock arrived</label>
                                <input name="qty" type="number" min="1" required class="border rounded px-2 py-1 w-24">
                            </div>
                            <input name="note" placeholder="Note" class="border rounded px-2 py-1 w-40">
                            <button class="bg-emerald-700 text-white px-3 py-1 rounded">Receive</button>
                        </form>
                        <form method="POST" action="{{ route('stock.adjust-out', $product) }}" class="flex gap-2 items-end">
                            @csrf
                            <div>
                                <label class="block text-xs text-slate-500">Adjust out (damage)</label>
                                <input name="qty" type="number" min="1" required class="border rounded px-2 py-1 w-20">
                            </div>
                            <input name="note" placeholder="Reason" required class="border rounded px-2 py-1 w-36">
                            <button class="bg-slate-700 text-white px-3 py-1 rounded">Adjust</button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <div class="mt-4">{{ $products->links() }}</div>
</x-layouts.app>
