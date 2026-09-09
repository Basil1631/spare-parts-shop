<x-layouts.app :title="$bill->number">
    <div class="flex flex-wrap justify-between gap-3 mb-4">
        <div>
            <h1 class="text-2xl font-semibold">{{ $bill->number }}</h1>
            <p class="text-slate-500">{{ $bill->status->value }} · {{ $bill->payment_type->label() }} · {{ $bill->billed_at->timezone(config('app.timezone'))->format('d M Y H:i') }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('bills.pdf', $bill) }}" class="bg-slate-900 text-white px-4 py-2 rounded-md">Download PDF</a>
            <a href="{{ route('bills.print', $bill) }}" target="_blank" class="border bg-white px-4 py-2 rounded-md">Print</a>
            @if ($bill->isIssued())
                <a href="{{ route('credit-notes.create', $bill) }}" class="border bg-white px-4 py-2 rounded-md">Tax credit note</a>
            @endif
            @if ($bill->isIssued() && $bill->isSameShopDay() && auth()->user()->can('void-bill'))
                <form method="POST" action="{{ route('bills.void', $bill) }}" onsubmit="return confirm('Void this bill and restore stock?')">
                    @csrf
                    <button class="bg-red-700 text-white px-4 py-2 rounded-md">Void (same day)</button>
                </form>
            @endif
        </div>
    </div>

    <div class="grid md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white p-4 rounded-lg shadow-sm text-sm">
            <div class="font-semibold mb-1">Customer</div>
            <div>{{ $bill->garage_name ?: 'Walk-in' }}</div>
            <div>{{ $bill->garage_phone }}</div>
            <div>{{ $bill->garage_address }}</div>
            <div>TRN {{ $bill->garage_trn ?: '—' }}</div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <div class="text-sm text-slate-500">Total / paid / credited</div>
            <div>AED {{ \App\Support\Money::fromFils($bill->total_fils) }} / {{ \App\Support\Money::fromFils($bill->paid_fils) }} / {{ \App\Support\Money::fromFils($bill->credited_fils) }}</div>
            <div class="font-semibold mt-1">Outstanding AED {{ \App\Support\Money::fromFils($bill->outstandingFils()) }}</div>
            @if ($bill->credit_due_date)
                <div class="text-sm">Credit due {{ $bill->credit_due_date->format('d M Y') }}</div>
            @endif
        </div>
        <div class="bg-white p-4 rounded-lg shadow-sm text-sm">Created by {{ $bill->creator?->name }}</div>
    </div>

    <table class="w-full text-sm bg-white rounded-lg mb-6">
        <thead class="bg-slate-50 text-left"><tr><th class="px-3 py-2">SKU</th><th>Name</th><th>Qty</th><th>Rate</th><th>VAT</th><th>Line total</th></tr></thead>
        <tbody>
        @foreach ($bill->items as $item)
            <tr class="border-t">
                <td class="px-3 py-2">{{ $item->sku }}</td>
                <td>{{ $item->name }}</td>
                <td>{{ $item->qty }}@if($item->returned_qty) <span class="text-xs text-slate-500">(returned {{ $item->returned_qty }})</span> @endif</td>
                <td>{{ \App\Support\Money::fromFils($item->unit_price_fils) }}</td>
                <td>{{ \App\Support\Money::fromFils($item->line_vat_fils) }}</td>
                <td>{{ \App\Support\Money::fromFils($item->line_total_fils) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    @if ($bill->installments->isNotEmpty())
        <h2 class="font-semibold mb-2">Installments (due on collection day)</h2>
        <table class="w-full text-sm bg-white rounded-lg mb-6">
            <thead class="bg-slate-50 text-left"><tr><th class="px-3 py-2">#</th><th>Due</th><th>Amount</th><th>Paid</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @foreach ($bill->installments as $row)
                <tr class="border-t {{ $row->isOpen() && $row->due_date->isPast() ? 'bg-red-50' : '' }}">
                    <td class="px-3 py-2">{{ $row->sequence }}</td>
                    <td>{{ $row->due_date->format('d M Y') }}</td>
                    <td>{{ \App\Support\Money::fromFils($row->amount_fils) }}</td>
                    <td>{{ \App\Support\Money::fromFils($row->paid_fils) }}</td>
                    <td>{{ $row->status->value }}</td>
                    <td>
                        @if ($row->isOpen() && $bill->isIssued())
                            <form method="POST" action="{{ route('collections.store') }}" class="flex gap-1">
                                @csrf
                                <input type="hidden" name="bill_id" value="{{ $bill->id }}">
                                <input type="hidden" name="installment_id" value="{{ $row->id }}">
                                <input type="hidden" name="method" value="cash">
                                <input name="amount" type="number" step="0.01" min="0.01" value="{{ number_format($row->outstandingFils()/100, 2, '.', '') }}" class="border rounded w-28 px-2 py-1">
                                <button class="bg-emerald-700 text-white px-2 py-1 rounded">Collect</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif

    @if ($bill->payment_type === \App\Enums\PaymentType::Credit && $bill->outstandingFils() > 0 && $bill->isIssued())
        <form method="POST" action="{{ route('collections.store') }}" class="bg-white p-4 rounded-lg shadow-sm max-w-md flex gap-2 items-end mb-6">
            @csrf
            <input type="hidden" name="bill_id" value="{{ $bill->id }}">
            <input type="hidden" name="method" value="cash">
            <div>
                <label class="text-sm">Collect credit (AED)</label>
                <input name="amount" type="number" step="0.01" required value="{{ number_format($bill->outstandingFils()/100, 2, '.', '') }}" class="border rounded px-3 py-2">
            </div>
            <button class="bg-emerald-700 text-white px-4 py-2 rounded-md">Record payment</button>
        </form>
    @endif
</x-layouts.app>
