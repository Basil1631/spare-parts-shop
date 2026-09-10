<x-layouts.app title="Attendance">
    <h1 class="text-2xl font-semibold mb-2">Attendance (login = present)</h1>
    <p class="text-sm text-slate-500 mb-4">Each login on a calendar day counts as present. Days with no login in the range are treated as leave for this report.</p>
    <form method="GET" class="flex gap-2 mb-4">
        <input type="date" name="from" value="{{ $from }}" class="border rounded px-3 py-2">
        <input type="date" name="to" value="{{ $to }}" class="border rounded px-3 py-2">
        <button class="border bg-white px-3 rounded">Filter</button>
    </form>
    <table class="w-full text-sm bg-white rounded-lg">
        <thead class="bg-slate-50 text-left">
            <tr>
                <th class="px-3 py-2">Staff</th>
                <th>Role</th>
                <th>In shop today</th>
                <th>Present days</th>
                <th>Leave days (range)</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($staff as $person)
            @php
                $present = (int) ($presentCounts[$person->id] ?? 0);
                $leave = max(0, $days - $present);
            @endphp
            <tr class="border-t">
                <td class="px-3 py-2">{{ $person->name }} <span class="text-slate-500">{{ $person->branch?->name }}</span></td>
                <td>{{ $person->getRoleNames()->join(', ') }}</td>
                <td>@if(in_array((int) $person->id, array_map('intval', $presentToday), true)) Yes @else No @endif</td>
                <td>{{ $present }}</td>
                <td>{{ $leave }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</x-layouts.app>
