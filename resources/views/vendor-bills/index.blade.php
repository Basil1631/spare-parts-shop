<x-layouts.app title="Vendor bills">
    <p class="text-sm text-slate-500 mb-4">Invoices appear here after the godown supervisor confirms receipt. Mark each vendor bill unpaid or paid.</p>
    <div class="bg-white rounded-2xl border border-slate-100 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left">
                <tr>
                    <th class="px-3 py-3">Received</th>
                    <th>Supplier</th>
                    <th>Invoice</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @forelse ($purchases as $purchase)
                <tr class="border-t">
                    <td class="px-3 py-3">{{ $purchase->received_at?->timezone(config('app.timezone'))->format('d M Y H:i') }}</td>
                    <td>{{ $purchase->supplier_name ?: '—' }}</td>
                    <td>{{ $purchase->invoice_number ?: '#'.$purchase->id }}</td>
                    <td>AED {{ \App\Support\Money::fromFils($purchase->total_fils) }}</td>
                    <td>{{ $purchase->status->label() }}</td>
                    <td><a class="text-teal-700" href="{{ route('vendor-bills.show', $purchase) }}">Open</a></td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-3 py-8 text-slate-500">No confirmed invoices yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $purchases->links() }}</div>
</x-layouts.app>
