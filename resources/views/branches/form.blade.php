@php $b = $branch ?? null; @endphp
<x-layouts.app :title="$b ? 'Edit branch' : 'Add branch'">
    <h1 class="text-2xl font-semibold mb-4">{{ $b ? 'Edit branch' : 'Add branch' }}</h1>
    <form method="POST" action="{{ $b ? route('branches.update', $b) : route('branches.store') }}" class="bg-white p-6 rounded-lg max-w-xl space-y-3">
        @csrf
        @if ($b) @method('PUT') @endif
        <input name="name" required placeholder="Name" value="{{ old('name', $b?->name) }}" class="w-full border rounded px-3 py-2">
        <input name="code" required placeholder="Code" value="{{ old('code', $b?->code) }}" class="w-full border rounded px-3 py-2">
        <input name="phone" placeholder="Phone" value="{{ old('phone', $b?->phone) }}" class="w-full border rounded px-3 py-2">
        <textarea name="address" placeholder="Address" class="w-full border rounded px-3 py-2">{{ old('address', $b?->address) }}</textarea>
        @if ($b)
            <label class="text-sm"><input type="checkbox" name="active" value="1" @checked(old('active', $b->active))> Active</label>
        @endif
        <button class="bg-slate-900 text-white px-4 py-2 rounded">Save</button>
    </form>
</x-layouts.app>
