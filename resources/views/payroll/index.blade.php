<x-layouts.app title="Salary">
    <h1 class="text-2xl font-semibold mb-2">Salary + incentive</h1>
    <p class="text-sm text-slate-500 mb-4">Sales take-home = monthly salary + incentive % of sales above their target. Change salary and incentive % on the staff screen.</p>
    <form method="GET" class="mb-4">
        <input type="month" name="month" value="{{ $month->format('Y-m') }}" class="border rounded px-3 py-2" onchange="this.form.submit()">
    </form>
    <table class="w-full text-sm bg-white rounded-lg">
        <thead class="bg-slate-50 text-left">
            <tr>
                <th class="px-3 py-2">Staff</th>
                <th>Sales</th>
                <th>Target</th>
                <th>Extra</th>
                <th>Incentive %</th>
                <th>Incentive</th>
                <th>Salary</th>
                <th>Take-home</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($rows as $row)
            <tr class="border-t">
                <td class="px-3 py-2">{{ $row['user']->name }} <span class="text-slate-500">{{ $row['user']->branch?->name }}</span></td>
                <td>AED {{ \App\Support\Money::fromFils($row['sales']) }}</td>
                <td>AED {{ \App\Support\Money::fromFils($row['target']) }}</td>
                <td>AED {{ \App\Support\Money::fromFils($row['extra']) }}</td>
                <td>{{ $row['user']->incentive_percent }}%</td>
                <td>AED {{ \App\Support\Money::fromFils($row['incentive']) }}</td>
                <td>AED {{ \App\Support\Money::fromFils($row['salary']) }}</td>
                <td class="font-semibold">AED {{ \App\Support\Money::fromFils($row['take_home']) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</x-layouts.app>
