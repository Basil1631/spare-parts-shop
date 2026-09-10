<x-layouts.app title="Branches">
    <div class="flex justify-between mb-4">
        <h1 class="text-2xl font-semibold">Branches</h1>
        <a href="{{ route('branches.create') }}" class="bg-slate-900 text-white px-4 py-2 rounded-md">Add branch</a>
    </div>
    <table class="w-full text-sm bg-white rounded-lg">
        <thead class="bg-slate-50 text-left"><tr><th class="px-3 py-2">Name</th><th>Code</th><th>Manager</th><th></th></tr></thead>
        <tbody>
        @foreach ($branches as $branch)
            <tr class="border-t">
                <td class="px-3 py-2">{{ $branch->name }}</td>
                <td>{{ $branch->code }}</td>
                <td>{{ $branch->manager?->name ?? '—' }}</td>
                <td><a class="text-blue-700" href="{{ route('branches.edit', $branch) }}">Edit</a></td>
            </tr>
        @endforeach
        </tbody>
    </table>
</x-layouts.app>
