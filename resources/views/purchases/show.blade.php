<x-layouts.app title="Purchase">
    <div class="flex justify-between mb-4">
        <h1 class="text-2xl font-semibold">Purchase {{ $purchase->invoice_number ?: '#'.$purchase->id }}</h1>
        <button type="button" onclick="window.print()" class="bg-slate-900 text-white px-4 py-2 rounded">Print</button>
    </div>
    <div class="bg-white p-6 rounded-lg space-y-3">
        <p>{{ $purchase->branch?->name }} · {{ $purchase->purchased_on->format('d M Y') }} · {{ $purchase->supplier_name }}</p>
        <p>Entered by {{ $purchase->creator?->name }} · Total AED {{ \App\Support\Money::fromFils($purchase->total_fils) }}</p>
        @if ($purchase->notes)
            <p class="text-sm text-slate-600">{{ $purchase->notes }}</p>
        @endif
        <table class="w-full text-sm">
            <thead class="text-left text-slate-500"><tr><th class="py-1">Item</th><th>Qty</th><th>Cost</th><th>Line</th></tr></thead>
            <tbody>
            @foreach ($purchase->items as $item)
                <tr class="border-t">
                    <td class="py-2">{{ $item->product?->name }} ({{ $item->product?->sku }})</td>
                    <td>{{ $item->qty }}</td>
                    <td>AED {{ \App\Support\Money::fromFils($item->unit_cost_fils) }}</td>
                    <td>AED {{ \App\Support\Money::fromFils($item->line_total_fils) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        @if ($purchase->invoice_path)
            <div class="pt-4">
                <h2 class="font-semibold mb-2">Scanned invoice</h2>
                @if (str_ends_with(strtolower($purchase->invoice_path), '.pdf'))
                    <a class="text-blue-700" href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($purchase->invoice_path) }}" target="_blank">Open PDF</a>
                @else
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($purchase->invoice_path) }}" alt="Invoice scan" class="max-w-lg border rounded">
                @endif
            </div>
        @endif
    </div>
</x-layouts.app>
