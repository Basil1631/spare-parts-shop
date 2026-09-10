<x-layouts.app title="Targets">
    <h1 class="text-2xl font-semibold mb-2">Monthly sales targets</h1>
    <p class="text-sm text-slate-500 mb-4">
        @if (auth()->user()->isAdmin())
            Set targets for each branch manager.
        @else
            Set targets for each sales person in this branch. Incentive is paid on extra sales above target, at the % on their staff record.
        @endif
    </p>
    <form method="GET" class="mb-4">
        <input type="month" name="period" value="{{ \Carbon\Carbon::parse($period)->format('Y-m') }}" class="border rounded px-3 py-2" onchange="this.form.submit()">
    </form>
    <form method="POST" action="{{ route('targets.store') }}" class="bg-white rounded-lg p-4">
        @csrf
        <input type="hidden" name="period_start" value="{{ $period }}">
        <table class="w-full text-sm">
            <thead class="text-left"><tr><th class="py-2">Person</th><th>Branch</th><th>Target AED this month</th></tr></thead>
            <tbody>
            @forelse ($people as $person)
                <tr class="border-t">
                    <td class="py-2">{{ $person->name }}</td>
                    <td>{{ $person->branch?->name }}</td>
                    <td>
                        <input name="amount[{{ $person->id }}]" type="number" step="0.01" min="0"
                               value="{{ old('amount.'.$person->id, isset($targets[$person->id]) ? number_format($targets[$person->id]->amount_fils/100, 2, '.', '') : '') }}"
                               class="border rounded w-40 px-2 py-1">
                    </td>
                </tr>
            @empty
                <tr><td colspan="3" class="py-4 text-slate-500">No people to assign yet. Add staff first.</td></tr>
            @endforelse
            </tbody>
        </table>
        <button class="mt-3 bg-slate-900 text-white px-4 py-2 rounded">Save targets</button>
    </form>
</x-layouts.app>
