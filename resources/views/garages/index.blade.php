<x-layouts.app title="Garages">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-semibold">Garages</h1>
        <a href="{{ route('garages.create') }}" class="bg-slate-900 text-white px-4 py-2 rounded-md">Add garage</a>
    </div>
    <form class="mb-4 flex gap-2">
        <input name="q" value="{{ request('q') }}" placeholder="Search name or phone" class="border rounded-md px-3 py-2 w-72 bg-white">
        <button class="border bg-white px-3 rounded-md">Search</button>
    </form>
    <div class="bg-white rounded-lg shadow-sm overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left"><tr><th class="px-3 py-2">Name</th><th>Phone</th><th>TRN</th><th>Default payment</th><th></th></tr></thead>
            <tbody>
            @foreach ($garages as $garage)
                <tr class="border-t">
                    <td class="px-3 py-2"><a class="text-blue-700" href="{{ route('garages.show', $garage) }}">{{ $garage->name }}</a></td>
                    <td>{{ $garage->phone }}</td>
                    <td>{{ $garage->trn }}</td>
                    <td>{{ $garage->payment_type->label() }}</td>
                    <td class="px-3"><a class="text-blue-700" href="{{ route('garages.edit', $garage) }}">Edit</a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $garages->links() }}</div>
</x-layouts.app>
