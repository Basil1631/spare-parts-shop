<x-layouts.app title="Incoming stock">
    <p class="text-sm text-slate-500 mb-4">Confirm goods at the godown. You see product name, SKU and quantity only. Stock increases after you confirm.</p>
    <div class="bg-white rounded-2xl border border-slate-100 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left">
                <tr>
                    <th class="px-3 py-3">Date</th>
                    <th>Branch</th>
                    <th>Lines</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @forelse ($purchases as $purchase)
                <tr class="border-t">
                    <td class="px-3 py-3">{{ $purchase->purchased_on->format('d M Y') }}</td>
                    <td>{{ $purchase->branch?->name }}</td>
                    <td>{{ $purchase->items->count() }} SKUs</td>
                    <td><a class="text-teal-700 font-medium" href="{{ route('godown.show', $purchase) }}">Check in</a></td>
                </tr>
            @empty
                <tr><td colspan="4" class="px-3 py-8 text-slate-500">No purchases waiting at the godown.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $purchases->links() }}</div>
</x-layouts.app>
