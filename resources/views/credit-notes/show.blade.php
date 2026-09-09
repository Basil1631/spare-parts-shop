<x-layouts.app :title="$creditNote->number">
    <div class="flex justify-between mb-4">
        <h1 class="text-2xl font-semibold">{{ $creditNote->number }}</h1>
        <a href="{{ route('credit-notes.pdf', $creditNote) }}" class="bg-slate-900 text-white px-4 py-2 rounded-md">Download PDF</a>
    </div>
    <p class="mb-4">Original invoice <a class="text-blue-700" href="{{ route('bills.show', $creditNote->bill) }}">{{ $creditNote->bill->number }}</a> dated {{ $creditNote->bill->billed_at->format('d M Y') }}</p>
    <p class="mb-4"><strong>Reason:</strong> {{ $creditNote->reason }}</p>
    <table class="w-full text-sm bg-white rounded-lg">
        <thead class="bg-slate-50 text-left"><tr><th class="px-3 py-2">Item</th><th>Qty</th><th>Total AED</th></tr></thead>
        <tbody>
        @foreach ($creditNote->items as $item)
            <tr class="border-t">
                <td class="px-3 py-2">{{ $item->billItem->name ?? '' }} ({{ $item->billItem->sku ?? '' }})</td>
                <td>{{ $item->qty }}</td>
                <td>{{ \App\Support\Money::fromFils($item->line_total_fils) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <p class="mt-4 font-semibold">Credit total AED {{ \App\Support\Money::fromFils($creditNote->total_fils) }} (VAT {{ \App\Support\Money::fromFils($creditNote->vat_fils) }})</p>
</x-layouts.app>
