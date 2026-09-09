<x-layouts.app :title="$garage->name">
    <div class="flex justify-between mb-4">
        <h1 class="text-2xl font-semibold">{{ $garage->name }}</h1>
        <a href="{{ route('garages.edit', $garage) }}" class="border bg-white px-4 py-2 rounded-md">Edit payment default</a>
    </div>
    <div class="grid md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <div class="text-sm text-slate-500">Outstanding</div>
            <div class="text-2xl font-semibold">AED {{ \App\Support\Money::fromFils($garage->outstandingFils()) }}</div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow-sm text-sm">
            <div>Default: {{ $garage->payment_type->label() }}</div>
            <div>Phone: {{ $garage->phone ?: '—' }}</div>
            <div>TRN: {{ $garage->trn ?: '—' }}</div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow-sm text-sm">{{ $garage->address }}</div>
    </div>
    <h2 class="font-semibold mb-2">Recent bills</h2>
    <table class="w-full text-sm bg-white rounded-lg overflow-hidden">
        <thead class="bg-slate-50 text-left"><tr><th class="px-3 py-2">Number</th><th>Date</th><th>Type</th><th>Total</th><th>Outstanding</th></tr></thead>
        <tbody>
        @foreach ($garage->bills as $bill)
            <tr class="border-t">
                <td class="px-3 py-2"><a class="text-blue-700" href="{{ route('bills.show', $bill) }}">{{ $bill->number }}</a></td>
                <td>{{ $bill->billed_at->timezone(config('app.timezone'))->format('d M Y H:i') }}</td>
                <td>{{ $bill->payment_type->label() }}</td>
                <td>{{ \App\Support\Money::fromFils($bill->total_fils) }}</td>
                <td>{{ \App\Support\Money::fromFils($bill->outstandingFils()) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</x-layouts.app>
