<x-layouts.app title="Confirm receipt">
    <p class="text-sm text-slate-500 mb-4">{{ $purchase->branch?->name }} · ordered {{ $purchase->purchased_on->format('d M Y') }}. Enter the quantity that actually arrived. Cost and invoice are hidden — accounts see those after you confirm.</p>
    <form method="POST" action="{{ route('godown.confirm', $purchase) }}" class="bg-white rounded-2xl border border-slate-100 p-5">
        @csrf
        <table class="w-full text-sm">
            <thead class="text-left text-slate-500">
                <tr>
                    <th class="py-2">Product</th>
                    <th>SKU</th>
                    <th>Ordered</th>
                    <th>Actual received</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($purchase->items as $item)
                <tr class="border-t">
                    <td class="py-3">{{ $item->product?->name }}</td>
                    <td>{{ $item->product?->sku }}</td>
                    <td>{{ $item->qty }}</td>
                    <td>
                        <input type="number" min="0" required name="received[{{ $item->id }}]" value="{{ old('received.'.$item->id, $item->qty) }}" class="border rounded-lg w-28 px-2 py-1.5">
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <button class="mt-4 bg-slate-900 text-white px-4 py-2 rounded-xl">Confirm received</button>
    </form>
</x-layouts.app>
