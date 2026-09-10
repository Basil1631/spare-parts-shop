<x-layouts.app title="Purchases">
    <div class="flex justify-between mb-4">
        <h1 class="text-2xl font-semibold">Purchases</h1>
        <a href="{{ route('purchases.create') }}" class="bg-slate-900 text-white px-4 py-2 rounded-md">Record purchase</a>
    </div>
    <table class="w-full text-sm bg-white rounded-lg">
        <thead class="bg-slate-50 text-left"><tr><th class="px-3 py-2">Date</th><th>Branch</th><th>Supplier</th><th>Invoice</th><th>Total</th><th></th></tr></thead>
        <tbody>
        @foreach ($purchases as $purchase)
            <tr class="border-t">
                <td class="px-3 py-2">{{ $purchase->purchased_on->format('d M Y') }}</td>
                <td>{{ $purchase->branch?->name }}</td>
                <td>{{ $purchase->supplier_name }}</td>
                <td>{{ $purchase->invoice_number }}</td>
                <td>AED {{ \App\Support\Money::fromFils($purchase->total_fils) }}</td>
                <td><a class="text-blue-700" href="{{ route('purchases.show', $purchase) }}">Open</a></td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="mt-4">{{ $purchases->links() }}</div>
</x-layouts.app>
