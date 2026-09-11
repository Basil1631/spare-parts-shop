<x-layouts.app title="Vendor bill">
    <div class="flex flex-wrap justify-between gap-3 mb-4">
        <div>
            <h1 class="text-xl font-semibold">{{ $purchase->invoice_number ?: 'Purchase #'.$purchase->id }}</h1>
            <p class="text-sm text-slate-500">{{ $purchase->status->label() }} · {{ $purchase->supplier_name }} · {{ $purchase->branch?->name }}</p>
        </div>
        <div class="flex gap-2">
            @if ($purchase->isReceived())
                <form method="POST" action="{{ route('vendor-bills.paid', $purchase) }}">
                    @csrf
                    <button class="bg-emerald-700 text-white px-4 py-2 rounded-xl">Mark paid</button>
                </form>
            @endif
            @if ($purchase->isPaid())
                <form method="POST" action="{{ route('vendor-bills.unpaid', $purchase) }}">
                    @csrf
                    <button class="bg-slate-700 text-white px-4 py-2 rounded-xl">Mark unpaid</button>
                </form>
            @endif
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 p-5 space-y-3">
        <p class="text-sm">Raised by {{ $purchase->creator?->name }} · Godown {{ $purchase->receiver?->name }} @if($purchase->received_at) ({{ $purchase->received_at->timezone(config('app.timezone'))->format('d M Y H:i') }}) @endif</p>
        @if ($purchase->payer)
            <p class="text-sm">Paid by {{ $purchase->payer->name }} on {{ $purchase->paid_at?->timezone(config('app.timezone'))->format('d M Y H:i') }}</p>
        @endif
        <table class="w-full text-sm">
            <thead class="text-left text-slate-500"><tr><th class="py-1">Item</th><th>Ordered</th><th>Received</th><th>Cost</th><th>Line</th></tr></thead>
            <tbody>
            @foreach ($purchase->items as $item)
                <tr class="border-t">
                    <td class="py-2">{{ $item->product?->name }} ({{ $item->product?->sku }})</td>
                    <td>{{ $item->qty }}</td>
                    <td>{{ $item->received_qty }}</td>
                    <td>AED {{ \App\Support\Money::fromFils($item->unit_cost_fils) }}</td>
                    <td>AED {{ \App\Support\Money::fromFils($item->line_total_fils) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <p class="font-semibold">Total AED {{ \App\Support\Money::fromFils($purchase->total_fils) }}</p>
        @if ($purchase->invoice_path)
            <div class="pt-2">
                <h2 class="font-semibold mb-2">Scanned invoice</h2>
                @if (str_ends_with(strtolower($purchase->invoice_path), '.pdf'))
                    <a class="text-teal-700" href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($purchase->invoice_path) }}" target="_blank">Open PDF</a>
                @else
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($purchase->invoice_path) }}" alt="Invoice scan" class="max-w-lg border rounded-xl">
                @endif
            </div>
        @endif
    </div>
</x-layouts.app>
