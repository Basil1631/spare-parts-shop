<x-layouts.app title="Bills">
    <div class="flex justify-between mb-4">
        <h1 class="text-2xl font-semibold">Bills</h1>
        <a href="{{ route('bills.create') }}" class="bg-amber-400 text-slate-900 font-bold px-4 py-2 rounded-md">Bill</a>
    </div>
    <form class="mb-4 flex flex-wrap gap-2">
        <input name="q" value="{{ request('q') }}" placeholder="Number or garage" class="border rounded-md px-3 py-2 bg-white">
        <input type="date" name="from" value="{{ request('from') }}" class="border rounded-md px-3 py-2 bg-white">
        <input type="date" name="to" value="{{ request('to') }}" class="border rounded-md px-3 py-2 bg-white">
        <button class="border bg-white px-3 rounded-md">Filter</button>
    </form>
    <div class="bg-white rounded-lg shadow-sm overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left">
                <tr>
                    <th class="px-3 py-2">Number</th>
                    <th>Date</th>
                    <th>Garage</th>
                    <th>Payment</th>
                    <th>Total</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($bills as $bill)
                <tr class="border-t">
                    <td class="px-3 py-2"><a class="text-blue-700" href="{{ route('bills.show', $bill) }}">{{ $bill->number }}</a></td>
                    <td>{{ $bill->billed_at->timezone(config('app.timezone'))->format('d M Y H:i') }}</td>
                    <td>{{ $bill->garage_name ?: 'Walk-in' }}</td>
                    <td>{{ $bill->payment_type->label() }}</td>
                    <td>AED {{ \App\Support\Money::fromFils($bill->total_fils) }}</td>
                    <td>{{ $bill->status->value }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $bills->links() }}</div>
</x-layouts.app>
