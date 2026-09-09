<x-layouts.app title="Collections">
    <h1 class="text-2xl font-semibold mb-4">Due payments</h1>
    <div class="grid lg:grid-cols-2 gap-6">
        <section class="bg-white rounded-lg shadow-sm p-4">
            <h2 class="font-semibold mb-3">Installments</h2>
            <table class="w-full text-sm">
                <thead class="text-left text-slate-500"><tr><th class="py-1">Bill</th><th>Due</th><th>Open</th><th></th></tr></thead>
                <tbody>
                @foreach ($installments as $row)
                    <tr class="border-t {{ $row->due_date->lte(now()) ? 'bg-red-50' : '' }}">
                        <td class="py-2">
                            <a class="text-blue-700" href="{{ route('bills.show', $row->bill) }}">{{ $row->bill->number }}</a>
                            <div class="text-xs text-slate-500">{{ $row->bill->garage_name }} · #{{ $row->sequence }}</div>
                        </td>
                        <td>{{ $row->due_date->format('d M Y') }}</td>
                        <td>AED {{ \App\Support\Money::fromFils($row->outstandingFils()) }}</td>
                        <td>
                            <form method="POST" action="{{ route('collections.store') }}" class="flex gap-1">
                                @csrf
                                <input type="hidden" name="bill_id" value="{{ $row->bill_id }}">
                                <input type="hidden" name="installment_id" value="{{ $row->id }}">
                                <input type="hidden" name="method" value="cash">
                                <input name="amount" type="number" step="0.01" class="border rounded w-24 px-1" value="{{ number_format($row->outstandingFils()/100, 2, '.', '') }}">
                                <button class="bg-emerald-700 text-white px-2 rounded">Pay</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div class="mt-3">{{ $installments->links() }}</div>
        </section>
        <section class="bg-white rounded-lg shadow-sm p-4">
            <h2 class="font-semibold mb-3">Credit bills</h2>
            <table class="w-full text-sm">
                <thead class="text-left text-slate-500"><tr><th class="py-1">Bill</th><th>Due</th><th>Open</th><th></th></tr></thead>
                <tbody>
                @foreach ($credits as $bill)
                    <tr class="border-t {{ $bill->credit_due_date?->isPast() ? 'bg-red-50' : '' }}">
                        <td class="py-2">
                            <a class="text-blue-700" href="{{ route('bills.show', $bill) }}">{{ $bill->number }}</a>
                            <div class="text-xs text-slate-500">{{ $bill->garage_name }}</div>
                        </td>
                        <td>{{ $bill->credit_due_date?->format('d M Y') }}</td>
                        <td>AED {{ \App\Support\Money::fromFils($bill->outstandingFils()) }}</td>
                        <td>
                            <form method="POST" action="{{ route('collections.store') }}" class="flex gap-1">
                                @csrf
                                <input type="hidden" name="bill_id" value="{{ $bill->id }}">
                                <input type="hidden" name="method" value="cash">
                                <input name="amount" type="number" step="0.01" class="border rounded w-24 px-1" value="{{ number_format($bill->outstandingFils()/100, 2, '.', '') }}">
                                <button class="bg-emerald-700 text-white px-2 rounded">Pay</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div class="mt-3">{{ $credits->links() }}</div>
        </section>
    </div>
</x-layouts.app>
