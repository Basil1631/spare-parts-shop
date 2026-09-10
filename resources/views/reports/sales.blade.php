<x-layouts.app title="Sales analysis">
    <h1 class="text-2xl font-semibold mb-2">Sales by person</h1>
    <div class="flex gap-2 mb-4">
        <a class="px-3 py-1 rounded {{ $range === 'day' ? 'bg-slate-900 text-white' : 'bg-white border' }}" href="{{ route('reports.sales', ['range' => 'day']) }}">Day</a>
        <a class="px-3 py-1 rounded {{ $range === 'week' ? 'bg-slate-900 text-white' : 'bg-white border' }}" href="{{ route('reports.sales', ['range' => 'week']) }}">Week</a>
        <a class="px-3 py-1 rounded {{ $range === 'month' ? 'bg-slate-900 text-white' : 'bg-white border' }}" href="{{ route('reports.sales', ['range' => 'month']) }}">Month</a>
    </div>
    <p class="text-sm text-slate-500 mb-3">{{ $from }} → {{ $to }}</p>
    <table class="w-full text-sm bg-white rounded-lg">
        <thead class="bg-slate-50 text-left"><tr><th class="px-3 py-2">Sales staff</th><th>Bills</th><th>Sales AED</th></tr></thead>
        <tbody>
        @foreach ($staff as $person)
            @php $tot = $totals->get($person->id); @endphp
            <tr class="border-t">
                <td class="px-3 py-2">{{ $person->name }}</td>
                <td>{{ $tot->bills_count ?? 0 }}</td>
                <td>AED {{ \App\Support\Money::fromFils((int) ($tot->sales_fils ?? 0)) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</x-layouts.app>
