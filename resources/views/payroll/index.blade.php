<x-layouts.app title="Salary & cuttings">
    <p class="text-sm text-slate-500 mb-4">Take-home = salary + incentive (extra sales above target) − cuttings. Accountant can record leave deductions and other cuttings for the month.</p>
    <form method="GET" class="mb-4">
        <input type="month" name="month" value="{{ $month->format('Y-m') }}" class="border border-slate-200 rounded-xl px-3 py-2 bg-white" onchange="this.form.submit()">
    </form>
    <form method="POST" action="{{ route('payroll.update') }}" class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-x-auto">
        @csrf
        <input type="hidden" name="period_start" value="{{ $month->toDateString() }}">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left">
                <tr>
                    <th class="px-3 py-3">Staff</th>
                    <th>Sales</th>
                    <th>Target</th>
                    <th>Incentive</th>
                    <th>Salary</th>
                    <th>Cuttings AED</th>
                    <th>Note</th>
                    <th>Take-home</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($rows as $row)
                <tr class="border-t">
                    <td class="px-3 py-3">{{ $row['user']->name }}<div class="text-xs text-slate-500">{{ $row['user']->getRoleNames()->join(', ') }} · {{ $row['user']->branch?->name }}</div></td>
                    <td>AED {{ \App\Support\Money::fromFils($row['sales']) }}</td>
                    <td>AED {{ \App\Support\Money::fromFils($row['target']) }}</td>
                    <td>AED {{ \App\Support\Money::fromFils($row['incentive']) }} <span class="text-xs text-slate-400">({{ $row['user']->incentive_percent }}%)</span></td>
                    <td>AED {{ \App\Support\Money::fromFils($row['salary']) }}</td>
                    <td>
                        <input name="cuttings[{{ $row['user']->id }}]" type="number" step="0.01" min="0"
                               value="{{ number_format($row['cuttings']/100, 2, '.', '') }}"
                               class="border rounded-lg w-28 px-2 py-1">
                    </td>
                    <td>
                        <input name="notes[{{ $row['user']->id }}]" value="{{ $row['cuttings_notes'] }}" placeholder="Leave / other" class="border rounded-lg w-36 px-2 py-1">
                    </td>
                    <td class="font-semibold">AED {{ \App\Support\Money::fromFils($row['take_home']) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <div class="p-4"><button class="bg-slate-900 text-white px-4 py-2 rounded-xl">Save cuttings</button></div>
    </form>
</x-layouts.app>
