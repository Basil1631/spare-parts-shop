<x-layouts.app title="Stock">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-2">
        <div>
            <p class="text-sm text-slate-500">Receive goods against existing SKUs only. Quantity updates instantly on every dashboard. New catalogue items are added by admin / branch manager.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('stock.index') }}" class="px-3 py-2 rounded-xl text-sm {{ ! $lowOnly ? 'bg-slate-900 text-white' : 'bg-white border border-slate-200' }}">All</a>
            <a href="{{ route('stock.index', ['filter' => 'low']) }}" class="px-3 py-2 rounded-xl text-sm {{ $lowOnly ? 'bg-red-600 text-white' : 'bg-white border border-slate-200' }}">Low stock</a>
            @if ($lowOnly)
                <a href="{{ route('stock.export') }}" class="px-3 py-2 rounded-xl text-sm bg-emerald-700 text-white">CSV</a>
            @endif
            @if (auth()->user()->canPurchase())
                <a href="{{ route('purchases.create') }}" class="px-3 py-2 rounded-xl text-sm bg-teal-600 text-white">Restock + scan invoice</a>
            @endif
        </div>
    </div>
    <form class="mb-5 flex gap-2">
        <input type="hidden" name="filter" value="{{ $lowOnly ? 'low' : '' }}">
        <input name="q" value="{{ request('q') }}" placeholder="Search name or SKU" class="border border-slate-200 rounded-xl px-3 py-2 w-72 bg-white">
        <button class="border border-slate-200 bg-white px-4 rounded-xl">Search</button>
    </form>
    @if ($products->isEmpty())
        <p class="text-slate-600 bg-white rounded-2xl p-6">No matching products. Ask admin to add the SKU first, then restock here.</p>
    @endif
    <div class="space-y-3">
        @foreach ($products as $product)
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 {{ $product->isLowStock() ? 'ring-2 ring-red-400' : '' }}">
                <div class="flex flex-wrap justify-between gap-4">
                    <div>
                        <div class="font-semibold {{ $product->isLowStock() ? 'text-red-700' : 'text-slate-900' }}">
                            {{ $product->name }}
                            @if ($product->isLowStock())
                                <span class="ml-2 text-xs bg-red-600 text-white px-2 py-0.5 rounded-full">LOW</span>
                            @endif
                        </div>
                        <div class="text-sm text-slate-500 mt-1">SKU {{ $product->sku }} · Min {{ $product->min_qty }} · On hand <span class="font-semibold text-slate-800">{{ $product->qty_on_hand }}</span></div>
                    </div>
                    @if (auth()->user()->canPurchase())
                    <div class="flex flex-col gap-3 w-full lg:w-auto">
                        <form method="POST" action="{{ route('stock.receive', $product) }}" enctype="multipart/form-data" class="flex flex-wrap gap-2 items-end bg-slate-50 rounded-xl p-3">
                            @csrf
                            <div>
                                <label class="block text-xs text-slate-500">Qty arrived</label>
                                <input name="qty" type="number" min="1" required class="border rounded-lg px-2 py-1.5 w-24">
                            </div>
                            <div>
                                <label class="block text-xs text-slate-500">Unit cost AED (optional)</label>
                                <input name="unit_cost" type="number" step="0.01" min="0" class="border rounded-lg px-2 py-1.5 w-28">
                            </div>
                            <div>
                                <label class="block text-xs text-slate-500">Scan / photo invoice</label>
                                <input type="file" name="invoice_scan" accept="image/*,application/pdf" capture="environment" class="text-xs w-44">
                            </div>
                            <input name="note" placeholder="Note" class="border rounded-lg px-2 py-1.5 w-36">
                            <button class="bg-emerald-700 text-white px-3 py-1.5 rounded-lg text-sm">Receive</button>
                        </form>
                        <form method="POST" action="{{ route('stock.adjust-out', $product) }}" class="flex gap-2 items-end">
                            @csrf
                            <div>
                                <label class="block text-xs text-slate-500">Adjust out (damage)</label>
                                <input name="qty" type="number" min="1" required class="border rounded-lg px-2 py-1.5 w-20">
                            </div>
                            <input name="note" placeholder="Reason" required class="border rounded-lg px-2 py-1.5 w-36">
                            <button class="bg-slate-700 text-white px-3 py-1.5 rounded-lg text-sm">Adjust</button>
                        </form>
                    </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
    <div class="mt-4">{{ $products->links() }}</div>
</x-layouts.app>
