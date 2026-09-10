<x-layouts.app title="Floor prices">
    <h1 class="text-2xl font-semibold mb-2">Floor price (purchase cost + profit %)</h1>
    <p class="text-sm text-slate-500 mb-4">Sales staff cannot sell below this floor. They may add 5%, 20%, or a custom % on the bill.</p>
    @if (auth()->user()->isAdmin())
        <form method="GET" class="mb-4">
            <select name="branch_id" class="border rounded px-3 py-2" onchange="this.form.submit()">
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}" @selected($branchId == $branch->id)>{{ $branch->name }}</option>
                @endforeach
            </select>
        </form>
    @endif
    @if (! $branchId)
        <p class="text-red-700">Add a branch first.</p>
    @else
        <form method="POST" action="{{ route('pricing.update') }}" class="bg-white rounded-lg overflow-x-auto">
            @csrf
            <input type="hidden" name="branch_id" value="{{ $branchId }}">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-left">
                    <tr>
                        <th class="px-3 py-2">Product</th>
                        <th>Last cost</th>
                        <th>Profit %</th>
                        <th>Floor</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($products as $product)
                    @php $row = $prices->get($product->id); @endphp
                    <tr class="border-t">
                        <td class="px-3 py-2">{{ $product->name }} <span class="text-slate-500">{{ $product->sku }}</span></td>
                        <td>AED {{ \App\Support\Money::fromFils($row?->last_cost_fils ?? 0) }}</td>
                        <td>
                            <input name="profit[{{ $product->id }}]" type="number" step="0.1" min="0"
                                   value="{{ old('profit.'.$product->id, $row?->profit_percent ?? 0) }}"
                                   class="border rounded w-24 px-2 py-1">
                        </td>
                        <td>AED {{ \App\Support\Money::fromFils($row?->floor_fils ?? 0) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div class="p-3"><button class="bg-slate-900 text-white px-4 py-2 rounded">Save profit %</button></div>
        </form>
    @endif
</x-layouts.app>
