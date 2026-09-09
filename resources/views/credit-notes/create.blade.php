<x-layouts.app title="Tax credit note">
    <h1 class="text-2xl font-semibold mb-2">Tax credit note for {{ $bill->number }}</h1>
    <p class="text-slate-500 mb-4">Restores stock and reduces garage dues. Must reference the original tax invoice.</p>
    <form method="POST" action="{{ route('credit-notes.store', $bill) }}" class="bg-white rounded-lg shadow-sm p-6 space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium mb-1">Reason for issuance</label>
            <input name="reason" required value="{{ old('reason', 'Goods returned') }}" class="w-full border rounded-md px-3 py-2">
        </div>
        <table class="w-full text-sm">
            <thead class="text-left"><tr><th>SKU</th><th>Name</th><th>Billed</th><th>Already returned</th><th>Return now</th></tr></thead>
            <tbody>
            @foreach ($bill->items as $item)
                <tr class="border-t">
                    <td class="py-2">{{ $item->sku }}</td>
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->qty }}</td>
                    <td>{{ $item->returned_qty }}</td>
                    <td>
                        <input type="number" min="0" max="{{ $item->returnableQty() }}" name="qty[{{ $item->id }}]" value="0" class="border rounded w-24 px-2 py-1">
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <button class="bg-slate-900 text-white px-4 py-2 rounded-md">Issue tax credit note</button>
    </form>
</x-layouts.app>
