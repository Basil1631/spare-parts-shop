<x-layouts.app title="Staff">
    <div class="flex justify-between mb-4">
        <h1 class="text-2xl font-semibold">Staff</h1>
        <a href="{{ route('staff.create') }}" class="bg-slate-900 text-white px-4 py-2 rounded-md">Add person</a>
    </div>
    <p class="text-sm text-slate-500 mb-3">Everyone signs in at the same /login URL with their own email and password.</p>
    <table class="w-full text-sm bg-white rounded-lg">
        <thead class="bg-slate-50 text-left"><tr><th class="px-3 py-2">Name</th><th>Email</th><th>Role</th><th>Branch</th><th>Salary</th><th>Incentive %</th><th></th></tr></thead>
        <tbody>
        @foreach ($users as $user)
            <tr class="border-t">
                <td class="px-3 py-2">{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->getRoleNames()->join(', ') }}</td>
                <td>{{ $user->branch?->name }}</td>
                <td>{{ \App\Support\Money::fromFils($user->monthly_salary_fils) }}</td>
                <td>{{ $user->incentive_percent }}%</td>
                <td><a class="text-blue-700" href="{{ route('staff.edit', $user) }}">Edit</a></td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="mt-4">{{ $users->links() }}</div>
</x-layouts.app>
